<?php
require_once __DIR__.'/../includes/functions.php';
$pageTitle = 'FAQ — '.siteName();
include __DIR__.'/../includes/header.php';

$faqs = [
    [
        'kategori' => 'Top Up & Pembelian',
        'icon'     => '🎮',
        'items'    => [
            ['q'=>'Bagaimana cara melakukan top up?',
             'a'=>'Pilih game yang ingin di-top up dari halaman utama → masukkan User ID (dan Server ID jika diperlukan) → pilih nominal → masukkan email → klik Pesan Sekarang → lakukan pembayaran via metode yang tersedia.'],
            ['q'=>'Berapa lama proses top up setelah pembayaran?',
             'a'=>'Proses top up berjalan otomatis 1-3 detik setelah pembayaran dikonfirmasi. Pada jam sibuk bisa memakan waktu hingga 5 menit.'],
            ['q'=>'Apakah bisa top up tanpa login/daftar?',
             'a'=>'Bisa! Kamu bisa top up sebagai tamu dengan memasukkan email untuk struk. Namun dengan mendaftar, kamu bisa cek riwayat transaksi lebih mudah.'],
            ['q'=>'Bagaimana cara cek User ID game saya?',
             'a'=>'Buka game → masuk ke profil karakter → User ID biasanya tertera di bawah nama karakter. Untuk Mobile Legends: Profil → salin ID & Server. Untuk Free Fire: Profil → lihat ID di bawah nama.'],
            ['q'=>'Apakah bisa top up untuk akun orang lain?',
             'a'=>'Bisa! Cukup masukkan User ID akun yang ingin di-top up, bukan akun kamu sendiri.'],
        ]
    ],
    [
        'kategori' => 'Pembayaran',
        'icon'     => '💳',
        'items'    => [
            ['q'=>'Metode pembayaran apa saja yang tersedia?',
             'a'=>'Tersedia: QRIS (GoPay, OVO, Dana, ShopeePay, dll), Virtual Account (BCA, BRI, BNI, Mandiri), Indomaret, dan Alfamart.'],
            ['q'=>'Apakah pembayaran saya aman?',
             'a'=>'Ya, pembayaran diproses melalui Midtrans — payment gateway terpercaya yang sudah dipakai oleh ribuan merchant di Indonesia dan berlisensi dari Bank Indonesia.'],
            ['q'=>'Berapa lama batas waktu pembayaran?',
             'a'=>'Batas waktu pembayaran adalah 1 jam setelah order dibuat. Lewat dari itu, order otomatis dibatalkan dan kamu perlu membuat order baru.'],
            ['q'=>'Apakah ada biaya tambahan?',
             'a'=>'Tidak ada biaya tambahan. Harga yang tertera sudah termasuk semua biaya.'],
            ['q'=>'Bagaimana jika pembayaran saya gagal?',
             'a'=>'Jika pembayaran gagal, order akan otomatis dibatalkan. Kamu bisa membuat order baru. Jika saldo sudah terpotong tapi order gagal, hubungi CS kami segera.'],
        ]
    ],
    [
        'kategori' => 'Voucher & Promo',
        'icon'     => '🎁',
        'items'    => [
            ['q'=>'Bagaimana cara menggunakan voucher?',
             'a'=>'Di halaman pemilihan game, ada kolom "Kode Voucher" di Step 3. Masukkan kode voucher → klik Gunakan → diskon akan otomatis teraplikasi ke total pembayaran.'],
            ['q'=>'Kenapa voucher saya tidak bisa dipakai?',
             'a'=>'Beberapa kemungkinan: voucher sudah kadaluarsa, kuota habis, atau nominal pembelian tidak memenuhi syarat minimum. Cek detail syarat voucher di halaman promo.'],
            ['q'=>'Apakah bisa pakai beberapa voucher sekaligus?',
             'a'=>'Tidak, hanya bisa menggunakan 1 voucher per transaksi.'],
        ]
    ],
    [
        'kategori' => 'Transaksi & Kendala',
        'icon'     => '🔍',
        'items'    => [
            ['q'=>'Bagaimana cara cek status transaksi saya?',
             'a'=>'Buka menu "Cek Transaksi" di navbar → masukkan kode order (format: FTS-YYYYMMDD-XXXXXX) → status akan muncul.'],
            ['q'=>'Top up sudah dibayar tapi item belum masuk, bagaimana?',
             'a'=>'Tunggu 5-10 menit. Jika dalam 30 menit belum masuk, hubungi CS kami via WhatsApp dengan menyertakan kode order dan screenshot pembayaran.'],
            ['q'=>'Saya salah memasukkan User ID, apa yang harus dilakukan?',
             'a'=>'Segera hubungi CS via WhatsApp sebelum transaksi diproses. Jika sudah diproses ke ID yang salah, kami tidak bisa menjamin refund karena item sudah terkirim ke akun tersebut.'],
            ['q'=>'Apakah bisa refund?',
             'a'=>'Refund hanya bisa dilakukan jika: (1) transaksi gagal tapi pembayaran berhasil, (2) item tidak masuk dalam 24 jam setelah dibayar. Hubungi CS dengan bukti pembayaran.'],
            ['q'=>'Bagaimana cara mendapatkan invoice?',
             'a'=>'Setelah transaksi berhasil, klik tombol "Lihat Invoice" di halaman sukses. Invoice bisa dicetak atau disimpan sebagai PDF.'],
        ]
    ],
    [
        'kategori' => 'Akun & Keamanan',
        'icon'     => '🔒',
        'items'    => [
            ['q'=>'Apakah data saya aman?',
             'a'=>'Ya, kami tidak menyimpan data kartu kredit atau password game kamu. Data yang tersimpan hanya email, nama, dan riwayat transaksi yang dienkripsi.'],
            ['q'=>'Bagaimana jika lupa password?',
             'a'=>'Klik "Lupa Password" di halaman login → masukkan email → cek email untuk link reset password.'],
            ['q'=>'Apakah ftarastore meminta password game saya?',
             'a'=>'TIDAK PERNAH! ftarastore tidak pernah meminta password game, PIN, atau OTP kamu. Hati-hati penipuan yang mengatasnamakan ftarastore.'],
        ]
    ],
];
?>

