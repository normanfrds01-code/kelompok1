<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['admin']);
$pageTitle = 'Kelola Reward & Poin — Admin';
$db = db();

function setOpt(PDO $db, string $k, string $v): void {
    $db->prepare("INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=?")->execute([$k,$v,$v]);
}

/* ══ POST HANDLER ══ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $act = $_POST['action'] ?? '';

    if ($act === 'save_tiers') {
        foreach ([
            'reward_earn_per','reward_cb_bronze',
            'reward_min_silver','reward_cb_silver',
            'reward_min_gold','reward_cb_gold',
            'reward_min_platinum','reward_cb_platinum',
        ] as $k) {
            setOpt($db, $k, (string)(int)($_POST[$k] ?? 0));
        }
        setFlash('success', 'Konfigurasi reward berhasil disimpan.');
        header('Location: '.asset('admin/rewards.php')); exit;
    }

    if ($act === 'adjust') {
        $uid   = (int)$_POST['user_id'];
        $delta = (int)$_POST['delta'];
        $note  = trim($_POST['note'] ?? '') ?: 'Penyesuaian poin oleh admin';
        if ($uid > 0 && $delta !== 0) {
            // Pastikan baris user_points ada
            $db->prepare("INSERT IGNORE INTO user_points (user_id,points,level,total_spent) VALUES (?,0,'bronze',0)")->execute([$uid]);
            // Jangan sampai poin minus
            $cur = (int)$db->query("SELECT points FROM user_points WHERE user_id=".$uid)->fetchColumn();
            $new = max(0, $cur + $delta);
            $db->prepare("UPDATE user_points SET points=? WHERE user_id=?")->execute([$new, $uid]);
            $db->prepare("INSERT INTO point_transactions (user_id,type,points,description) VALUES (?,?,?,?)")
               ->execute([$uid, $delta >= 0 ? 'bonus' : 'redeem', $delta, $note]);
            Security::audit('REWARD_ADJUST', "User #$uid poin $delta ($note)");
            setFlash('success', "Poin user #$uid diperbarui ($cur → $new).");
        }
        header('Location: '.asset('admin/rewards.php')); exit;
    }
}

/* ══ FETCH ══ */
$s = [];
foreach ($db->query("SELECT `key`,`value` FROM settings")->fetchAll() as $r) $s[$r['key']] = $r['value'];
$opt = fn($k,$def)=> $s[$k] ?? $def;

