<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['admin']);

$gameId = (int)($_GET['game_id'] ?? 0);
if (!$gameId) { header('Location: '.asset('admin/games.php')); exit; }

$db   = db();
$game = $db->prepare("SELECT g.*, c.name AS cat_name FROM games g LEFT JOIN categories c ON c.id=g.category_id WHERE g.id=?");
$game->execute([$gameId]);
$game = $game->fetch();
if (!$game) { header('Location: '.asset('admin/games.php')); exit; }

$pageTitle = 'Produk — '.$game['name'].' — Admin';

/* ══ POST HANDLER ══ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $act = $_POST['action'] ?? '';

    if ($act === 'add') {
        $db->prepare("INSERT INTO products (game_id,name,description,price_cost,price_sell,digi_code,sort_order,is_active,created_at) VALUES (?,?,?,?,?,?,?,1,NOW())")
           ->execute([
               $gameId,
               trim($_POST['name'] ?? ''),
               trim($_POST['description'] ?? '') ?: null,
               (int)($_POST['price_cost'] ?? 0),
               (int)($_POST['price_sell'] ?? 0),
               trim($_POST['digi_code'] ?? '') ?: null,
               (int)($_POST['sort_order'] ?? 0),
           ]);
        setFlash('success', 'Produk berhasil ditambahkan.');
    }
    elseif ($act === 'edit') {
        $id = (int)$_POST['id'];
        $db->prepare("UPDATE products SET name=?,description=?,price_cost=?,price_sell=?,digi_code=?,sort_order=?,is_active=?,updated_at=NOW() WHERE id=? AND game_id=?")
           ->execute([
               trim($_POST['name'] ?? ''),
               trim($_POST['description'] ?? '') ?: null,
               (int)($_POST['price_cost'] ?? 0),
               (int)($_POST['price_sell'] ?? 0),
               trim($_POST['digi_code'] ?? '') ?: null,
               (int)($_POST['sort_order'] ?? 0),
               (int)($_POST['is_active'] ?? 1),
               $id, $gameId,
           ]);
        setFlash('success', 'Produk diperbarui.');
    }
    elseif ($act === 'delete') {
        $id = (int)$_POST['id'];
        $db->prepare("DELETE FROM products WHERE id=? AND game_id=?")->execute([$id, $gameId]);
        setFlash('success', 'Produk dihapus.');
    }
    elseif ($act === 'toggle') {
        $id  = (int)$_POST['id'];
        $cur = (int)$_POST['current'];
        $db->prepare("UPDATE products SET is_active=?,updated_at=NOW() WHERE id=? AND game_id=?")->execute([$cur ? 0 : 1, $id, $gameId]);
    }
    elseif ($act === 'import_digi') {
        // Import dari digi_products yang sudah di-sync
        try {
            $digiProducts = $db->prepare("SELECT * FROM digi_products WHERE brand LIKE ? AND is_active=1 ORDER BY price ASC");
            $digiProducts->execute(['%'.$game['name'].'%']);
            $digiList = $digiProducts->fetchAll();

            $imported = 0;
            foreach ($digiList as $dp) {
                // Cek apakah SKU sudah ada
                $exists = $db->prepare("SELECT id FROM products WHERE game_id=? AND digi_code=?");
                $exists->execute([$gameId, $dp['sku_code']]);
                if ($exists->fetch()) continue;

                $db->prepare("INSERT INTO products (game_id,name,description,price_cost,price_sell,digi_code,sort_order,is_active,created_at) VALUES (?,?,?,?,?,?,?,1,NOW())")
                   ->execute([
                       $gameId,
                       $dp['product_name'],
                       $dp['description'],
                       $dp['price'],
                       $dp['price_sell'],
                       $dp['sku_code'],
                       0,
                   ]);
                $imported++;
            }
            setFlash('success', "Import selesai! $imported produk baru dari DigiFlazz.");
        } catch (\Exception $e) {
            setFlash('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    header('Location: '.asset('admin/products.php').'?game_id='.$gameId);
    exit;
}

/* ══ FETCH PRODUCTS ══ */
$products = $db->prepare("SELECT * FROM products WHERE game_id=? ORDER BY sort_order ASC, price_sell ASC");
$products->execute([$gameId]);
$products = $products->fetchAll();

