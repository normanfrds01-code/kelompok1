<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['admin']);
$pageTitle = 'Sync Produk DigiFlazz — Admin';

$db = db();
$message = null;
$syncResult = null;

/* ══ HANDLE ACTIONS ══ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $act = $_POST['action'] ?? '';

    // ── SYNC PRODUK DARI DIGIFLAZZ ──
    if ($act === 'sync') {
        try {
            // Gunakan kredensial dari Pengaturan (bukan konstanta placeholder)
            $user = digiUsername();
            $sign = md5($user . digiApiKey() . 'pricelist');
            $payload = [
                'cmd'      => 'prepaid',
                'username' => $user,
                'sign'     => $sign,
            ];

            $url = DIGI_BASE_URL . '/price-list';
            $ch  = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 60,
            ]);
            $resp = curl_exec($ch);
            $err  = curl_error($ch);
            curl_close($ch);

            if ($err) throw new Exception('cURL error: ' . $err);

            $data = json_decode($resp, true);
            if (!is_array($data) || !isset($data['data'])) {
                throw new Exception('Response tidak valid dari DigiFlazz.');
            }
            // DigiFlazz membalas object error (rc != 00) saat kredensial/IP salah
            if (isset($data['data']['rc']) && ($data['data']['rc'] ?? '') !== '00') {
                throw new Exception($data['data']['message'] ?? 'DigiFlazz menolak permintaan (cek username, API key & whitelist IP).');
            }

            $products = $data['data'];
            $new = 0; $updated = 0; $skipped = 0;

            $sel = $db->prepare("SELECT id FROM digi_products WHERE sku_code = ?");
            $ins = $db->prepare("INSERT INTO digi_products (sku_code,product_name,brand,category,price,price_sell,description,is_active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,1,NOW(),NOW())");
            // Catatan: price_sell TIDAK ditimpa saat update — markup manual admin tetap terjaga.
            $upd = $db->prepare("UPDATE digi_products SET product_name=?,brand=?,category=?,price=?,description=?,updated_at=NOW() WHERE sku_code=?");

            foreach ($products as $p) {
                $skuCode  = $p['buyer_sku_code'] ?? null;
                $prodName = $p['product_name']  ?? null;
                if (!$skuCode || !$prodName) { $skipped++; continue; }

                $price    = (int)($p['price'] ?? 0);
                $category = $p['category'] ?? 'game';
                $brand    = $p['brand'] ?? null;
                $desc     = $p['desc']  ?? null;

                $sel->execute([$skuCode]);
                if ($sel->fetch()) {
                    $upd->execute([$prodName, $brand, $category, $price, $desc, $skuCode]);
                    $updated++;
                } else {
                    $sellPrice = $price + (int)round($price * 0.02); // default markup 2%
                    $ins->execute([$skuCode, $prodName, $brand, $category, $price, $sellPrice, $desc]);
                    $new++;
                }
            }

            Security::audit('DIGI_SYNC', "Sync DigiFlazz: $new baru, $updated update, $skipped dilewati");
            setFlash('success', "Sync selesai! <strong>$new</strong> produk baru, <strong>$updated</strong> diperbarui" . ($skipped ? ", $skipped dilewati" : '') . ".");

        } catch (\Exception $e) {
            setFlash('error', 'Sync gagal: ' . htmlspecialchars($e->getMessage()));
        }
        header('Location: ' . asset('admin/sync-products.php')); exit;
    }

    // ── UPDATE MARKUP ──
    if ($act === 'update_markup') {
        $markupType = $_POST['markup_type'] ?? 'percent';
        $markupVal  = (float)($_POST['markup_value'] ?? 2);
        $catFilter  = $_POST['cat_filter'] ?? 'all';

        $where = $catFilter !== 'all' ? "WHERE category = " . $db->quote($catFilter) : '';
        $rows   = $db->query("SELECT id, price FROM digi_products $where")->fetchAll();

        foreach ($rows as $r) {
            if ($markupType === 'percent') {
                $newPrice = $r['price'] + round($r['price'] * ($markupVal / 100));
            } else {
                $newPrice = $r['price'] + $markupVal;
            }
            $db->prepare("UPDATE digi_products SET price_sell=? WHERE id=?")->execute([(int)$newPrice, $r['id']]);
        }

        setFlash('success', 'Markup berhasil diperbarui untuk ' . count($rows) . ' produk.');
        header('Location: ' . asset('admin/sync-products.php')); exit;
    }

    // ── TOGGLE ACTIVE ──
    if ($act === 'toggle') {
        $id   = (int)$_POST['id'];
        $curr = (int)$_POST['current'];
        $db->prepare("UPDATE digi_products SET is_active=? WHERE id=?")->execute([$curr ? 0 : 1, $id]);
        header('Location: ' . asset('admin/sync-products.php')); exit;
    }

    // ── UPDATE HARGA JUAL MANUAL ──
    if ($act === 'update_price') {
        $id        = (int)$_POST['id'];
        $sellPrice = (int)$_POST['price_sell'];
        $db->prepare("UPDATE digi_products SET price_sell=? WHERE id=?")->execute([$sellPrice, $id]);
        echo json_encode(['ok' => true]); exit;
    }
}

/* ══ FETCH DATA ══ */
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 30;
$offset = ($page - 1) * $limit;
$search = trim($_GET['q'] ?? '');
$catF   = trim($_GET['cat'] ?? '');
$activeF = $_GET['active'] ?? '';

