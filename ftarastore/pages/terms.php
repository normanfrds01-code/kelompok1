<?php
require_once __DIR__.'/../includes/functions.php';
$pageTitle = 'Syarat & Ketentuan — '.siteName();
include __DIR__.'/../includes/header.php';
$updated = '1 Mei 2026';
?>
<style>
.tos-wrap{max-width:800px;margin:0 auto;padding:40px 20px 60px;}
.tos-card{background:var(--card);border-radius:14px;border:1.5px solid var(--b1);box-shadow:0 2px 16px rgba(14,100,180,.08);overflow:hidden;}
.tos-head{background:linear-gradient(135deg,#0c1a2e,#0f2a4a);padding:28px 32px;}
.tos-body{padding:28px 32px;}
.tos-section{margin-bottom:28px;padding-bottom:28px;border-bottom:1px solid var(--b0);}
.tos-section:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0;}
.tos-section h2{font-family:var(--f-display);font-size:1rem;font-weight:800;color:var(--t1);margin-bottom:10px;display:flex;align-items:center;gap:8px;}
.tos-section p{font-size:.86rem;color:var(--t2);line-height:1.8;margin-bottom:8px;}
.tos-section ul{padding-left:20px;margin:8px 0;}
.tos-section ul li{font-size:.86rem;color:var(--t2);line-height:1.8;margin-bottom:4px;}
.highlight-box{background:rgba(56,189,248,.05);border:1.5px solid rgba(56,189,248,.15);border-radius:8px;padding:12px 16px;margin:12px 0;font-size:.84rem;color:var(--cyan);}
.warning-box{background:rgba(239,68,68,.05);border:1.5px solid rgba(239,68,68,.15);border-radius:8px;padding:12px 16px;margin:12px 0;font-size:.84rem;color:#fca5a5;}
</style>

<div class="tos-wrap">

  <div style="text-align:center;margin-bottom:28px;">
    <h1 style="font-family:var(--f-display);font-size:1.8rem;font-weight:800;color:var(--t1);margin-bottom:6px;">Syarat & Ketentuan</h1>
    <p style="font-size:.84rem;color:var(--t3);">Terakhir diperbarui: <?=$updated?></p>
  </div>

  <div class="tos-card">
    <div class="tos-head">
      <p style="font-size:.84rem;color:rgba(255,255,255,.6);line-height:1.7;">
        Dengan menggunakan layanan <strong style="color:#38bdf8;">ftarastore</strong>, kamu dianggap telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang berlaku di bawah ini. Harap baca dengan seksama sebelum menggunakan layanan kami.
      </p>
    </div>

    <div class="tos-body">

      <div class="tos-section">
        <h2>1. 📋 Tentang Layanan</h2>
        <p>ftarastore adalah platform top up game online yang menyediakan layanan pembelian item, diamond, coin, dan voucher untuk berbagai game populer di Indonesia.</p>
        <p>Layanan kami beroperasi 24 jam sehari, 7 hari seminggu. Proses top up berjalan otomatis melalui sistem yang terintegrasi dengan provider resmi.</p>
      </div>

      <div class="tos-section">
        <h2>2. ✅ Syarat Penggunaan</h2>
        <p>Untuk menggunakan layanan ftarastore, pengguna harus:</p>
        <ul>
          <li>Berusia minimal 13 tahun atau mendapat izin dari orang tua/wali</li>
          <li>Memberikan informasi yang benar dan akurat saat melakukan pembelian</li>
          <li>Memiliki akun game yang valid dan aktif</li>
          <li>Tidak menggunakan layanan untuk tujuan illegal atau penipuan</li>
          <li>Bertanggung jawab atas keamanan akun ftarastore milik sendiri</li>
        </ul>
      </div>

      <div class="tos-section">
        <h2>3. 💳 Pembayaran</h2>
        <ul>
          <li>Semua harga yang tertera adalah harga final sudah termasuk pajak</li>
          <li>Pembayaran diproses melalui Midtrans sebagai payment gateway terpercaya</li>
          <li>Batas waktu pembayaran adalah 1 jam setelah order dibuat</li>
          <li>Order yang belum dibayar dalam batas waktu akan otomatis dibatalkan</li>
          <li>ftarastore tidak menyimpan informasi kartu kredit/debit pengguna</li>
        </ul>
        <div class="highlight-box">💡 Selalu periksa kembali nominal dan User ID sebelum melakukan pembayaran. Kesalahan input adalah tanggung jawab pengguna.</div>
      </div>

      <div class="tos-section">
        <h2>4. ⚡ Proses Top Up</h2>
        <ul>
          <li>Top up diproses otomatis dalam 1-3 detik setelah pembayaran dikonfirmasi</li>
          <li>Jika top up gagal karena kesalahan sistem kami, item akan dikirim ulang atau akan diproses refund</li>
          <li>ftarastore tidak bertanggung jawab atas keterlambatan yang disebabkan oleh gangguan server game</li>
          <li>Pengguna wajib memastikan kebenaran User ID dan Server ID sebelum melakukan pembelian</li>
        </ul>
        <div class="warning-box">⚠️ Top up ke User ID yang salah tidak dapat dibatalkan atau di-refund jika item sudah terkirim ke akun tersebut.</div>
      </div>

      <div class="tos-section">
        <h2>5. 🔄 Kebijakan Refund</h2>
        <p>Refund dapat dilakukan dalam kondisi berikut:</p>
        <ul>
          <li>Pembayaran berhasil namun item tidak masuk dalam 24 jam</li>
          <li>Terjadi double charge (pembayaran ganda) untuk 1 order yang sama</li>
          <li>Sistem kami mengalami error dan top up tidak dapat diproses</li>
        </ul>
        <p>Refund <strong>tidak dapat</strong> dilakukan jika:</p>
        <ul>
          <li>Pengguna salah memasukkan User ID atau Server ID</li>
          <li>Item sudah berhasil terkirim ke akun yang benar</li>
          <li>Pengguna berubah pikiran setelah pembayaran</li>
        </ul>
        <p>Proses refund membutuhkan waktu 3-7 hari kerja dan dikembalikan ke metode pembayaran asal.</p>
      </div>

      <div class="tos-section">
        <h2>6. 🔒 Keamanan & Privasi</h2>
        <ul>
          <li>ftarastore tidak pernah meminta password, PIN, atau OTP akun game pengguna</li>
          <li>Data pribadi pengguna disimpan dengan enkripsi dan tidak dijual ke pihak ketiga</li>
          <li>Kami menggunakan HTTPS untuk mengamankan semua data transaksi</li>
          <li>Pengguna bertanggung jawab menjaga kerahasiaan akun ftarastore miliknya</li>
        </ul>
        <div class="warning-box">⚠️ Hati-hati penipuan! ftarastore tidak pernah menghubungi pengguna melalui nomor tidak resmi dan meminta data sensitif.</div>
      </div>

      <div class="tos-section">
        <h2>7. 🎁 Voucher & Promo</h2>
        <ul>
          <li>Voucher hanya dapat digunakan 1x per transaksi</li>
          <li>Voucher tidak dapat digabungkan dengan promo lain kecuali dinyatakan sebaliknya</li>
          <li>ftarastore berhak mencabut voucher yang didapat dengan cara tidak sah</li>
          <li>Masa berlaku voucher sesuai yang tertera, tidak dapat diperpanjang</li>
        </ul>
      </div>

      <div class="tos-section">
        <h2>8. ⚖️ Batasan Tanggung Jawab</h2>
        <p>ftarastore tidak bertanggung jawab atas:</p>
        <ul>
          <li>Kerugian akibat pengguna memberikan informasi yang salah</li>
          <li>Gangguan layanan dari pihak provider game atau payment gateway</li>
          <li>Kehilangan item dalam game setelah top up berhasil (tanggung jawab developer game)</li>
          <li>Kerugian tidak langsung akibat penggunaan layanan kami</li>
        </ul>
      </div>

      <div class="tos-section">
        <h2>9. 📝 Perubahan Ketentuan</h2>
        <p>ftarastore berhak mengubah syarat dan ketentuan ini sewaktu-waktu. Perubahan akan diumumkan melalui website dan berlaku sejak tanggal publikasi. Dengan terus menggunakan layanan setelah perubahan, pengguna dianggap menyetujui ketentuan yang baru.</p>
      </div>

      <div class="tos-section">
        <h2>10. 📞 Kontak</h2>
        <p>Untuk pertanyaan seputar syarat dan ketentuan ini, hubungi kami melalui:</p>
        <ul>
          <?php $wa = getSetting('whatsapp_number','62'); ?>
          <li>WhatsApp: <a href="https://wa.me/<?=$wa?>" style="color:var(--cyan);">+<?=$wa?></a></li>
          <li>Jam operasional CS: 08.00 - 22.00 WIB (hari biasa), 09.00 - 20.00 WIB (akhir pekan)</li>
        </ul>
      </div>

    </div>
  </div>

  <div style="text-align:center;margin-top:20px;font-size:.8rem;color:var(--t3);">
    Dengan menggunakan ftarastore, kamu menyetujui semua ketentuan di atas.
    <br><a href="<?=asset('pages/faq.php')?>" style="color:var(--cyan);">Lihat FAQ →</a>
  </div>

</div>

<?php include __DIR__.'/../includes/footer.php'; ?>