// Cek apakah digi_products table ada
$hasDigi = false;
try { $db->query("SELECT 1 FROM digi_products LIMIT 1"); $hasDigi = true; } catch (\Exception $e) {}

include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

  <!-- Breadcrumb -->
  <div style="display:flex;align-items:center;gap:8px;font-size:.76rem;color:var(--t3);margin-bottom:14px;">
    <a href="<?=asset('admin/games.php')?>" style="color:var(--t3);text-decoration:none;">Kelola Produk</a>
    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
    <span style="color:var(--t1);"><?=htmlspecialchars($game['name'])?></span>
  </div>

  <!-- Header -->
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
    <div style="display:flex;align-items:center;gap:12px;">
      <?php if($game['image_url']): ?>
      <img src="<?=htmlspecialchars($game['image_url'])?>" style="width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid var(--b1);" onerror="this.style.display='none'"/>
      <?php endif; ?>
      <div>
        <div class="admin-title" style="margin:0;font-size:1.1rem;"><?=htmlspecialchars($game['name'])?></div>
        <div style="font-size:.73rem;color:var(--t3);margin-top:2px;">
          <span class="badge badge-process" style="font-size:.65rem;"><?=htmlspecialchars($game['cat_name'])?></span>
          <span style="margin-left:6px;"><?=count($products)?> produk</span>
        </div>
      </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <?php if ($hasDigi): ?>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="_token" value="<?=csrfToken()?>">
        <input type="hidden" name="action" value="import_digi">
        <button type="submit" class="btn-sm btn-sm-edit" style="padding:8px 14px;font-size:.78rem;display:flex;align-items:center;gap:6px;"
          onclick="return confirm('Import produk dari DigiFlazz sync?')">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
          Import dari Digi
        </button>
      </form>
      <?php endif; ?>
      <button class="btn-gold" data-modal-open="modal-add" style="padding:8px 16px;font-size:.82rem;display:flex;align-items:center;gap:6px;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Nominal
      </button>
    </div>
  </div>

  <!-- Info markup -->
  <div style="background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.15);border-radius:8px;padding:10px 14px;font-size:.76rem;color:#60a5fa;margin-bottom:14px;display:flex;gap:8px;align-items:flex-start;">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span><strong>Harga Modal</strong> = harga beli dari DigiFlazz. <strong>Harga Jual</strong> = harga yang dibayar customer. Profit = Jual − Modal.</span>
  </div>

  <!-- Products table -->
  <div class="admin-card">
    <div class="table-wrap">
      <table class="dtable">
        <thead>
          <tr>
            <th style="width:40px;">No</th>
            <th>Nama Produk</th>
            <th>SKU / Digi Code</th>
            <th style="text-align:right;">Harga Modal</th>
            <th style="text-align:right;">Harga Jual</th>
            <th style="text-align:right;">Profit</th>
            <th style="text-align:center;">Urutan</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
        <tr>
          <td colspan="9" style="text-align:center;padding:48px;color:var(--t3);">
            <div style="font-size:1.8rem;margin-bottom:8px;">📦</div>
            <div style="font-size:.86rem;margin-bottom:12px;">Belum ada nominal untuk <?=htmlspecialchars($game['name'])?></div>
            <button class="btn-gold" data-modal-open="modal-add" style="padding:8px 16px;font-size:.8rem;">+ Tambah Nominal</button>
          </td>
        </tr>
        <?php else: foreach ($products as $i => $p):
          $profit     = (int)$p['price_sell'] - (int)($p['price_cost'] ?? 0);
