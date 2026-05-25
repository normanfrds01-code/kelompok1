<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['admin']);
$pageTitle = 'Kelola Explore — Admin';
$db = db();
$tab = $_GET['tab'] ?? 'artikel';

/* ══ POST HANDLER ══ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $act = $_POST['action'] ?? '';

    /* ─ ARTIKEL ─ */
    if ($act === 'add_article') {
        $db->prepare("INSERT INTO explore_articles (title,category,game_name,emoji,summary,url,read_time,published_at,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?,?,1)")
           ->execute([
               trim($_POST['title'] ?? ''),
               $_POST['category'] ?? 'artikel',
               trim($_POST['game_name'] ?? ''),
               trim($_POST['emoji'] ?? '🎮'),
               trim($_POST['summary'] ?? ''),
               trim($_POST['url'] ?? '') ?: null,
               (int)($_POST['read_time'] ?? 3),
               $_POST['published_at'] ?: date('Y-m-d'),
               (int)($_POST['sort_order'] ?? 0),
           ]);
        setFlash('success', 'Artikel berhasil ditambahkan.');
    }
    elseif ($act === 'edit_article') {
        $id = (int)$_POST['id'];
        $db->prepare("UPDATE explore_articles SET title=?,category=?,game_name=?,emoji=?,summary=?,url=?,read_time=?,published_at=?,sort_order=?,is_active=? WHERE id=?")
           ->execute([
               trim($_POST['title'] ?? ''),
               $_POST['category'] ?? 'artikel',
               trim($_POST['game_name'] ?? ''),
               trim($_POST['emoji'] ?? '🎮'),
               trim($_POST['summary'] ?? ''),
               trim($_POST['url'] ?? '') ?: null,
               (int)($_POST['read_time'] ?? 3),
               $_POST['published_at'] ?: date('Y-m-d'),
               (int)($_POST['sort_order'] ?? 0),
               (int)($_POST['is_active'] ?? 1),
               $id,
           ]);
        setFlash('success', 'Artikel berhasil diperbarui.');
    }
    elseif ($act === 'delete_article') {
        $db->prepare("DELETE FROM explore_articles WHERE id=?")->execute([(int)$_POST['id']]);
        setFlash('success', 'Artikel dihapus.');
    }
    elseif ($act === 'toggle_article') {
        $id = (int)$_POST['id']; $cur = (int)$_POST['current'];
        $db->prepare("UPDATE explore_articles SET is_active=? WHERE id=?")->execute([$cur ? 0 : 1, $id]);
        setFlash('success', 'Status artikel diperbarui.');
    }

    /* ─ TURNAMEN ─ */
    elseif ($act === 'add_tournament') {
        $db->prepare("INSERT INTO explore_tournaments (name,game,emoji,date_range,prize,status,color,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?,1)")
           ->execute([
               trim($_POST['name'] ?? ''),
               trim($_POST['game'] ?? ''),
               trim($_POST['emoji'] ?? '🏆'),
               trim($_POST['date_range'] ?? ''),
               trim($_POST['prize'] ?? ''),
               $_POST['status'] ?? 'upcoming',
               trim($_POST['color'] ?? '#38bdf8'),
               (int)($_POST['sort_order'] ?? 0),
           ]);
        setFlash('success', 'Turnamen berhasil ditambahkan.');
    }
    elseif ($act === 'edit_tournament') {
        $id = (int)$_POST['id'];
        $db->prepare("UPDATE explore_tournaments SET name=?,game=?,emoji=?,date_range=?,prize=?,status=?,color=?,sort_order=?,is_active=? WHERE id=?")
           ->execute([
               trim($_POST['name'] ?? ''),
               trim($_POST['game'] ?? ''),
               trim($_POST['emoji'] ?? '🏆'),
               trim($_POST['date_range'] ?? ''),
               trim($_POST['prize'] ?? ''),
               $_POST['status'] ?? 'upcoming',
               trim($_POST['color'] ?? '#38bdf8'),
               (int)($_POST['sort_order'] ?? 0),
               (int)($_POST['is_active'] ?? 1),
               $id,
           ]);
        setFlash('success', 'Turnamen berhasil diperbarui.');
    }
    elseif ($act === 'delete_tournament') {
        $db->prepare("DELETE FROM explore_tournaments WHERE id=?")->execute([(int)$_POST['id']]);
        setFlash('success', 'Turnamen dihapus.');
    }

    header('Location: ' . asset('admin/explore.php') . '?tab=' . $tab);
    exit;
}