try {
    // Check if table exists
    $db->query("SELECT 1 FROM digi_products LIMIT 1");
    $tableExists = true;
} catch (\Exception $e) {
    $tableExists = false;
}

$products = [];
$total    = 0;
$pages    = 1;
$balance  = null;
$catList  = [];

if ($tableExists) {
    $where  = [];
    $params = [];
    if ($search) { $where[] = '(product_name LIKE ? OR sku_code LIKE ? OR brand LIKE ?)'; $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]); }
    if ($catF)   { $where[] = 'category = ?'; $params[] = $catF; }
    if ($activeF !== '') { $where[] = 'is_active = ?'; $params[] = (int)$activeF; }
    $wClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $totalStmt = $db->prepare("SELECT COUNT(*) FROM digi_products $wClause");
    $totalStmt->execute($params);
    $total = (int)$totalStmt->fetchColumn();
    $pages = max(1, ceil($total / $limit));

    $stmt = $db->prepare("SELECT * FROM digi_products $wClause ORDER BY category ASC, product_name ASC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $catList = $db->query("SELECT DISTINCT category FROM digi_products ORDER BY category")->fetchAll(\PDO::FETCH_COLUMN);
}

// Cek saldo DigiFlazz
try {
    $balRes = digiCheckBalance();
    $balance = $balRes['data']['deposit'] ?? null;
} catch (\Exception $e) {
    $balance = null;
}