$margin     = ($p['price_cost'] ?? 0) > 0 ? round(($profit / $p['price_cost']) * 100, 1) : 0;
          $profitColor = $profit > 0 ? '#34d399' : ($profit < 0 ? '#f87171' : '#8892a4');
        ?>
        <tr>
          <td style="color:var(--t3);font-size:.76rem;"><?=$i+1?></td>
          <td>
            <div style="font-weight:600;font-size:.84rem;"><?=htmlspecialchars($p['name'])?></div>
            <?php if($p['description']): ?>
            <div style="font-size:.7rem;color:var(--t3);margin-top:2px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($p['description'])?></div>
            <?php endif; ?>
          </td>
          <td>
            <?php if($p['digi_code']): ?>
            <code style="font-size:.71rem;background:var(--card2);padding:2px 6px;border-radius:4px;color:var(--red);"><?=htmlspecialchars($p['digi_code'])?></code>
            <?php else: ?>
            <span style="color:var(--t3);font-size:.75rem;">—</span>
            <?php endif; ?>
          </td>
          <td style="text-align:right;font-size:.82rem;color:var(--t2);"><?=$p['price_cost']>0?formatRupiah($p['price_cost']):'—'?></td>
          <td style="text-align:right;font-weight:700;color:var(--gold);"><?=formatRupiah($p['price_sell'])?></td>
          <td style="text-align:right;">
            <span style="font-size:.78rem;font-weight:700;color:<?=$profitColor?>;">
              <?=$profit>=0?'+':''?><?=formatRupiah($profit)?>
              <?php if($p['price_cost']>0): ?>
              <div style="font-size:.65rem;color:var(--t3);font-weight:400;"><?=$margin?>%</div>
              <?php endif; ?>
            </span>
          </td>
          <td style="text-align:center;color:var(--t3);font-size:.8rem;"><?=$p['sort_order']?></td>
          <td>
            <?php if($p['is_active']): ?>
            <span class="badge badge-success" style="font-size:.65rem;">Aktif</span>
            <?php else: ?>
            <span class="badge badge-failed" style="font-size:.65rem;">Nonaktif</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
              <button class="btn-sm btn-sm-edit"
                data-id="<?=(int)$p['id']?>"
                data-name="<?=htmlspecialchars($p['name'],ENT_QUOTES)?>"
                data-desc="<?=htmlspecialchars($p['description']??'',ENT_QUOTES)?>"
                data-cost="<?=(int)$p['price_cost']?>"
                data-sell="<?=(int)$p['price_sell']?>"
                data-digi="<?=htmlspecialchars($p['digi_code']??'',ENT_QUOTES)?>"
                data-sort="<?=(int)$p['sort_order']?>"
                data-active="<?=(int)$p['is_active']?>"
                onclick="openEditProduct(this)">✏️</button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?=$p['id']?>">
                <input type="hidden" name="current" value="<?=$p['is_active']?>">
                <button class="btn-sm <?=$p['is_active']?'btn-sm-danger':'btn-sm-edit'?>"><?=$p['is_active']?'Nonaktif':'Aktif'?></button>
              </form>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?=$p['id']?>">
                <button type="submit" class="btn-sm btn-sm-danger" onclick="return confirm('Hapus produk ini?')">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Summary -->
  <?php if (!empty($products)): ?>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:14px;">
    <?php
    $totalProduks = count($products);
    $aktif        = count(array_filter($products, fn($p) => $p['is_active']));
    $minHarga     = min(array_column($products, 'price_sell'));
    $maxHarga     = max(array_column($products, 'price_sell'));
    ?>
    <div style="background:var(--card);border:1px solid var(--b1);border-radius:8px;padding:12px 14px;text-align:center;">
      <div style="font-size:.6rem;text-transform:uppercase;letter-spacing:.6px;color:var(--t3);margin-bottom:4px;">Total Nominal</div>
      <div style="font-size:1.3rem;font-weight:700;color:var(--t1);"><?=$totalProduks?></div>
    </div>
    <div style="background:var(--card);border:1px solid var(--b1);border-radius:8px;padding:12px 14px;text-align:center;">
      <div style="font-size:.6rem;text-transform:uppercase;letter-spacing:.6px;color:var(--t3);margin-bottom:4px;">Aktif</div>
      <div style="font-size:1.3rem;font-weight:700;color:#34d399;"><?=$aktif?></div>
    </div>
    <div style="background:var(--card);border:1px solid var(--b1);border-radius:8px;padding:12px 14px;text-align:center;">
      <div style="font-size:.6rem;text-transform:uppercase;letter-spacing:.6px;color:var(--t3);margin-bottom:4px;">Range Harga</div>
      <div style="font-size:.82rem;font-weight:700;color:var(--gold);"><?=formatRupiah($minHarga)?> – <?=formatRupiah($maxHarga)?></div>
    </div>
  </div>
  <?php endif; ?>