/* ══ FETCH DATA ══ */
$articles    = $db->query("SELECT * FROM explore_articles ORDER BY sort_order ASC, created_at DESC")->fetchAll();
$tournaments = $db->query("SELECT * FROM explore_tournaments ORDER BY sort_order ASC")->fetchAll();

$cats = ['artikel'=>'Artikel','tips'=>'Tips & Trik','turnamen'=>'Turnamen','review'=>'Review','update'=>'Update Game','promo'=>'Promo & Event','komunitas'=>'Komunitas'];
$statusMap = ['live'=>['badge-success','🔴 Live'],'upcoming'=>['badge-process','Segera'],'ended'=>['badge-failed','Selesai']];

include __DIR__ . '/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-main">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
    <div class="admin-title" style="margin:0;display:flex;align-items:center;gap:8px;">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      Kelola Explore
    </div>
    <a href="<?=asset('pages/explore.php')?>" target="_blank" class="btn-sm btn-sm-edit" style="display:inline-flex;align-items:center;gap:5px;">
      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Lihat Halaman
    </a>
  </div>

  <!-- Tabs -->
  <div style="display:flex;gap:6px;margin-bottom:18px;border-bottom:1px solid var(--b1);padding-bottom:0;">
    <a href="?tab=artikel" style="padding:8px 18px;font-size:.82rem;font-weight:600;border-radius:8px 8px 0 0;border:1px solid var(--b1);border-bottom:none;text-decoration:none;
      <?=$tab==='artikel'?'background:var(--card);color:var(--red);margin-bottom:-1px;z-index:1;position:relative;':'color:var(--t3);background:transparent;'?>">
      📰 Artikel <span style="font-size:.7rem;background:var(--card2);padding:1px 6px;border-radius:10px;margin-left:4px;"><?=count($articles)?></span>
    </a>
    <a href="?tab=turnamen" style="padding:8px 18px;font-size:.82rem;font-weight:600;border-radius:8px 8px 0 0;border:1px solid var(--b1);border-bottom:none;text-decoration:none;
      <?=$tab==='turnamen'?'background:var(--card);color:var(--red);margin-bottom:-1px;z-index:1;position:relative;':'color:var(--t3);background:transparent;'?>">
      🏆 Turnamen <span style="font-size:.7rem;background:var(--card2);padding:1px 6px;border-radius:10px;margin-left:4px;"><?=count($tournaments)?></span>
    </a>
  </div>

  <?php if ($tab === 'artikel'): ?>
  <!-- ══ TAB ARTIKEL ══ -->
  <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
    <button class="btn-gold" data-modal-open="modal-add-article" style="padding:8px 18px;font-size:.84rem;display:flex;align-items:center;gap:6px;">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Artikel
    </button>
  </div>

  <div class="admin-card">
    <div class="table-wrap">
      <table class="dtable">
        <thead><tr><th>Judul</th><th>Kategori</th><th>Game</th><th>Tanggal</th><th>Waktu Baca</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if (empty($articles)): ?>
        <tr><td colspan="7" style="text-align:center;padding:36px;color:var(--t3);">Belum ada artikel.</td></tr>
        <?php else: foreach ($articles as $a): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="font-size:1.2rem;"><?=htmlspecialchars($a['emoji'])?></span>
              <div>
                <div style="font-weight:600;font-size:.84rem;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($a['title'])?></div>
                <?php if($a['summary']): ?>
                <div style="font-size:.7rem;color:var(--t3);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($a['summary'])?></div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td><span class="badge badge-process" style="font-size:.67rem;"><?=$cats[$a['category']] ?? $a['category']?></span></td>
          <td style="font-size:.78rem;color:var(--t2);"><?=htmlspecialchars($a['game_name'] ?? '—')?></td>
          <td style="font-size:.75rem;color:var(--t3);"><?=$a['published_at'] ? date('d M Y', strtotime($a['published_at'])) : '—'?></td>
          <td style="font-size:.75rem;color:var(--t3);"><?=$a['read_time']?> mnt</td>
          <td>
            <?php if ($a['is_active']): ?>
            <span class="badge badge-success" style="font-size:.67rem;">Aktif</span>
            <?php else: ?>
            <span class="badge badge-failed" style="font-size:.67rem;">Nonaktif</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:5px;">
              <button class="btn-sm btn-sm-edit"
                data-id="<?=(int)$a['id']?>"
                data-title="<?=htmlspecialchars($a['title'],ENT_QUOTES)?>"
                data-category="<?=htmlspecialchars($a['category'],ENT_QUOTES)?>"
                data-game="<?=htmlspecialchars($a['game_name']??'',ENT_QUOTES)?>"
                data-emoji="<?=htmlspecialchars($a['emoji'],ENT_QUOTES)?>"
                data-summary="<?=htmlspecialchars($a['summary']??'',ENT_QUOTES)?>"
                data-url="<?=htmlspecialchars($a['url']??'',ENT_QUOTES)?>"
                data-readtime="<?=(int)$a['read_time']?>"
                data-date="<?=htmlspecialchars($a['published_at']??'',ENT_QUOTES)?>"
                data-sort="<?=(int)$a['sort_order']?>"
                data-active="<?=(int)$a['is_active']?>"
                onclick="openEditArticle(this)">✏️ Edit</button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="toggle_article">
                <input type="hidden" name="id" value="<?=$a['id']?>">
                <input type="hidden" name="current" value="<?=$a['is_active']?>">
                <button class="btn-sm <?=$a['is_active']?'btn-sm-danger':'btn-sm-edit'?>"><?=$a['is_active']?'Nonaktif':'Aktifkan'?></button>
              </form>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="delete_article">
                <input type="hidden" name="id" value="<?=$a['id']?>">
                <button type="submit" class="btn-sm btn-sm-danger" onclick="return confirm('Hapus artikel ini?')">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php elseif ($tab === 'turnamen'): ?>
  <!-- ══ TAB TURNAMEN ══ -->
  <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
    <button class="btn-gold" data-modal-open="modal-add-tournament" style="padding:8px 18px;font-size:.84rem;display:flex;align-items:center;gap:6px;">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Turnamen
    </button>
  </div>

  <div class="admin-card">
    <div class="table-wrap">
      <table class="dtable">
        <thead><tr><th>Nama</th><th>Game</th><th>Tanggal</th><th>Prize</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if (empty($tournaments)): ?>
        <tr><td colspan="6" style="text-align:center;padding:36px;color:var(--t3);">Belum ada turnamen.</td></tr>
        <?php else: foreach ($tournaments as $t):
          $sm = $statusMap[$t['status']] ?? ['badge-pending','—'];
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="font-size:1.2rem;"><?=htmlspecialchars($t['emoji'])?></span>
              <div style="font-weight:600;font-size:.84rem;"><?=htmlspecialchars($t['name'])?></div>
            </div>
          </td>
          <td style="font-size:.8rem;color:var(--t2);"><?=htmlspecialchars($t['game'])?></td>
          <td style="font-size:.75rem;color:var(--t3);"><?=htmlspecialchars($t['date_range'] ?? '—')?></td>
          <td style="font-size:.78rem;color:var(--gold);font-weight:700;"><?=htmlspecialchars($t['prize'] ?? '—')?></td>
          <td><span class="badge <?=$sm[0]?>" style="font-size:.67rem;"><?=$sm[1]?></span></td>
          <td>
            <div style="display:flex;gap:5px;">
              <button class="btn-sm btn-sm-edit"
                data-id="<?=(int)$t['id']?>"
                data-name="<?=htmlspecialchars($t['name'],ENT_QUOTES)?>"
                data-game="<?=htmlspecialchars($t['game'],ENT_QUOTES)?>"
                data-emoji="<?=htmlspecialchars($t['emoji'],ENT_QUOTES)?>"
                data-daterange="<?=htmlspecialchars($t['date_range']??'',ENT_QUOTES)?>"
                data-prize="<?=htmlspecialchars($t['prize']??'',ENT_QUOTES)?>"
                data-status="<?=htmlspecialchars($t['status'],ENT_QUOTES)?>"
                data-color="<?=htmlspecialchars($t['color']??'#38bdf8',ENT_QUOTES)?>"
                data-sort="<?=(int)$t['sort_order']?>"
                data-active="<?=(int)$t['is_active']?>"
                onclick="openEditTournament(this)">✏️ Edit</button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="delete_tournament">
                <input type="hidden" name="id" value="<?=$t['id']?>">
                <button type="submit" class="btn-sm btn-sm-danger" onclick="return confirm('Hapus turnamen ini?')">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</div>