include __DIR__ . '/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-main">

  <div class="admin-title" style="margin-bottom:16px;">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
    Sync Produk DigiFlazz
  </div>

  <!-- Info bar -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px;">
    <div style="background:var(--card);border:1px solid var(--b1);border-radius:10px;padding:14px 16px;">
      <div style="font-size:.61rem;text-transform:uppercase;letter-spacing:.6px;color:var(--t3);margin-bottom:6px;">Saldo DigiFlazz</div>
      <div style="font-size:1.3rem;font-weight:800;color:<?=$balance!==null?'#34d399':'#f87171'?>;">
        <?=$balance!==null ? formatRupiah((float)$balance) : 'Tidak terhubung'?>
      </div>
    </div>
    <div style="background:var(--card);border:1px solid var(--b1);border-radius:10px;padding:14px 16px;">
      <div style="font-size:.61rem;text-transform:uppercase;letter-spacing:.6px;color:var(--t3);margin-bottom:6px;">Total Produk</div>
      <div style="font-size:1.3rem;font-weight:800;color:#e8eaf0;"><?=number_format($total)?></div>
    </div>
    <div style="background:var(--card);border:1px solid var(--b1);border-radius:10px;padding:14px 16px;">
      <div style="font-size:.61rem;text-transform:uppercase;letter-spacing:.6px;color:var(--t3);margin-bottom:6px;">Mode DigiFlazz</div>
      <div style="font-size:1rem;font-weight:700;color:<?=digiEnv()==='dev'?'#fbbf24':'#34d399'?>;">
        <?=digiEnv()==='dev'?'🧪 Sandbox':'🚀 Production'?>
      </div>
    </div>
  </div>

  <!-- Actions row -->
  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;">
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="sync">
      <button type="submit" class="btn-gold" style="display:flex;align-items:center;gap:7px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        Sync dari DigiFlazz
      </button>
    </form>
    <button class="btn-sm btn-sm-edit" data-modal-open="modal-markup" style="display:flex;align-items:center;gap:6px;padding:8px 16px;">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      Atur Markup Massal
    </button>
    <?php if (!$tableExists): ?>
    <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:10px 14px;font-size:.79rem;color:#f87171;">
      ⚠️ Tabel <code>digi_products</code> belum ada. Jalankan SQL migration terlebih dahulu.
    </div>
    <?php endif; ?>
  </div>

  <?php if ($tableExists): ?>
  <!-- Filter -->
  <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
    <input type="text" name="q" value="<?=htmlspecialchars($search)?>" placeholder="Cari produk, SKU, brand..." class="finput" style="flex:1;min-width:200px;"/>
    <select name="cat" class="finput" style="min-width:140px;">
      <option value="">Semua Kategori</option>
      <?php foreach ($catList as $c): ?>
      <option value="<?=htmlspecialchars($c)?>" <?=$catF===$c?'selected':''?>><?=ucfirst($c)?></option>
      <?php endforeach; ?>
    </select>
    <select name="active" class="finput" style="min-width:110px;">
      <option value="">Semua Status</option>
      <option value="1" <?=$activeF==='1'?'selected':''?>>Aktif</option>
      <option value="0" <?=$activeF==='0'?'selected':''?>>Nonaktif</option>
    </select>
    <button type="submit" class="btn-gold" style="padding:8px 18px;">Filter</button>
    <a href="?" class="btn-sm btn-sm-edit" style="padding:8px 14px;">Reset</a>
  </form>

  <!-- Table -->
  <div class="admin-card">
    <div class="table-wrap">
      <table class="dtable">
        <thead>
          <tr>
            <th>SKU Code</th>
            <th>Produk</th>
            <th>Brand</th>
            <th>Kategori</th>
            <th style="text-align:right">Harga Modal</th>
            <th style="text-align:right">Harga Jual</th>
            <th style="text-align:center">Margin</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
        <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--t3);">
          <?=$total===0?'Belum ada produk. Klik "Sync dari DigiFlazz" untuk mengambil produk.':'Tidak ada produk sesuai filter.'?>
        </td></tr>
        <?php else: foreach ($products as $p):
          $margin = $p['price'] > 0 ? round((($p['price_sell'] - $p['price']) / $p['price']) * 100, 1) : 0;
          $marginColor = $margin >= 5 ? '#34d399' : ($margin >= 2 ? '#fbbf24' : '#f87171');
        ?>
        <tr>
          <td><code style="font-size:.72rem;color:var(--red);background:var(--red-lo);padding:2px 6px;border-radius:4px;"><?=htmlspecialchars($p['sku_code'])?></code></td>
          <td style="font-size:.82rem;font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($p['product_name'])?></td>
          <td style="font-size:.77rem;color:var(--t2);"><?=htmlspecialchars($p['brand'] ?? '—')?></td>
          <td><span class="badge badge-process" style="font-size:.66rem;"><?=ucfirst($p['category'])?></span></td>
          <td style="text-align:right;font-size:.78rem;color:var(--t3);"><?=formatRupiah($p['price'])?></td>
          <td style="text-align:right;">
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
              <span style="font-size:.8rem;font-weight:700;color:var(--gold);" id="price-<?=$p['id']?>"><?=formatRupiah($p['price_sell'])?></span>
              <button onclick="editPriceInline(<?=$p['id']?>,<?=$p['price_sell']?>)" style="background:none;border:none;color:var(--t3);cursor:pointer;padding:2px;" title="Edit harga">✏️</button>
            </div>
          </td>
          <td style="text-align:center;">
            <span style="font-size:.72rem;font-weight:700;color:<?=$marginColor?>;"><?=$margin?>%</span>
          </td>
          <td>
            <?php if ($p['is_active']): ?>
            <span class="badge badge-success" style="font-size:.66rem;">Aktif</span>
            <?php else: ?>
            <span class="badge badge-failed" style="font-size:.66rem;">Nonaktif</span>
            <?php endif; ?>
          </td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="_token" value="<?=csrfToken()?>">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?=$p['id']?>">
              <input type="hidden" name="current" value="<?=$p['is_active']?>">
              <button class="btn-sm <?=$p['is_active']?'btn-sm-danger':'btn-sm-edit'?>"><?=$p['is_active']?'Nonaktif':'Aktifkan'?></button>
            </form>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div style="display:flex;gap:5px;padding:14px 16px;flex-wrap:wrap;align-items:center;">
      <span style="font-size:.75rem;color:var(--t3);margin-right:8px;">Total: <?=number_format($total)?> produk</span>
      <?php for ($i = 1; $i <= min($pages, 20); $i++): ?>
      <a href="?page=<?=$i?>&q=<?=urlencode($search)?>&cat=<?=urlencode($catF)?>&active=<?=urlencode($activeF)?>"
         style="padding:5px 11px;border-radius:6px;font-size:.78rem;text-decoration:none;<?=$i===$page?'background:var(--red);color:#fff;font-weight:700;':'background:var(--card2);color:var(--t2);border:1px solid var(--b1);'?>"><?=$i?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>
