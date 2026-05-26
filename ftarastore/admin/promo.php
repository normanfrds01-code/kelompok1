<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['admin']);
$pageTitle = 'Kelola Promo & Event — Admin';
$db = db();

/* ══ POST HANDLER ══ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $act = $_POST['action'] ?? '';

    if ($act === 'add') {
        $img = uploadIconImage('image', 'promo', 'promo');
        $db->prepare("INSERT INTO promo_events (emoji,image,color,game,title,description,period,link_url,status,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,1)")
           ->execute([
               trim($_POST['emoji'] ?? '🎮') ?: '🎮',
               $img ?: null,
               trim($_POST['color'] ?? '') ?: 'rgba(56,189,248,.1)',
               trim($_POST['game'] ?? '') ?: null,
               trim($_POST['title'] ?? ''),
               trim($_POST['description'] ?? '') ?: null,
               trim($_POST['period'] ?? '') ?: null,
               trim($_POST['link_url'] ?? '') ?: null,
               $_POST['status'] ?? 'live',
               (int)($_POST['sort_order'] ?? 0),
           ]);
        setFlash('success', 'Event berhasil ditambahkan.');
    }
    elseif ($act === 'edit') {
        $exImg = $db->prepare("SELECT image FROM promo_events WHERE id=?");
        $exImg->execute([(int)$_POST['id']]);
        $img = uploadIconImage('image', 'promo', 'promo', (string)($exImg->fetchColumn() ?: ''));
        $db->prepare("UPDATE promo_events SET emoji=?,image=?,color=?,game=?,title=?,description=?,period=?,link_url=?,status=?,sort_order=?,is_active=? WHERE id=?")
           ->execute([
               trim($_POST['emoji'] ?? '🎮') ?: '🎮',
               $img ?: null,
               trim($_POST['color'] ?? '') ?: 'rgba(56,189,248,.1)',
               trim($_POST['game'] ?? '') ?: null,
               trim($_POST['title'] ?? ''),
               trim($_POST['description'] ?? '') ?: null,
               trim($_POST['period'] ?? '') ?: null,
               trim($_POST['link_url'] ?? '') ?: null,
               $_POST['status'] ?? 'live',
               (int)($_POST['sort_order'] ?? 0),
               (int)($_POST['is_active'] ?? 1),
               (int)$_POST['id'],
           ]);
        setFlash('success', 'Event diperbarui.');
    }
    elseif ($act === 'delete') {
        $db->prepare("DELETE FROM promo_events WHERE id=?")->execute([(int)$_POST['id']]);
        setFlash('success', 'Event dihapus.');
    }
    elseif ($act === 'toggle') {
        $cur = (int)$_POST['current'];
        $db->prepare("UPDATE promo_events SET is_active=? WHERE id=?")->execute([$cur ? 0 : 1, (int)$_POST['id']]);
    }
    header('Location: '.asset('admin/promo.php')); exit;
}

/* ══ FETCH ══ */
$tableExists = true;
try { $db->query("SELECT 1 FROM promo_events LIMIT 1"); } catch (\Exception $e) { $tableExists = false; }
$events = $tableExists ? $db->query("SELECT * FROM promo_events ORDER BY sort_order ASC, id DESC")->fetchAll() : [];

// Hitung voucher aktif (diskon dikelola di halaman Voucher)
$voucherActive = 0;
try { $voucherActive = (int)$db->query("SELECT COUNT(*) FROM vouchers WHERE is_active=1")->fetchColumn(); } catch (\Exception $e) {}