</div>

<!-- ══ MODAL TAMBAH ARTIKEL ══ -->
<div class="modal" id="modal-add-article">
  <div class="modal-box" style="max-width:580px;">
    <div class="modal-head"><h3>📰 Tambah Artikel</h3><button class="modal-close" data-modal-close="modal-add-article">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="add_article">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg" style="grid-column:1/-1;"><label class="flabel">Judul <span class="req">*</span></label><input type="text" name="title" class="finput" required placeholder="Judul artikel..."/></div>
        <div class="fg"><label class="flabel">Kategori</label>
          <select name="category" class="finput">
            <?php foreach ($cats as $k => $v): ?><option value="<?=$k?>"><?=$v?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label class="flabel">Nama Game</label><input type="text" name="game_name" class="finput" placeholder="Mobile Legends..."/></div>
        <div class="fg"><label class="flabel">Emoji</label><input type="text" name="emoji" class="finput" value="🎮" maxlength="5"/></div>
        <div class="fg"><label class="flabel">Waktu Baca (menit)</label><input type="number" name="read_time" class="finput" value="3" min="1" max="60"/></div>
        <div class="fg" style="grid-column:1/-1;"><label class="flabel">Ringkasan</label><textarea name="summary" class="finput" rows="3" placeholder="Ringkasan singkat artikel..."></textarea></div>
        <div class="fg" style="grid-column:1/-1;"><label class="flabel">URL Artikel (opsional)</label><input type="url" name="url" class="finput" placeholder="https://..."/></div>
        <div class="fg"><label class="flabel">Tanggal Terbit</label><input type="date" name="published_at" class="finput" value="<?=date('Y-m-d')?>"/></div>
        <div class="fg"><label class="flabel">Sort Order</label><input type="number" name="sort_order" class="finput" value="0"/></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Artikel</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-add-article" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ MODAL EDIT ARTIKEL ══ -->