<style>
.faq-wrap{max-width:800px;margin:0 auto;padding:40px 20px 60px;}
.faq-hero{text-align:center;margin-bottom:40px;}
.faq-search{display:flex;align-items:center;gap:10px;background:var(--card);border:1.5px solid var(--b1);border-radius:12px;padding:10px 16px;max-width:480px;margin:20px auto 0;box-shadow:0 2px 12px rgba(14,100,180,.08);}
.faq-search input{flex:1;border:none;outline:none;font-size:.9rem;font-family:'Inter',sans-serif;background:transparent;color:var(--t1);}
.cat-section{margin-bottom:32px;}
.cat-head{display:flex;align-items:center;gap:10px;margin-bottom:14px;padding-bottom:10px;border-bottom:2px solid var(--b1);}
.cat-head h2{font-family:var(--f-display);font-size:1rem;font-weight:800;color:var(--t1);margin:0;}
.faq-item{background:var(--card);border:1.5px solid var(--b1);border-radius:10px;margin-bottom:8px;overflow:hidden;transition:all .2s;box-shadow:0 1px 6px rgba(14,100,180,.05);}
.faq-item:hover{border-color:rgba(212,0,0,.3);}
.faq-item.open{border-color:#aa0000;box-shadow:0 2px 16px rgba(227,24,55,.12);}
.faq-q{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;cursor:pointer;gap:12px;}
.faq-q span{font-weight:600;font-size:.88rem;color:var(--t1);flex:1;}
.faq-icon{width:22px;height:22px;border-radius:50%;background:var(--b1);display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .25s;}
.faq-item.open .faq-icon{background:#aa0000;transform:rotate(45deg);}
.faq-a{max-height:0;overflow:hidden;transition:max-height .3s ease;}
.faq-item.open .faq-a{max-height:200px;}
.faq-a-inner{padding:0 18px 14px;font-size:.84rem;color:var(--t2);line-height:1.7;border-top:1px solid var(--b0);}
.contact-box{background:linear-gradient(135deg,#0c1a2e,#0f2a4a);border-radius:14px;padding:28px;text-align:center;margin-top:32px;}
</style>

<div class="faq-wrap">

  <!-- Hero -->
  <div class="faq-hero">
    <div style="font-size:2.5rem;margin-bottom:8px;">❓</div>
    <h1 style="font-family:var(--f-display);font-size:2rem;font-weight:800;color:var(--t1);margin-bottom:8px;">Pertanyaan yang Sering Ditanyakan</h1>
    <p style="color:var(--t3);font-size:.9rem;">Temukan jawaban untuk pertanyaan umum seputar ftarastore</p>
    <div class="faq-search">
      <svg width="15" height="15" fill="none" stroke="#7aabb8" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" id="faq-search" placeholder="Cari pertanyaan..." oninput="filterFaq(this.value)"/>
    </div>
  </div>

  <!-- FAQ Items -->
  <?php foreach($faqs as $cat): ?>
  <div class="cat-section" data-cat>
    <div class="cat-head">
      <span style="font-size:1.3rem;"><?=$cat['icon']?></span>
      <h2><?=htmlspecialchars($cat['kategori'])?></h2>
    </div>
    <?php foreach($cat['items'] as $i=>$item): ?>
    <div class="faq-item" data-faq onclick="toggleFaq(this)">
      <div class="faq-q">
        <span><?=htmlspecialchars($item['q'])?></span>
        <div class="faq-icon">
          <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </div>
      </div>
      <div class="faq-a">
        <div class="faq-a-inner"><?=htmlspecialchars($item['a'])?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

  <!-- Contact box -->
  <div class="contact-box">
    <div style="font-size:1.8rem;margin-bottom:8px;">💬</div>
    <h3 style="font-family:var(--f-display);font-size:1.1rem;font-weight:800;color:white;margin-bottom:6px;">Tidak menemukan jawaban?</h3>
    <p style="font-size:.84rem;color:rgba(255,255,255,.55);margin-bottom:18px;">Tim CS kami siap membantu 24 jam</p>
    <?php $wa = getSetting('whatsapp_number','62'); ?>
    <a href="https://wa.me/<?=$wa?>" target="_blank"
       style="display:inline-flex;align-items:center;gap:8px;padding:11px 24px;background:#22c55e;color:white;border-radius:10px;font-weight:700;font-size:.88rem;text-decoration:none;">
      <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
      Chat via WhatsApp
    </a>
  </div>

</div>

<script>
function toggleFaq(el) {
    var isOpen = el.classList.contains('open');
    document.querySelectorAll('.faq-item.open').forEach(function(i){ i.classList.remove('open'); });
    if (!isOpen) el.classList.add('open');
}

function filterFaq(val) {
    val = val.toLowerCase();
    document.querySelectorAll('.faq-item').forEach(function(item) {
        var q = item.querySelector('.faq-q span').textContent.toLowerCase();
        var a = item.querySelector('.faq-a-inner').textContent.toLowerCase();
        item.style.display = (q.includes(val) || a.includes(val)) ? '' : 'none';
    });
    document.querySelectorAll('.cat-section').forEach(function(sec) {
        var visible = Array.from(sec.querySelectorAll('.faq-item')).some(function(i){ return i.style.display !== 'none'; });
        sec.style.display = visible ? '' : 'none';
    });
}
</script>

<?php include __DIR__.'/../includes/footer.php'; ?>