include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

  <div class="admin-title" style="margin-bottom:16px;">🎁 Kelola Promo &amp; Event</div>

  <!-- Info bar -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px;">
    <div style="background:var(--card);border:1px solid var(--b1);border-radius:10px;padding:14px 16px;">
      <div style="font-size:.61rem;text-transform:uppercase;letter-spacing:.6px;color:var(--t3);margin-bottom:6px;">Total Event</div>
      <div style="font-size:1.3rem;font-weight:800;color:#e8eaf0;"><?=count($events)?></div>
    </div>
    <div style="background:var(--card);border:1px solid var(--b1);border-radius:10px;padding:14px 16px;">
      <div style="font-size:.61rem;text-transform:uppercase;letter-spacing:.6px;color:var(--t3);margin-bottom:6px;">Event Live</div>
      <div style="font-size:1.3rem;font-weight:800;color:#34d399;"><?=count(array_filter($events, fn($e)=>$e['status']==='live' && $e['is_active']))?></div>
    </div>
    <a href="<?=asset('admin/vouchers.php')?>" style="background:var(--card);border:1px solid var(--b1);border-radius:10px;padding:14px 16px;text-decoration:none;">
      <div style="font-size:.61rem;text-transform:uppercase;letter-spacing:.6px;color:var(--t3);margin-bottom:6px;">Diskon / Voucher Aktif</div>
      <div style="font-size:1.3rem;font-weight:800;color:var(--gold);"><?=$voucherActive?> <span style="font-size:.7rem;color:var(--t3);font-weight:600;">→ kelola</span></div>
    </a>
  </div>

  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
    <button class="btn-gold" data-modal-open="modal-add" style="padding:8px 18px;font-size:.84rem;display:flex;align-items:center;gap:6px;">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Event
    </button>
    <?php if (!$tableExists): ?>
    <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:10px 14px;font-size:.79rem;color:#f87171;">
      ⚠️ Tabel <code>promo_events</code> belum ada. Jalankan SQL migration terlebih dahulu.
    </div>
    <?php endif; ?>
  </div>

  <div class="admin-card">
    <div class="table-wrap">
      <table class="dtable">
        <thead>
          <tr>
            <th style="width:50px;">Icon</th>
            <th>Judul Event</th>
            <th>Game</th>
            <th>Periode</th>
            <th>Status</th>
            <th style="text-align:center;">Urutan</th>
            <th>Aktif</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($events)): ?>
        <tr><td colspan="8" style="text-align:center;padding:42px;color:var(--t3);">
          <div style="font-size:1.8rem;margin-bottom:6px;">🎁</div>Belum ada event. Klik "Tambah Event".
        </td></tr>
        <?php else: foreach ($events as $e): ?>
        <tr>
          <td style="text-align:center;"><?=iconImg($e['image'] ?? null, $e['emoji'], 30, 8)?></td>
          <td>
            <div style="font-weight:600;font-size:.85rem;"><?=htmlspecialchars($e['title'])?></div>
            <?php if($e['description']): ?><div style="font-size:.7rem;color:var(--t3);margin-top:2px;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($e['description'])?></div><?php endif; ?>
          </td>
          <td style="font-size:.78rem;color:var(--red);font-weight:600;"><?=htmlspecialchars($e['game'] ?? '—')?></td>
          <td style="font-size:.76rem;color:var(--t2);"><?=htmlspecialchars($e['period'] ?? '—')?></td>
          <td>
            <?php
              $stBadge = ['live'=>['badge-success','● Live'],'upcoming'=>['badge-process','⏳ Segera'],'ended'=>['badge-failed','Selesai']];
              [$bc,$bl] = $stBadge[$e['status']] ?? $stBadge['live'];
            ?>
            <span class="badge <?=$bc?>" style="font-size:.65rem;"><?=$bl?></span>
          </td>
          <td style="text-align:center;color:var(--t3);font-size:.8rem;"><?=$e['sort_order']?></td>
          <td>
            <span class="badge <?=$e['is_active']?'badge-success':'badge-failed'?>" style="font-size:.65rem;"><?=$e['is_active']?'Aktif':'Nonaktif'?></span>
          </td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
              <button class="btn-sm btn-sm-edit"
                data-id="<?=(int)$e['id']?>"
                data-emoji="<?=htmlspecialchars($e['emoji'],ENT_QUOTES)?>"
                data-image="<?=htmlspecialchars($e['image']??'',ENT_QUOTES)?>"
                data-color="<?=htmlspecialchars($e['color'],ENT_QUOTES)?>"
                data-game="<?=htmlspecialchars($e['game']??'',ENT_QUOTES)?>"
                data-title="<?=htmlspecialchars($e['title'],ENT_QUOTES)?>"
                data-desc="<?=htmlspecialchars($e['description']??'',ENT_QUOTES)?>"
                data-period="<?=htmlspecialchars($e['period']??'',ENT_QUOTES)?>"
                data-link="<?=htmlspecialchars($e['link_url']??'',ENT_QUOTES)?>"
                data-status="<?=htmlspecialchars($e['status'],ENT_QUOTES)?>"
                data-sort="<?=(int)$e['sort_order']?>"
                data-active="<?=(int)$e['is_active']?>"
                onclick="openEdit(this)">✏️ Edit</button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?=(int)$e['id']?>">
                <input type="hidden" name="current" value="<?=(int)$e['is_active']?>">
                <button class="btn-sm <?=$e['is_active']?'btn-sm-danger':'btn-sm-edit'?>"><?=$e['is_active']?'Nonaktif':'Aktifkan'?></button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus event ini?')">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?=(int)$e['id']?>">
                <button class="btn-sm btn-sm-danger">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<?php