<div class="modal" id="modal-edit-article">
  <div class="modal-box" style="max-width:580px;">
    <div class="modal-head"><h3>✏️ Edit Artikel</h3><button class="modal-close" data-modal-close="modal-edit-article">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="edit_article">
      <input type="hidden" name="id" id="ea-id">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg" style="grid-column:1/-1;"><label class="flabel">Judul <span class="req">*</span></label><input type="text" name="title" id="ea-title" class="finput" required/></div>
        <div class="fg"><label class="flabel">Kategori</label>
          <select name="category" id="ea-category" class="finput">
            <?php foreach ($cats as $k => $v): ?><option value="<?=$k?>"><?=$v?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label class="flabel">Nama Game</label><input type="text" name="game_name" id="ea-game" class="finput"/></div>
        <div class="fg"><label class="flabel">Emoji</label><input type="text" name="emoji" id="ea-emoji" class="finput" maxlength="5"/></div>
        <div class="fg"><label class="flabel">Waktu Baca (menit)</label><input type="number" name="read_time" id="ea-readtime" class="finput" min="1" max="60"/></div>
        <div class="fg" style="grid-column:1/-1;"><label class="flabel">Ringkasan</label><textarea name="summary" id="ea-summary" class="finput" rows="3"></textarea></div>
        <div class="fg" style="grid-column:1/-1;"><label class="flabel">URL Artikel</label><input type="url" name="url" id="ea-url" class="finput"/></div>
        <div class="fg"><label class="flabel">Tanggal Terbit</label><input type="date" name="published_at" id="ea-date" class="finput"/></div>
        <div class="fg"><label class="flabel">Sort Order</label><input type="number" name="sort_order" id="ea-sort" class="finput"/></div>
        <div class="fg" style="display:flex;align-items:flex-end;padding-bottom:2px;">
          <label style="display:flex;align-items:center;gap:8px;font-size:.86rem;color:var(--t2);cursor:pointer;">
            <input type="checkbox" name="is_active" id="ea-active" value="1"> Aktif
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Perubahan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-edit-article" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ MODAL TAMBAH TURNAMEN ══ -->
<div class="modal" id="modal-add-tournament">
  <div class="modal-box" style="max-width:520px;">
    <div class="modal-head"><h3>🏆 Tambah Turnamen</h3><button class="modal-close" data-modal-close="modal-add-tournament">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="add_tournament">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg" style="grid-column:1/-1;"><label class="flabel">Nama Turnamen <span class="req">*</span></label><input type="text" name="name" class="finput" required placeholder="MPL ID Season 18..."/></div>
        <div class="fg"><label class="flabel">Game <span class="req">*</span></label><input type="text" name="game" class="finput" required placeholder="Mobile Legends..."/></div>
        <div class="fg"><label class="flabel">Emoji</label><input type="text" name="emoji" class="finput" value="🏆" maxlength="5"/></div>
        <div class="fg"><label class="flabel">Periode</label><input type="text" name="date_range" class="finput" placeholder="Jun — Aug 2026"/></div>
        <div class="fg"><label class="flabel">Prize Pool</label><input type="text" name="prize" class="finput" placeholder="Rp 1 Miliar"/></div>
        <div class="fg"><label class="flabel">Status</label>
          <select name="status" class="finput">
            <option value="upcoming">Segera</option>
            <option value="live">Live</option>
            <option value="ended">Selesai</option>
          </select>
        </div>
        <div class="fg"><label class="flabel">Warna Badge</label><input type="color" name="color" class="finput" value="#38bdf8" style="height:40px;padding:4px;"/></div>
        <div class="fg"><label class="flabel">Sort Order</label><input type="number" name="sort_order" class="finput" value="0"/></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Turnamen</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-add-tournament" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ MODAL EDIT TURNAMEN ══ -->
