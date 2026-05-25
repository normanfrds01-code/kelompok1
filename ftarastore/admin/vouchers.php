<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['admin']);
$pageTitle = 'Voucher & Promo — Admin';

// Buat tabel jika belum ada
try {
    db()->exec("CREATE TABLE IF NOT EXISTS vouchers (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(32) NOT NULL UNIQUE,
        type ENUM('percent','fixed') NOT NULL DEFAULT 'fixed',
        value DECIMAL(12,2) NOT NULL DEFAULT 0,
        min_purchase DECIMAL(12,2) NOT NULL DEFAULT 0,
        max_discount DECIMAL(12,2) NULL,
        quota INT NOT NULL DEFAULT 1,
        used_count INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        description VARCHAR(255) NULL,
        expires_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch(\Exception $e) {}

if($_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf(); $act=$_POST['action']??'';

    if($act==='add'){
        $code    = strtoupper(trim($_POST['code']??''));
        $type    = $_POST['type']==='percent'?'percent':'fixed';
        $value   = (float)$_POST['value'];
        $minPur  = (float)str_replace('.','', $_POST['min_purchase']??0);
        $maxDis  = !empty($_POST['max_discount'])?(float)str_replace('.','',$_POST['max_discount']):null;
        $quota   = max(1,(int)$_POST['quota']);
        $desc    = trim($_POST['description']??'');
        $expires = !empty($_POST['expires_at'])?$_POST['expires_at'].' 23:59:59':null;

        $dup = db()->prepare("SELECT id FROM vouchers WHERE code=?");
        $dup->execute([$code]);
        if($dup->fetch()){
            setFlash('error','Kode voucher sudah ada.');
        } else {
            db()->prepare("INSERT INTO vouchers (code,type,value,min_purchase,max_discount,quota,description,expires_at) VALUES (?,?,?,?,?,?,?,?)")
               ->execute([$code,$type,$value,$minPur,$maxDis,$quota,$desc,$expires]);
            setFlash('success','Voucher <strong>'.$code.'</strong> berhasil dibuat.');
        }
    }

    elseif($act==='edit'){
        $id      = (int)$_POST['id'];
        $type    = $_POST['type']==='percent'?'percent':'fixed';
        $value   = (float)$_POST['value'];
        $minPur  = (float)str_replace('.','', $_POST['min_purchase']??0);
        $maxDis  = isset($_POST['max_discount'])&&$_POST['max_discount']!==''?(float)str_replace('.','',$_POST['max_discount']):null;
        $quota   = max(1,(int)$_POST['quota']);
        $desc    = trim($_POST['description']??'');
        $expires = !empty($_POST['expires_at'])?$_POST['expires_at'].' 23:59:59':null;
        $isAct   = (int)($_POST['is_active']??1);
        db()->prepare("UPDATE vouchers SET type=?,value=?,min_purchase=?,max_discount=?,quota=?,description=?,expires_at=?,is_active=? WHERE id=?")
           ->execute([$type,$value,$minPur,$maxDis,$quota,$desc,$expires,$isAct,$id]);
        setFlash('success','Voucher berhasil diperbarui.');
    }
    elseif($act==='toggle'){
        $id=(int)$_POST['id']; $cur=(int)$_POST['current'];
        db()->prepare("UPDATE vouchers SET is_active=? WHERE id=?")->execute([$cur?0:1,$id]);
        setFlash('success','Status voucher diperbarui.');
    }

    elseif($act==='delete'){
        db()->prepare("DELETE FROM vouchers WHERE id=?")->execute([(int)$_POST['id']]);
        setFlash('success','Voucher dihapus.');
    }

    elseif($act==='generate'){
        $prefix  = strtoupper(trim($_POST['prefix']??'FTS'));
        $count   = min(10,max(1,(int)$_POST['count']));
        $type    = $_POST['type']==='percent'?'percent':'fixed';
        $value   = (float)$_POST['value'];
        $quota   = max(1,(int)($_POST['quota_each']??1));
        $expires = !empty($_POST['expires_at'])?$_POST['expires_at'].' 23:59:59':null;
        $created = 0;
        for($i=0;$i<$count;$i++){
            $code = $prefix.'-'.strtoupper(substr(uniqid(),-6));
            try{
                db()->prepare("INSERT INTO vouchers (code,type,value,quota,expires_at) VALUES (?,?,?,?,?)")
                   ->execute([$code,$type,$value,$quota,$expires]);
                $created++;
            }catch(\Exception $e){}
        }
        setFlash('success',"$created voucher berhasil di-generate.");
    }

    header('Location: '.asset('admin/vouchers.php')); exit;
}

// Stats
$totalV   = (int)db()->query("SELECT COUNT(*) FROM vouchers")->fetchColumn();
$activeV  = (int)db()->query("SELECT COUNT(*) FROM vouchers WHERE is_active=1 AND (expires_at IS NULL OR expires_at>NOW()) AND used_count<quota")->fetchColumn();
$totalUsed= (int)db()->query("SELECT COALESCE(SUM(used_count),0) FROM vouchers")->fetchColumn();
$expiredV = (int)db()->query("SELECT COUNT(*) FROM vouchers WHERE expires_at IS NOT NULL AND expires_at<NOW()")->fetchColumn();

$vouchers = db()->query("SELECT * FROM vouchers ORDER BY created_at DESC")->fetchAll();

include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div class="admin-title" style="margin:0;display:flex;align-items:center;gap:10px;">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 12v10H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
      Voucher & Promo
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-ghost" data-modal-open="modal-generate" style="padding:9px 16px;font-size:.84rem;display:flex;align-items:center;gap:6px;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        Generate Massal
      </button>
      <button class="btn-gold" data-modal-open="modal-add" style="padding:9px 20px;font-size:.84rem;display:flex;align-items:center;gap:6px;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Buat Voucher
      </button>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
      <div class="stat-label">Total Voucher</div>
      <div class="stat-val"><?=$totalV?></div>
      <div class="stat-change">Semua voucher</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Aktif & Valid</div>
      <div class="stat-val" style="color:var(--green);"><?=$activeV?></div>
      <div class="stat-change">Bisa dipakai sekarang</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total Dipakai</div>
      <div class="stat-val"><?=$totalUsed?></div>
      <div class="stat-change">Kali penggunaan</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Kadaluarsa</div>
      <div class="stat-val" style="color:var(--red);"><?=$expiredV?></div>
      <div class="stat-change">Sudah expired</div>
    </div>
  </div>

  <!-- Info -->
  <div style="background:rgba(56,189,248,.05);border:1px solid rgba(56,189,248,.15);border-radius:var(--r);padding:12px 16px;margin-bottom:18px;font-size:.8rem;color:var(--cyan);display:flex;gap:10px;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    Voucher bisa dipakai pelanggan saat checkout. <strong>Persen (%)</strong> = diskon persentase. <strong>Nominal (Rp)</strong> = potongan tetap.
  </div>

  <!-- Tabel -->
  <div class="admin-card">
    <div class="admin-card-head">
      <h3>Daftar Voucher <span style="font-size:.75rem;color:var(--t3);font-weight:400;">(<?=count($vouchers)?>)</span></h3>
    </div>
    <div class="table-wrap">
      <table class="dtable">
        <thead><tr><th>Kode</th><th>Tipe</th><th>Nilai</th><th>Min. Beli</th><th>Kuota</th><th>Expired</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if(empty($vouchers)): ?>
        <tr><td colspan="8" style="text-align:center;padding:44px;color:var(--t3);">Belum ada voucher. Buat yang pertama!</td></tr>
        <?php else: foreach($vouchers as $v):
          $isExpired = $v['expires_at'] && strtotime($v['expires_at']) < time();
          $isFull    = $v['used_count'] >= $v['quota'];
          $statusOk  = $v['is_active'] && !$isExpired && !$isFull;
        ?>
        <tr>
          <td>
            <span style="font-family:var(--f-display);font-size:.92rem;font-weight:800;color:var(--cyanf);letter-spacing:1px;"><?=htmlspecialchars($v['code'])?></span>
            <?php if($v['description']): ?>
            <div style="font-size:.7rem;color:var(--t3);margin-top:2px;"><?=htmlspecialchars($v['description'])?></div>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge <?=$v['type']==='percent'?'badge-process':'badge-success'?>" style="font-size:.69rem;">
              <?=$v['type']==='percent'?'Persen (%)':'Nominal (Rp)'?>
            </span>
          </td>
          <td style="font-weight:700;color:var(--green);font-family:var(--f-display);">
            <?=$v['type']==='percent'?$v['value'].'%':'Rp '.number_format($v['value'],0,',','.')?>
            <?php if($v['max_discount'] && $v['type']==='percent'): ?>
            <div style="font-size:.68rem;color:var(--t3);font-weight:400;">Maks <?=formatRupiah($v['max_discount'])?></div>
            <?php endif; ?>
          </td>
          <td style="font-size:.83rem;"><?=$v['min_purchase']>0?formatRupiah($v['min_purchase']):'—'?></td>
          <td>
            <div style="display:flex;align-items:center;gap:6px;">
              <div style="flex:1;height:5px;background:var(--b1);border-radius:3px;min-width:50px;">
                <div style="height:100%;width:<?=min(100,round($v['used_count']/$v['quota']*100))?>%;background:<?=$isFull?'var(--red)':'var(--cyan)'?>;border-radius:3px;"></div>
              </div>
              <span style="font-size:.75rem;color:var(--t2);"><?=$v['used_count']?>/<?=$v['quota']?></span>
            </div>
          </td>
          <td style="font-size:.78rem;color:<?=$isExpired?'var(--red)':'var(--t3)'?>;">
            <?=$v['expires_at']?date('d M Y',strtotime($v['expires_at'])):'—'?>
            <?php if($v['expires_at'] && !$isExpired):
              $daysLeft = round((strtotime($v['expires_at'])-time())/86400);
            ?><div style="font-size:.68rem;color:<?=$daysLeft<=3?'#f59e0b':'var(--t3)'?>"><?=$daysLeft?> hari lagi</div>
            <?php elseif($isExpired): ?>
            <div style="font-size:.68rem;color:var(--red);font-weight:600;">Kadaluarsa</div>
            <?php endif; ?>
          </td>
          <td>
            <?php if($statusOk): ?><span class="badge badge-success" style="font-size:.69rem;">Aktif</span>
            <?php elseif($isExpired): ?><span class="badge badge-failed" style="font-size:.69rem;">Expired</span>
            <?php elseif($isFull): ?><span class="badge badge-failed" style="font-size:.69rem;">Habis</span>
            <?php else: ?><span class="badge badge-pending" style="font-size:.69rem;">Nonaktif</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:5px;">
              <!-- Edit — pakai data attributes, aman dari quote conflict -->
              <button type="button" class="btn-sm btn-sm-edit" style="font-size:.72rem;"
                data-id="<?=$v['id']?>"
                data-type="<?=htmlspecialchars($v['type'])?>"
                data-value="<?=(float)$v['value']?>"
                data-minpur="<?=(float)$v['min_purchase']?>"
                data-maxdis="<?=(float)($v['max_discount']??0)?>"
                data-quota="<?=(int)$v['quota']?>"
                data-desc="<?=htmlspecialchars($v['description']??'')?>"
                data-expires="<?=$v['expires_at']?date('Y-m-d',strtotime($v['expires_at'])):''?>"
                data-active="<?=(int)$v['is_active']?>"
                onclick="openEditVoucherEl(this)">✏️ Edit</button>
              <!-- Toggle aktif/nonaktif -->
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?=$v['id']?>">
                <input type="hidden" name="current" value="<?=$v['is_active']?>">
                <button type="submit" class="btn-sm <?=$v['is_active']?'btn-sm-danger':'btn-sm-edit'?>" style="font-size:.72rem;"><?=$v['is_active']?'Nonaktif':'Aktifkan'?></button>
              </form>
              <!-- Hapus -->
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?=$v['id']?>">
                <button type="submit" class="btn-sm btn-sm-danger" onclick="return confirm('Hapus voucher '+ (this.closest('tr').querySelector('.code')?.textContent || 'ini') +'? Tidak bisa dibatalkan!')" style="font-size:.72rem;">Hapus</button>
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

<!-- Modal Tambah -->
<div class="modal" id="modal-add">
  <div class="modal-box">
    <div class="modal-head"><h3>Buat Voucher Baru</h3><button class="modal-close" data-modal-close="modal-add">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="add">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg" style="margin:0;">
          <label class="flabel">Kode Voucher <span class="req">*</span></label>
          <input type="text" name="code" class="finput" placeholder="Contoh: TOPUP50" style="text-transform:uppercase;" required/>
        </div>
        <div class="fg" style="margin:0;">
          <label class="flabel">Tipe Diskon <span class="req">*</span></label>
          <select name="type" class="finput" id="vtype" onchange="toggleMaxDiscount()">
            <option value="fixed">Nominal (Rp)</option>
            <option value="percent">Persen (%)</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
        <div class="fg" style="margin:0;">
          <label class="flabel" id="val-lbl">Nilai Diskon (Rp) <span class="req">*</span></label>
          <input type="number" name="value" class="finput" placeholder="5000" required min="0"/>
        </div>
        <div class="fg" style="margin:0;" id="maxd-wrap" style="display:none;">
          <label class="flabel">Maks. Diskon (Rp)</label>
          <input type="text" name="max_discount" class="finput" placeholder="Opsional"/>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
        <div class="fg" style="margin:0;">
          <label class="flabel">Min. Pembelian (Rp)</label>
          <input type="text" name="min_purchase" class="finput" placeholder="0 = tidak ada batas"/>
        </div>
        <div class="fg" style="margin:0;">
          <label class="flabel">Kuota <span class="req">*</span></label>
          <input type="number" name="quota" class="finput" value="1" min="1" required/>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
        <div class="fg" style="margin:0;">
          <label class="flabel">Berlaku Sampai</label>
          <input type="date" name="expires_at" class="finput" min="<?=date('Y-m-d')?>"/>
          <div class="fhint">Kosongkan = tidak ada batas</div>
        </div>
        <div class="fg" style="margin:0;">
          <label class="flabel">Keterangan</label>
          <input type="text" name="description" class="finput" placeholder="Contoh: Promo Lebaran"/>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Buat Voucher</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-add" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Generate -->
<div class="modal" id="modal-generate">
  <div class="modal-box">
    <div class="modal-head"><h3>Generate Voucher Massal</h3><button class="modal-close" data-modal-close="modal-generate">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="generate">
      <div style="background:var(--cyan-lo);border:1px solid var(--cyan-md);border-radius:8px;padding:11px;margin-bottom:14px;font-size:.78rem;color:var(--cyan);">Generate banyak kode unik sekaligus — cocok untuk giveaway atau event.</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg" style="margin:0;"><label class="flabel">Prefix</label><input type="text" name="prefix" class="finput" value="FTS" maxlength="6" style="text-transform:uppercase;"/><div class="fhint">Maks 6 huruf</div></div>
        <div class="fg" style="margin:0;"><label class="flabel">Jumlah <span class="req">*</span></label><input type="number" name="count" class="finput" value="5" min="1" max="10" required/><div class="fhint">Maks 10</div></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
        <div class="fg" style="margin:0;"><label class="flabel">Tipe</label><select name="type" class="finput"><option value="fixed">Nominal (Rp)</option><option value="percent">Persen (%)</option></select></div>
        <div class="fg" style="margin:0;"><label class="flabel">Nilai <span class="req">*</span></label><input type="number" name="value" class="finput" placeholder="5000" required min="0"/></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
        <div class="fg" style="margin:0;"><label class="flabel">Kuota per Voucher</label><input type="number" name="quota_each" class="finput" value="1" min="1"/></div>
        <div class="fg" style="margin:0;"><label class="flabel">Berlaku Sampai</label><input type="date" name="expires_at" class="finput" min="<?=date('Y-m-d')?>"/></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Generate</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-generate" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleMaxDiscount(){
  var t = document.getElementById('vtype').value;
  document.getElementById('val-lbl').innerHTML = t==='percent'?'Nilai Diskon (%) <span class="req">*</span>':'Nilai Diskon (Rp) <span class="req">*</span>';
  document.getElementById('maxd-wrap').style.display = t==='percent'?'block':'none';
}
toggleMaxDiscount();
</script>
<!-- Modal Edit Voucher -->
<div class="modal" id="modal-edit-voucher">
  <div class="modal-box">
    <div class="modal-head"><h3>✏️ Edit Voucher</h3><button class="modal-close" data-modal-close="modal-edit-voucher">✕</button></div>
    <form method="POST">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="ev-id">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="fg" style="margin:0;"><label class="flabel">Tipe Diskon</label>
          <select name="type" id="ev-type" class="finput" onchange="document.getElementById('ev-maxd-wrap').style.display=this.value==='percent'?'block':'none'">
            <option value="fixed">Nominal (Rp)</option>
            <option value="percent">Persen (%)</option>
          </select></div>
        <div class="fg" style="margin:0;"><label class="flabel">Nilai <span class="req">*</span></label><input type="number" name="value" id="ev-value" class="finput" required min="0"/></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
        <div class="fg" style="margin:0;"><label class="flabel">Min. Pembelian (Rp)</label><input type="number" name="min_purchase" id="ev-minpur" class="finput" value="0"/></div>
        <div class="fg" id="ev-maxd-wrap" style="margin:0;display:none;"><label class="flabel">Maks. Diskon (Rp)</label><input type="number" name="max_discount" id="ev-maxd" class="finput"/></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
        <div class="fg" style="margin:0;"><label class="flabel">Kuota Total <span class="req">*</span></label><input type="number" name="quota" id="ev-quota" class="finput" required min="1"/></div>
        <div class="fg" style="margin:0;"><label class="flabel">Berlaku Sampai</label><input type="date" name="expires_at" id="ev-exp" class="finput" min="<?=date('Y-m-d')?>"/></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
        <div class="fg" style="margin:0;"><label class="flabel">Keterangan</label><input type="text" name="description" id="ev-desc" class="finput"/></div>
        <div class="fg" style="margin:0;display:flex;align-items:flex-end;padding-bottom:2px;"><label style="display:flex;align-items:center;gap:8px;font-size:.88rem;color:var(--t2);cursor:pointer;"><input type="checkbox" name="is_active" id="ev-active" value="1"> Voucher Aktif</label></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn-submit" style="flex:1;">Simpan</button>
        <button type="button" class="btn-ghost" data-modal-close="modal-edit-voucher" style="flex:1;border-radius:10px;padding:13px;">Batal</button>
      </div>
    </form>
  </div>
</div>
<script>
function openEditVoucherEl(btn){
  document.getElementById('ev-id').value     = btn.dataset.id;
  document.getElementById('ev-type').value   = btn.dataset.type;
  document.getElementById('ev-value').value  = btn.dataset.value;
  document.getElementById('ev-minpur').value = btn.dataset.minpur;
  document.getElementById('ev-maxd').value   = btn.dataset.maxdis||'';
  document.getElementById('ev-quota').value  = btn.dataset.quota;
  document.getElementById('ev-desc').value   = btn.dataset.desc;
  document.getElementById('ev-exp').value    = btn.dataset.expires||'';
  document.getElementById('ev-active').checked = btn.dataset.active=='1';
  document.getElementById('ev-maxd-wrap').style.display = btn.dataset.type==='percent'?'block':'none';
  document.getElementById('modal-edit-voucher').classList.add('show');
}
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>