// Reusable form fields
function promoFields($p = []) {
  $emoji=$p['emoji']??'🎮'; $color=$p['color']??'rgba(56,189,248,.1)'; $game=$p['game']??'';
  $title=$p['title']??''; $desc=$p['desc']??''; $period=$p['period']??''; $link=$p['link']??'';
  $status=$p['status']??'live'; $sort=$p['sort']??0;
  ob_start(); ?>
  <div class="fg"><label class="flabel">Gambar Icon</label><input type="file" name="image" class="finput" accept="image/png,image/jpeg,image/webp,image/gif"/><div class="fhint">Upload gambar (jpg/png/webp, max 2MB). Kosongkan = pakai emoji default.</div></div>
  <div class="fg"><label class="flabel">Judul Event</label><input type="text" name="title" class="finput" value="<?=htmlspecialchars($title)?>" required/></div>
  <div class="fg"><label class="flabel">Nama Game</label><input type="text" name="game" class="finput" value="<?=htmlspecialchars($game)?>" placeholder="cth: Mobile Legends"/></div>
  <div class="fg"><label class="flabel">Deskripsi</label><input type="text" name="description" class="finput" value="<?=htmlspecialchars($desc)?>"/></div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
    <div class="fg"><label class="flabel">Periode</label><input type="text" name="period" class="finput" value="<?=htmlspecialchars($period)?>" placeholder="cth: 17–31 Agustus"/></div>
    <div class="fg"><label class="flabel">Status</label>
      <select name="status" class="finput">
        <option value="live" <?=$status==='live'?'selected':''?>>● Live</option>
        <option value="upcoming" <?=$status==='upcoming'?'selected':''?>>⏳ Segera</option>
        <option value="ended" <?=$status==='ended'?'selected':''?>>Selesai</option>
      </select>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
    <div class="fg"><label class="flabel">Warna Icon (CSS)</label><input type="text" name="color" class="finput" value="<?=htmlspecialchars($color)?>" placeholder="rgba(56,189,248,.1)"/></div>
    <div class="fg"><label class="flabel">Urutan</label><input type="number" name="sort_order" class="finput" value="<?=(int)$sort?>"/></div>
  </div>
  <div class="fg"><label class="flabel">Link Tombol (opsional)</label><input type="text" name="link_url" class="finput" value="<?=htmlspecialchars($link)?>" placeholder="kosongkan = ke beranda"/></div>
  <?php return ob_get_clean();
}
?>

<!-- Modal Tambah -->
<div class="modal" id="modal-add">
  <div class="modal-box" style="max-width:560px;">
    <div class="modal-head"><h3>🎁 Tambah Event</h3><button class="modal-close" data-modal-close="modal-add">✕</button></div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="add">
      <?=promoFields()?>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-add" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal" id="modal-edit">
  <div class="modal-box" style="max-width:560px;">
    <div class="modal-head"><h3>✏️ Edit Event</h3><button class="modal-close" data-modal-close="modal-edit">✕</button></div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="e-id">
      <div class="fg">
        <label class="flabel">Gambar Icon</label>
        <div style="display:flex;align-items:center;gap:10px;">
          <img id="e-img-preview" src="" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:8px;background:var(--card2);display:none;">
          <span id="e-img-emoji" style="font-size:1.6rem;"></span>
          <input type="file" name="image" class="finput" accept="image/png,image/jpeg,image/webp,image/gif" style="flex:1;"/>
        </div>
        <div class="fhint">Upload untuk mengganti. Kosongkan = gambar lama tetap dipakai.</div>
      </div>
      <div class="fg"><label class="flabel">Judul Event</label><input type="text" name="title" id="e-title" class="finput" required/></div>
      <div class="fg"><label class="flabel">Nama Game</label><input type="text" name="game" id="e-game" class="finput"/></div>
      <div class="fg"><label class="flabel">Deskripsi</label><input type="text" name="description" id="e-desc" class="finput"/></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg"><label class="flabel">Periode</label><input type="text" name="period" id="e-period" class="finput"/></div>
        <div class="fg"><label class="flabel">Status</label>
          <select name="status" id="e-status" class="finput">
            <option value="live">● Live</option><option value="upcoming">⏳ Segera</option><option value="ended">Selesai</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
        <div class="fg"><label class="flabel">Warna Icon (CSS)</label><input type="text" name="color" id="e-color" class="finput"/></div>
        <div class="fg"><label class="flabel">Urutan</label><input type="number" name="sort_order" id="e-sort" class="finput"/></div>
      </div>
      <div class="fg"><label class="flabel">Link Tombol (opsional)</label><input type="text" name="link_url" id="e-link" class="finput"/></div>
      <div class="fg"><label class="flabel" style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="is_active" value="1" id="e-active"> Aktif</label></div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Perubahan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-edit" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(btn){
  var d = btn.dataset;
  document.getElementById('e-id').value     = d.id;
  var prev = document.getElementById('e-img-preview');
  var em   = document.getElementById('e-img-emoji');
  if (d.image) { prev.src = d.image; prev.style.display = 'inline-block'; em.textContent = ''; }
  else { prev.style.display = 'none'; em.textContent = d.emoji || '🎮'; }
  document.getElementById('e-color').value  = d.color;
  document.getElementById('e-game').value   = d.game;
  document.getElementById('e-title').value  = d.title;
  document.getElementById('e-desc').value   = d.desc;
  document.getElementById('e-period').value = d.period;
  document.getElementById('e-link').value   = d.link;
  document.getElementById('e-status').value = d.status;
  document.getElementById('e-sort').value   = d.sort;
  document.getElementById('e-active').checked = d.active === '1';
  document.getElementById('modal-edit').classList.add('show');
  document.body.style.overflow='hidden';
}
</script>

<?php include __DIR__.'/../includes/footer.php'; ?>