<div class="modal" id="modal-edit-tournament">
  <div class="modal-box" style="max-width:520px;">
    <div class="modal-head"><h3>✏️ Edit Turnamen</h3><button class="modal-close" data-modal-close="modal-edit-tournament">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="edit_tournament">
      <input type="hidden" name="id" id="et-id">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg" style="grid-column:1/-1;"><label class="flabel">Nama Turnamen <span class="req">*</span></label><input type="text" name="name" id="et-name" class="finput" required/></div>
        <div class="fg"><label class="flabel">Game</label><input type="text" name="game" id="et-game" class="finput"/></div>
        <div class="fg"><label class="flabel">Emoji</label><input type="text" name="emoji" id="et-emoji" class="finput" maxlength="5"/></div>
        <div class="fg"><label class="flabel">Periode</label><input type="text" name="date_range" id="et-daterange" class="finput"/></div>
        <div class="fg"><label class="flabel">Prize Pool</label><input type="text" name="prize" id="et-prize" class="finput"/></div>
        <div class="fg"><label class="flabel">Status</label>
          <select name="status" id="et-status" class="finput">
            <option value="upcoming">Segera</option>
            <option value="live">Live</option>
            <option value="ended">Selesai</option>
          </select>
        </div>
        <div class="fg"><label class="flabel">Warna Badge</label><input type="color" name="color" id="et-color" class="finput" style="height:40px;padding:4px;"/></div>
        <div class="fg"><label class="flabel">Sort Order</label><input type="number" name="sort_order" id="et-sort" class="finput"/></div>
        <div class="fg" style="display:flex;align-items:flex-end;padding-bottom:2px;">
          <label style="display:flex;align-items:center;gap:8px;font-size:.86rem;color:var(--t2);cursor:pointer;">
            <input type="checkbox" name="is_active" id="et-active" value="1"> Aktif
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan Perubahan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-edit-tournament" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditArticle(btn) {
  document.getElementById('ea-id').value       = btn.dataset.id;
  document.getElementById('ea-title').value    = btn.dataset.title;
  document.getElementById('ea-category').value = btn.dataset.category;
  document.getElementById('ea-game').value     = btn.dataset.game;
  document.getElementById('ea-emoji').value    = btn.dataset.emoji;
  document.getElementById('ea-summary').value  = btn.dataset.summary;
  document.getElementById('ea-url').value      = btn.dataset.url;
  document.getElementById('ea-readtime').value = btn.dataset.readtime;
  document.getElementById('ea-date').value     = btn.dataset.date;
  document.getElementById('ea-sort').value     = btn.dataset.sort;
  document.getElementById('ea-active').checked = btn.dataset.active === '1';
  window.openModal('modal-edit-article');
}
function openEditTournament(btn) {
  document.getElementById('et-id').value        = btn.dataset.id;
  document.getElementById('et-name').value      = btn.dataset.name;
  document.getElementById('et-game').value      = btn.dataset.game;
  document.getElementById('et-emoji').value     = btn.dataset.emoji;
  document.getElementById('et-daterange').value = btn.dataset.daterange;
  document.getElementById('et-prize').value     = btn.dataset.prize;
  document.getElementById('et-status').value    = btn.dataset.status;
  document.getElementById('et-color').value     = btn.dataset.color;
  document.getElementById('et-sort').value      = btn.dataset.sort;
  document.getElementById('et-active').checked  = btn.dataset.active === '1';
  window.openModal('modal-edit-tournament');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>