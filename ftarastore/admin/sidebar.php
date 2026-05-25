<?php
$cp   = basename($_SERVER['PHP_SELF']);
$role = currentRole();
?>
<aside class="admin-sb">

  <!-- User info -->
  <div style="padding:16px 18px 14px;border-bottom:1px solid var(--b1);">
    <div style="display:flex;align-items:center;gap:10px;">
      <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:var(--f-display);font-size:.95rem;font-weight:800;
        <?= $role==='super_admin'?'background:linear-gradient(135deg,#e31837,#0ea5e9);color:#03111f;':'background:linear-gradient(135deg,#2dd4bf,#0d9488);color:#03111f;' ?>">
        <?= strtoupper(substr(currentUser()['name']??'A',0,1)) ?>
      </div>
      <div style="min-width:0;">
        <div style="font-size:.82rem;font-weight:700;color:var(--t1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
          <?= htmlspecialchars(currentUser()['name']??'Admin') ?>
        </div>
        <div style="font-size:.67rem;margin-top:2px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;
          <?= $role==='super_admin'?'color:#e31837;':'color:#2dd4bf;' ?>">
          <?= $role==='super_admin'?'Super Admin':'Admin' ?>
        </div>
      </div>
    </div>
  </div>

  <!-- MENU UTAMA -->
  <div class="admin-sb-label">Menu Utama</div>

  <a href="<?= asset('admin/index.php') ?>" class="<?= $cp==='index.php'?'on':'' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    Dashboard
  </a>

  <a href="<?= asset('admin/transactions.php') ?>" class="<?= $cp==='transactions.php'?'on':'' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
    Transaksi
    <?php
    $pending=0;
    try{$pending=db()->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();}catch(\Exception $e){}
    if($pending>0):?>
    <span style="margin-left:auto;background:#ef4444;color:#fff;font-size:.62rem;font-weight:700;padding:1px 7px;border-radius:10px;"><?=$pending?></span>
    <?php endif;?>
  </a>

  <!-- KELOLA KONTEN -->
  <div class="admin-sb-label">Kelola Konten</div>

  <a href="<?= asset('admin/games.php') ?>" class="<?= in_array($cp,['games.php','products.php'])?'on':'' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
    Kelola Produk
  </a>

  <a href="<?= asset('admin/categories.php') ?>" class="<?= $cp==='categories.php'?'on':'' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    Kelola Kategori
  </a>

  <a href="<?= asset('admin/vouchers.php') ?>" class="<?= $cp==='vouchers.php'?'on':'' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 12v10H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
    Voucher & Promo
    <?php
    $activeVoucher=0;
    try{$activeVoucher=db()->query("SELECT COUNT(*) FROM vouchers WHERE is_active=1 AND (expires_at IS NULL OR expires_at>NOW())")->fetchColumn();}catch(\Exception $e){}
    if($activeVoucher>0):?>
    <span style="margin-left:auto;background:rgba(227,24,55,.15);color:#e31837;font-size:.62rem;font-weight:700;padding:1px 7px;border-radius:10px;border:1px solid rgba(227,24,55,.25);"><?=$activeVoucher?> aktif</span>
    <?php endif;?>
  </a>

  <a href="<?= asset('admin/banners.php') ?>" class="<?= $cp==='banners.php'?'on':'' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="m2 10 20 0"/></svg>
    Banner & Promo
  </a>
<a href="<?= asset('admin/explore.php') ?>" class="<?= $cp==='explore.php'?'on':'' ?>">
  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
  Kelola Explore
</a>


  <!-- KONFIGURASI -->
  <div class="admin-sb-label">Konfigurasi</div>

  <a href="<?= asset('admin/settings.php') ?>" class="<?= $cp==='settings.php'?'on':'' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
    Pengaturan Web
  </a>

  <?php if($role==='super_admin'): ?>
  <!-- SUPER ADMIN -->
  <div class="admin-sb-label">Super Admin</div>

  <a href="<?= asset('admin/reports.php') ?>" class="<?= $cp==='reports.php'?'on':'' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    Laporan Revenue
  </a>

  <a href="<?= asset('admin/admins.php') ?>" class="<?= $cp==='admins.php'?'on':'' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Kelola Admin
  </a>

  <a href="<?= asset('admin/users.php') ?>" class="<?= $cp==='users.php'?'on':'' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    Kelola Akun Pengunjung
  </a>

  <a href="<?= asset('admin/security.php') ?>" class="<?= $cp==='security.php'?'on':'' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    Pusat Keamanan
  </a>
  <?php endif; ?>

  <!-- Lihat Website -->
  <div style="padding:8px 17px;margin-top:4px;border-top:1px solid var(--b1);">
    <a href="<?= asset('index.php') ?>" target="_blank" style="display:flex;align-items:center;gap:8px;font-size:.8rem;color:var(--t3);transition:color .15s;" onmouseover="this.style.color='var(--redf)'" onmouseout="this.style.color='var(--t3)'">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Lihat Website
    </a>
  </div>

</aside>