</div>

<!-- Modal Markup -->
<div class="modal" id="modal-markup">
  <div class="modal-box" style="max-width:460px;">
    <div class="modal-head"><h3>💰 Atur Markup Massal</h3><button class="modal-close" data-modal-close="modal-markup">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="update_markup">
      <div class="fg">
        <label class="flabel">Tipe Markup</label>
        <select name="markup_type" class="finput">
          <option value="percent">Persentase (%)</option>
          <option value="flat">Nominal Flat (Rp)</option>
        </select>
      </div>
      <div class="fg">
        <label class="flabel">Nilai Markup</label>
        <input type="number" name="markup_value" class="finput" value="2" step="0.5" min="0"/>
        <div class="fhint">Contoh: 2 = markup 2% dari harga modal</div>
      </div>
      <div class="fg">
        <label class="flabel">Terapkan ke Kategori</label>
        <select name="cat_filter" class="finput">
          <option value="all">Semua Kategori</option>
          <?php foreach ($catList as $c): ?>
          <option value="<?=htmlspecialchars($c)?>"><?=ucfirst($c)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;" onclick="return confirm('Yakin update harga jual semua produk?')">Terapkan Markup</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-markup" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function editPriceInline(id, currentPrice) {
  var newPrice = prompt('Harga jual baru (Rp):', currentPrice);
  if (!newPrice || isNaN(newPrice)) return;
  newPrice = parseInt(newPrice);

  fetch('', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_token=<?=csrfToken()?>&action=update_price&id='+id+'&price_sell='+newPrice
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      var el = document.getElementById('price-'+id);
      if (el) el.textContent = 'Rp ' + newPrice.toLocaleString('id-ID');
    }
  });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