</div>
</div>

<!-- Modal Tambah -->
<div class="modal" id="modal-add">
  <div class="modal-box" style="max-width:540px;">
    <div class="modal-head">
      <h3>➕ Tambah Nominal — <?=htmlspecialchars($game['name'])?></h3>
      <button class="modal-close" data-modal-close="modal-add">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="add">
      <div class="fg">
        <label class="flabel">Nama Produk <span class="req">*</span></label>
        <input type="text" name="name" class="finput" required placeholder="Contoh: 100 Diamond, 500 UC, 1 Bulan Netflix..."/>
      </div>
      <div class="fg">
        <label class="flabel">Deskripsi (opsional)</label>
        <input type="text" name="description" class="finput" placeholder="Keterangan tambahan"/>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg">
          <label class="flabel">Harga Modal (Rp)</label>
          <input type="number" name="price_cost" class="finput" value="0" min="0" placeholder="0"/>
          <div class="fhint">Harga beli dari DigiFlazz</div>
        </div>
        <div class="fg">
          <label class="flabel">Harga Jual (Rp) <span class="req">*</span></label>
          <input type="number" name="price_sell" class="finput" required value="0" min="0" placeholder="0"/>
          <div class="fhint">Harga yang dibayar customer</div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
        <div class="fg">
          <label class="flabel">SKU / Digi Code (opsional)</label>
          <input type="text" name="digi_code" class="finput" placeholder="Contoh: ml-86-diamond"/>
          <div class="fhint">Kode produk DigiFlazz untuk auto top up</div>
        </div>
        <div class="fg">
          <label class="flabel">Sort Order</label>
          <input type="number" name="sort_order" class="finput" value="0"/>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Produk</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-add" style="flex:1;border-radius:10px;padding:12px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal" id="modal-edit">
  <div class="modal-box" style="max-width:540px;">
    <div class="modal-head">
      <h3>✏️ Edit Nominal</h3>
      <button class="modal-close" data-modal-close="modal-edit">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="ep-id">
      <div class="fg">
        <label class="flabel">Nama Produk <span class="req">*</span></label>
        <input type="text" name="name" id="ep-name" class="finput" required/>
      </div>
      <div class="fg">
        <label class="flabel">Deskripsi</label>
        <input type="text" name="description" id="ep-desc" class="finput"/>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg">
          <label class="flabel">Harga Modal (Rp)</label>
          <input type="number" name="price_cost" id="ep-cost" class="finput" min="0"/>
        </div>
        <div class="fg">
          <label class="flabel">Harga Jual (Rp) <span class="req">*</span></label>
          <input type="number" name="price_sell" id="ep-sell" class="finput" required min="0"/>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
        <div class="fg">
          <label class="flabel">SKU / Digi Code</label>
          <input type="text" name="digi_code" id="ep-digi" class="finput"/>
        </div>
        <div class="fg">
          <label class="flabel">Sort Order</label>
          <input type="number" name="sort_order" id="ep-sort" class="finput"/>
        </div>
      </div>
      <div class="fg" style="display:flex;align-items:center;gap:10px;">
        <input type="checkbox" name="is_active" id="ep-active" value="1" style="width:auto;min-height:auto;"/>
        <label for="ep-active" class="flabel" style="margin:0;cursor:pointer;">Produk aktif (tampil di halaman game)</label>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Perubahan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-edit" style="flex:1;border-radius:10px;padding:12px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditProduct(btn) {
  document.getElementById('ep-id').value      = btn.dataset.id;
  document.getElementById('ep-name').value    = btn.dataset.name;
  document.getElementById('ep-desc').value    = btn.dataset.desc;
  document.getElementById('ep-cost').value    = btn.dataset.cost;
  document.getElementById('ep-sell').value    = btn.dataset.sell;
  document.getElementById('ep-digi').value    = btn.dataset.digi;
  document.getElementById('ep-sort').value    = btn.dataset.sort;
  document.getElementById('ep-active').checked = btn.dataset.active === '1';
  window.openModal('modal-edit');
}
</script>

<?php include __DIR__.'/../includes/footer.php'; ?>