// Daftar user + poin
$rows = [];
try {
    $rows = $db->query("
        SELECT u.id, u.name, u.email,
               COALESCE(up.points,0) AS points,
               COALESCE(up.level,'bronze') AS level,
               COALESCE(up.total_spent,0) AS total_spent
        FROM users u
        LEFT JOIN user_points up ON up.user_id = u.id
        WHERE u.role = 'user'
        ORDER BY points DESC, total_spent DESC
        LIMIT 100
    ")->fetchAll();
} catch (\Exception $e) {
    // fallback jika kolom role berbeda
    try { $rows = $db->query("SELECT u.id,u.name,u.email,COALESCE(up.points,0) points,COALESCE(up.level,'bronze') level,COALESCE(up.total_spent,0) total_spent FROM users u LEFT JOIN user_points up ON up.user_id=u.id ORDER BY points DESC LIMIT 100")->fetchAll(); }
    catch (\Exception $e2) { $rows = []; }
}

$levelBadge = [
  'bronze'=>['#cd7f32','🥉 Bronze'], 'silver'=>['#8892a4','🥈 Silver'],
  'gold'=>['#f5a623','🥇 Gold'], 'platinum'=>['#60a5fa','💎 Platinum'],
];

include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

  <div class="admin-title" style="margin-bottom:16px;">🏅 Kelola Reward &amp; Poin</div>

  <!-- Konfigurasi Tier -->
  <div class="admin-card" style="margin-bottom:20px;">
    <div class="admin-card-head"><h3>⚙️ Konfigurasi Level &amp; Cashback</h3></div>
    <form method="POST" style="padding:18px 20px;">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="save_tiers">

      <div class="fg" style="max-width:340px;">
        <label class="flabel">Poin per Rp (1 poin / Rp ...)</label>
        <input type="number" name="reward_earn_per" class="finput" value="<?=htmlspecialchars($opt('reward_earn_per','1000'))?>" min="1"/>
        <div class="fhint">Contoh: 1000 → tiap belanja Rp1.000 dapat 1 poin.</div>
      </div>

      <div style="overflow-x:auto;margin-top:6px;">
      <table class="dtable" style="min-width:520px;">
        <thead><tr><th>Level</th><th>Min. Total Belanja (Rp)</th><th>Cashback (%)</th></tr></thead>
        <tbody>
          <tr>
            <td><span style="color:#cd7f32;font-weight:700;">🥉 Bronze</span></td>
            <td><input type="number" class="finput" value="0" disabled style="max-width:160px;opacity:.6;"/></td>
            <td><input type="number" name="reward_cb_bronze" class="finput" value="<?=htmlspecialchars($opt('reward_cb_bronze','0'))?>" min="0" style="max-width:110px;"/></td>
          </tr>
          <tr>
            <td><span style="color:#8892a4;font-weight:700;">🥈 Silver</span></td>
            <td><input type="number" name="reward_min_silver" class="finput" value="<?=htmlspecialchars($opt('reward_min_silver','500000'))?>" min="0" style="max-width:160px;"/></td>
            <td><input type="number" name="reward_cb_silver" class="finput" value="<?=htmlspecialchars($opt('reward_cb_silver','1'))?>" min="0" style="max-width:110px;"/></td>
          </tr>
          <tr>
            <td><span style="color:#f5a623;font-weight:700;">🥇 Gold</span></td>
            <td><input type="number" name="reward_min_gold" class="finput" value="<?=htmlspecialchars($opt('reward_min_gold','2000000'))?>" min="0" style="max-width:160px;"/></td>
            <td><input type="number" name="reward_cb_gold" class="finput" value="<?=htmlspecialchars($opt('reward_cb_gold','2'))?>" min="0" style="max-width:110px;"/></td>
          </tr>
          <tr>
            <td><span style="color:#60a5fa;font-weight:700;">💎 Platinum</span></td>
            <td><input type="number" name="reward_min_platinum" class="finput" value="<?=htmlspecialchars($opt('reward_min_platinum','5000000'))?>" min="0" style="max-width:160px;"/></td>
            <td><input type="number" name="reward_cb_platinum" class="finput" value="<?=htmlspecialchars($opt('reward_cb_platinum','3'))?>" min="0" style="max-width:110px;"/></td>
          </tr>
        </tbody>
      </table>
      </div>

      <button type="submit" class="btn-gold" style="margin-top:16px;padding:9px 22px;">💾 Simpan Konfigurasi</button>
    </form>
  </div>

  <!-- Poin User -->
  <div class="admin-card">
    <div class="admin-card-head"><h3>👥 Poin Pengguna <span style="font-size:.7rem;color:var(--t3);font-weight:500;">(top 100)</span></h3></div>
    <div class="table-wrap">
      <table class="dtable">
        <thead>
          <tr><th>User</th><th>Level</th><th style="text-align:right;">Total Belanja</th><th style="text-align:right;">Poin</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
        <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--t3);">Belum ada data pengguna.</td></tr>
        <?php else: foreach ($rows as $u):
          [$lc,$ll] = $levelBadge[$u['level']] ?? $levelBadge['bronze']; ?>
        <tr>
          <td>
            <div style="font-weight:600;font-size:.84rem;"><?=htmlspecialchars($u['name'] ?? 'User')?></div>
            <div style="font-size:.7rem;color:var(--t3);"><?=htmlspecialchars($u['email'] ?? '')?></div>
          </td>
          <td><span style="font-size:.74rem;font-weight:700;color:<?=$lc?>;"><?=$ll?></span></td>
          <td style="text-align:right;font-size:.8rem;color:var(--t2);"><?=formatRupiah((float)$u['total_spent'])?></td>
          <td style="text-align:right;font-weight:700;color:var(--gold);"><?=number_format((int)$u['points'])?></td>
          <td>
            <button class="btn-sm btn-sm-edit"
              data-id="<?=(int)$u['id']?>" data-name="<?=htmlspecialchars($u['name']??'User',ENT_QUOTES)?>" data-points="<?=(int)$u['points']?>"
              onclick="openAdjust(this)">± Poin</button>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<!-- Modal Adjust Poin -->
<div class="modal" id="modal-adjust">
  <div class="modal-box" style="max-width:420px;">
    <div class="modal-head"><h3>± Sesuaikan Poin</h3><button class="modal-close" data-modal-close="modal-adjust">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="adjust">
      <input type="hidden" name="user_id" id="a-uid">
      <div style="padding:0 2px 10px;font-size:.8rem;color:var(--t2);">User: <strong id="a-name"></strong> · Poin sekarang: <strong id="a-cur" style="color:var(--gold);"></strong></div>
      <div class="fg">
        <label class="flabel">Tambah / Kurangi Poin</label>
        <input type="number" name="delta" class="finput" placeholder="cth: 100 atau -50" required/>
        <div class="fhint">Nilai positif = tambah, negatif = kurangi.</div>
      </div>
      <div class="fg">
        <label class="flabel">Catatan</label>
        <input type="text" name="note" class="finput" placeholder="Alasan penyesuaian"/>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-adjust" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAdjust(btn){
  var d = btn.dataset;
  document.getElementById('a-uid').value = d.id;
  document.getElementById('a-name').textContent = d.name;
  document.getElementById('a-cur').textContent = parseInt(d.points).toLocaleString('id-ID');
  document.getElementById('modal-adjust').classList.add('show');
  document.body.style.overflow='hidden';
}
</script>

<?php include __DIR__.'/../includes/footer.php'; ?>
