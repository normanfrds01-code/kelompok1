<?php
require_once __DIR__.'/../includes/functions.php';
$pageTitle = 'Kalkulator — '.siteName();
$type = $_GET['type'] ?? '';
include __DIR__.'/../includes/header.php';
?>

<div class="container" style="padding-top:36px;padding-bottom:60px;max-width:860px;">

<?php if(!$type): ?>
<!-- ── PILIH KALKULATOR ── -->
<div style="text-align:center;margin-bottom:36px;">
  <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:var(--rl);background:linear-gradient(135deg,var(--violet),var(--violet2));margin-bottom:16px;box-shadow:0 4px 20px rgba(124,58,237,.35);">
    <svg width="26" height="26" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="12" x2="10" y2="12"/><line x1="8" y1="16" x2="10" y2="16"/><line x1="14" y1="12" x2="16" y2="12"/><line x1="14" y1="16" x2="16" y2="16"/></svg>
  </div>
  <h1 style="font-family:var(--f-display);font-size:1.9rem;font-weight:800;margin-bottom:8px;">Kalkulator</h1>
  <p style="color:var(--t3);font-size:.92rem;">Pilih jenis kalkulator yang kamu butuhkan</p>
</div>

<div style="display:flex;flex-direction:column;gap:10px;">
  <?php
  $calcs = [
    ['type'=>'winrate',    'icon'=>'trophy',   'title'=>'Win Rate',    'desc'=>'Hitung total pertandingan yang harus dimainkan untuk mencapai target win rate yang diinginkan.'],
    ['type'=>'magicwheel', 'icon'=>'star',     'title'=>'Magic Wheel', 'desc'=>'Hitung total maksimal diamond yang dibutuhkan untuk mendapatkan skin Legends dari Magic Wheel.'],
    ['type'=>'zodiac',     'icon'=>'moon',     'title'=>'Zodiac',      'desc'=>'Hitung total estimasi diamond yang dibutuhkan untuk mendapatkan skin Zodiac berdasarkan poin kamu.'],
    ['type'=>'harga',      'icon'=>'calc',     'title'=>'Harga Jual',  'desc'=>'Hitung harga jual produk dari harga modal dan fee yang kamu tentukan.'],
  ];
  $svgs = [
    'trophy' => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9H4a2 2 0 0 0-2 2v1a4 4 0 0 0 4 4h0"/><path d="M18 9h2a2 2 0 0 1 2 2v1a4 4 0 0 1-4 4h0"/><path d="M8 21h8"/><path d="M12 17v4"/><path d="M7 4h10v9a5 5 0 0 1-10 0V4z"/></svg>',
    'star'   => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    'moon'   => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>',
    'calc'   => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="12" x2="10" y2="12"/><line x1="8" y1="16" x2="10" y2="16"/><line x1="14" y1="12" x2="16" y2="12"/><line x1="14" y1="16" x2="16" y2="16"/></svg>',
  ];
  foreach($calcs as $c): ?>
  <a href="?type=<?=$c['type']?>" style="display:flex;align-items:center;gap:18px;background:var(--card);border:1px solid var(--b1);border-radius:var(--rl);padding:20px 22px;text-decoration:none;transition:all .2s var(--ease);"
     onmouseover="this.style.borderColor='rgba(124,58,237,.4)';this.style.background='rgba(124,58,237,.06)'"
     onmouseout="this.style.borderColor='var(--b1)';this.style.background='var(--card)'">
    <div style="width:48px;height:48px;border-radius:12px;background:rgba(124,58,237,.12);border:1px solid rgba(124,58,237,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#c4b5fd;">
      <?=$svgs[$c['icon']]?>
    </div>
    <div style="flex:1;">
      <div style="font-family:var(--f-display);font-size:1rem;font-weight:700;color:var(--t1);margin-bottom:3px;"><?=$c['title']?></div>
      <div style="font-size:.82rem;color:var(--t3);line-height:1.5;"><?=$c['desc']?></div>
    </div>
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--t3);flex-shrink:0;"><path d="m9 18 6-6-6-6"/></svg>
  </a>
  <?php endforeach; ?>
</div>

<?php else: ?>

<!-- ── HEADER KEMBALI ── -->
<div style="margin-bottom:28px;">
  <a href="<?=asset('pages/kalkulator.php')?>" style="display:inline-flex;align-items:center;gap:6px;font-size:.84rem;color:var(--t3);text-decoration:none;margin-bottom:20px;transition:color .15s;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--t3)'">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
    Kembali ke Kalkulator
  </a>
</div>

<!-- ════════════════ WIN RATE ════════════════ -->
<?php if($type==='winrate'): ?>
<div style="max-width:580px;margin:0 auto;text-align:center;">
  <div style="display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:var(--rl);background:linear-gradient(135deg,#f59e0b,#d97706);margin-bottom:16px;">
    <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9H4a2 2 0 0 0-2 2v1a4 4 0 0 0 4 4h0"/><path d="M18 9h2a2 2 0 0 1 2 2v1a4 4 0 0 1-4 4h0"/><path d="M8 21h8"/><path d="M12 17v4"/><path d="M7 4h10v9a5 5 0 0 1-10 0V4z"/></svg>
  </div>
  <h1 style="font-family:var(--f-display);font-size:1.7rem;font-weight:800;margin-bottom:8px;">Kalkulator Win Rate</h1>
  <p style="color:var(--t3);font-size:.88rem;margin-bottom:32px;line-height:1.6;">Hitung total pertandingan yang harus dimainkan untuk mencapai target win rate yang diinginkan.</p>
</div>
<div class="kalk-card" style="max-width:580px;margin:0 auto;">
  <div class="fg"><label class="flabel">Total Pertandingan Kamu Saat Ini</label><input type="number" id="wr-total" class="finput" placeholder="Contoh: 223" min="0"/></div>
  <div class="fg"><label class="flabel">Total Kemenangan Kamu Saat Ini</label><input type="number" id="wr-wins" class="finput" placeholder="Contoh: 120" min="0"/></div>
  <div class="fg"><label class="flabel">Target Win Rate (%)</label><input type="number" id="wr-target" class="finput" placeholder="Contoh: 60" min="0" max="100" step="0.1"/></div>
  <div style="display:flex;gap:10px;">
    <button onclick="calcWinRate()" class="btn-submit" style="flex:1;">Hitung</button>
    <a href="<?=asset('index.php')?>" class="btn-submit" style="flex:1;display:flex;align-items:center;justify-content:center;gap:7px;text-decoration:none;background:rgba(124,58,237,.15);color:#c4b5fd;border:1px solid rgba(124,58,237,.3);">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
      Top Up Sekarang
    </a>
  </div>
  <div id="wr-result" style="display:none;margin-top:20px;"></div>
</div>
<script>
function calcWinRate(){
  const total  = parseFloat(document.getElementById('wr-total').value)||0;
  const wins   = parseFloat(document.getElementById('wr-wins').value)||0;
  const target = parseFloat(document.getElementById('wr-target').value)||0;
  const res    = document.getElementById('wr-result');
  if(!total||!wins||!target){res.style.display='block';res.innerHTML='<div class="auth-err">⚠️ Semua field wajib diisi.</div>';return;}
  if(wins>total){res.style.display='block';res.innerHTML='<div class="auth-err">⚠️ Kemenangan tidak boleh lebih dari total pertandingan.</div>';return;}
  const curWR  = (wins/total*100).toFixed(1);
  if(target<=curWR){res.style.display='block';res.innerHTML='<div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);border-radius:var(--r);padding:16px;color:#34d399;font-size:.9rem;">✅ Win rate kamu sudah <strong>'+curWR+'%</strong>, sudah melebihi target '+target+'%!</div>';return;}
  // Hitung: (wins + x) / (total + x) = target/100
  const t = target/100;
  const x = Math.ceil((t*total - wins)/(1 - t));
  res.style.display='block';
  res.innerHTML=`
    <div style="background:var(--card2);border:1px solid rgba(245,166,35,.2);border-radius:var(--r);padding:20px;">
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--b0);font-size:.88rem;"><span style="color:var(--t3)">Win Rate Saat Ini</span><span style="font-weight:600">${curWR}%</span></div>
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--b0);font-size:.88rem;"><span style="color:var(--t3)">Target Win Rate</span><span style="font-weight:600">${target}%</span></div>
      <div style="display:flex;justify-content:space-between;padding:14px 0 0;"><span style="font-weight:600;font-size:.95rem;">Kamu perlu menang</span><span style="font-family:var(--f-display);font-size:1.5rem;font-weight:800;color:var(--gold);">${x} pertandingan lagi</span></div>
    </div>`;
}
</script>

<!-- ════════════════ MAGIC WHEEL ════════════════ -->
<?php elseif($type==='magicwheel'): ?>
<div style="max-width:580px;margin:0 auto;text-align:center;">
  <div style="display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:var(--rl);background:linear-gradient(135deg,#8b5cf6,#6d28d9);margin-bottom:16px;">
    <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
  </div>
  <h1 style="font-family:var(--f-display);font-size:1.7rem;font-weight:800;margin-bottom:8px;">Kalkulator Magic Wheel</h1>
  <p style="color:var(--t3);font-size:.88rem;margin-bottom:32px;line-height:1.6;">Hitung total maksimal diamond yang dibutuhkan untuk mendapatkan skin Legends dari Magic Wheel.</p>
</div>
<div class="kalk-card" style="max-width:580px;margin:0 auto;">
  <div class="fg">
    <label class="flabel">Putaran Magic Wheel Kamu Saat Ini</label>
    <input type="number" id="mw-spin" class="finput" placeholder="Contoh: 0 (belum pernah spin)" min="0" max="50"/>
    <div class="fhint">Skin Legends dijamin keluar maksimal di putaran ke-50.</div>
  </div>
  <div style="background:rgba(124,58,237,.06);border:1px solid rgba(124,58,237,.15);border-radius:var(--r);padding:14px 16px;margin-bottom:18px;font-size:.82rem;color:#c4b5fd;line-height:1.7;">
    💡 Setiap spin Magic Wheel membutuhkan <strong>100 diamond</strong>. Skin Legends dijamin pada putaran ke-50 (pity system).
  </div>
  <div style="display:flex;gap:10px;">
    <button onclick="calcMagicWheel()" class="btn-submit" style="flex:1;">Hitung</button>
    <a href="<?=asset('index.php')?>" class="btn-submit" style="flex:1;display:flex;align-items:center;justify-content:center;gap:7px;text-decoration:none;background:rgba(124,58,237,.15);color:#c4b5fd;border:1px solid rgba(124,58,237,.3);">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
      Top Up Diamond
    </a>
  </div>
  <div id="mw-result" style="display:none;margin-top:20px;"></div>
</div>
<script>
function calcMagicWheel(){
  const spin = parseInt(document.getElementById('mw-spin').value)||0;
  const res  = document.getElementById('mw-result');
  if(spin<0||spin>50){res.style.display='block';res.innerHTML='<div class="auth-err">⚠️ Putaran harus antara 0–50.</div>';return;}
  const remaining = 50 - spin;
  const maxDiamond = remaining * 100;
  const minDiamond = 100; // bisa saja langsung dapat
  res.style.display='block';
  res.innerHTML=`
    <div style="background:var(--card2);border:1px solid rgba(124,58,237,.2);border-radius:var(--r);padding:20px;">
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--b0);font-size:.88rem;"><span style="color:var(--t3)">Putaran Saat Ini</span><span style="font-weight:600">${spin} / 50</span></div>
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--b0);font-size:.88rem;"><span style="color:var(--t3)">Sisa Putaran Maksimal</span><span style="font-weight:600">${remaining} putaran</span></div>
      <div style="display:flex;justify-content:space-between;padding:14px 0 0;">
        <span style="font-weight:600;font-size:.95rem;">Maksimal Diamond</span>
        <span style="font-family:var(--f-display);font-size:1.5rem;font-weight:800;color:#c4b5fd;">${maxDiamond.toLocaleString('id-ID')} 💎</span>
      </div>
    </div>
    ${remaining===0?'<div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);border-radius:var(--r);padding:12px 16px;margin-top:10px;color:#34d399;font-size:.85rem;">🎉 Kamu sudah di putaran 50! Spin sekarang dijamin dapat skin Legends!</div>':''}`;
}
</script>

<!-- ════════════════ ZODIAC ════════════════ -->
<?php elseif($type==='zodiac'): ?>
<div style="max-width:580px;margin:0 auto;text-align:center;">
  <div style="display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:var(--rl);background:linear-gradient(135deg,#0ea5e9,#0284c7);margin-bottom:16px;">
    <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
  </div>
  <h1 style="font-family:var(--f-display);font-size:1.7rem;font-weight:800;margin-bottom:8px;">Kalkulator Zodiac</h1>
  <p style="color:var(--t3);font-size:.88rem;margin-bottom:32px;line-height:1.6;">Hitung total estimasi diamond yang dibutuhkan untuk mendapatkan skin Zodiac berdasarkan poin bintang kamu.</p>
</div>
<div class="kalk-card" style="max-width:580px;margin:0 auto;">
  <div class="fg">
    <label class="flabel">Geser sesuai Titik Zodiac Kamu</label>
    <div style="display:flex;align-items:center;gap:14px;margin-top:8px;">
      <span style="font-size:.8rem;color:var(--t3);flex-shrink:0;">0</span>
      <input type="range" id="zodiac-slider" min="0" max="50" value="0" step="1"
             oninput="updateZodiac(this.value)"
             style="flex:1;-webkit-appearance:none;appearance:none;height:4px;border-radius:2px;background:linear-gradient(to right,var(--gold) 0%, var(--b2) 0%);outline:none;cursor:pointer;"/>
      <span style="font-size:.8rem;color:var(--t3);flex-shrink:0;">50</span>
    </div>
  </div>
  <div style="background:var(--card2);border:1px solid rgba(245,166,35,.2);border-radius:var(--r);padding:20px;margin-bottom:18px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
      <div>
        <div style="font-size:.78rem;color:var(--t3);margin-bottom:3px;">Poin Bintang Kamu</div>
        <div style="font-family:var(--f-display);font-size:1.6rem;font-weight:800;color:var(--gold);" id="zodiac-poin">0</div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:.78rem;color:var(--t3);margin-bottom:3px;">Membutuhkan Maksimal</div>
        <div style="font-family:var(--f-display);font-size:1.6rem;font-weight:800;color:#93c5fd;" id="zodiac-diamond">2500 💎</div>
      </div>
    </div>
    <div style="margin-top:14px;height:6px;background:var(--b1);border-radius:3px;overflow:hidden;">
      <div id="zodiac-bar" style="height:100%;background:linear-gradient(90deg,var(--gold),#f59e0b);border-radius:3px;width:100%;transition:width .3s;"></div>
    </div>
  </div>
  <a href="<?=asset('index.php')?>" class="btn-submit" style="display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M6 12h4m-2-2v4M15 12h.01M18 12h.01"/></svg>
    Top Up Diamond Sekarang!
  </a>
</div>
<style>
#zodiac-slider::-webkit-slider-thumb{-webkit-appearance:none;width:18px;height:18px;border-radius:50%;background:var(--gold);cursor:pointer;border:2px solid #0a0817;box-shadow:0 0 6px rgba(245,166,35,.5);}
#zodiac-slider::-moz-range-thumb{width:18px;height:18px;border-radius:50%;background:var(--gold);cursor:pointer;border:2px solid #0a0817;}
</style>
<script>
function updateZodiac(val){
  val = parseInt(val);
  // Maks 2500 diamond (50 poin = 0 diamond sudah punya semua, 0 poin = 2500)
  const maxDiamond = 2500;
  const perPoin = 50; // 50 diamond per poin
  const needed = Math.max(0, (50 - val) * perPoin);
  const pct = val / 50 * 100;
  document.getElementById('zodiac-poin').textContent    = val;
  document.getElementById('zodiac-diamond').textContent = needed.toLocaleString('id-ID') + ' 💎';
  document.getElementById('zodiac-bar').style.width = pct + '%';
  // Update slider gradient
  const slider = document.getElementById('zodiac-slider');
  slider.style.background = `linear-gradient(to right, var(--gold) ${pct}%, var(--b2) ${pct}%)`;
}
updateZodiac(0);
</script>

<!-- ════════════════ HARGA JUAL ════════════════ -->
<?php elseif($type==='harga'): ?>
<div style="max-width:580px;margin:0 auto;text-align:center;">
  <div style="display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:var(--rl);background:linear-gradient(135deg,var(--green),#059669);margin-bottom:16px;">
    <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="12" x2="10" y2="12"/><line x1="8" y1="16" x2="10" y2="16"/><line x1="14" y1="12" x2="16" y2="12"/><line x1="14" y1="16" x2="16" y2="16"/></svg>
  </div>
  <h1 style="font-family:var(--f-display);font-size:1.7rem;font-weight:800;margin-bottom:8px;">Kalkulator Harga Jual</h1>
  <p style="color:var(--t3);font-size:.88rem;margin-bottom:32px;line-height:1.6;">Hitung harga jual produk berdasarkan harga modal dan fee yang kamu tentukan.</p>
</div>
<div class="kalk-card" style="max-width:580px;margin:0 auto;" id="kalk-form">
  <div class="fg"><label class="flabel">Harga Modal (Rp)</label><input type="number" id="k-modal" class="finput" placeholder="Contoh: 15000" min="0"/></div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
    <div class="fg"><label class="flabel">Jenis Fee</label>
      <select id="k-type" class="finput">
        <option value="percent">Persen (%)</option>
        <option value="flat">Nominal Flat (Rp)</option>
      </select>
    </div>
    <div class="fg"><label class="flabel">Nilai Fee</label><input type="number" id="k-fee" class="finput" placeholder="Contoh: 5" min="0" step="0.01"/></div>
  </div>
  <button onclick="calcHarga()" class="btn-submit">Hitung Harga Jual</button>
  <div id="kalk-result" style="display:none;margin-top:20px;"></div>
</div>
<script>
function calcHarga(){
  const modal = parseFloat(document.getElementById('k-modal').value)||0;
  const fee   = parseFloat(document.getElementById('k-fee').value)||0;
  const type  = document.getElementById('k-type').value;
  const res   = document.getElementById('kalk-result');
  if(!modal){res.style.display='block';res.innerHTML='<div class="auth-err">⚠️ Harga modal wajib diisi.</div>';return;}
  const feeAmt = type==='percent' ? modal*fee/100 : fee;
  const jual   = modal + feeAmt;
  const profit = feeAmt;
  res.style.display='block';
  res.innerHTML=`
    <div style="background:var(--card2);border:1px solid rgba(16,185,129,.2);border-radius:var(--r);padding:20px;">
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--b0);font-size:.88rem;"><span style="color:var(--t3)">Harga Modal</span><span>Rp ${modal.toLocaleString('id-ID')}</span></div>
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--b0);font-size:.88rem;"><span style="color:var(--t3)">Fee (${type==='percent'?fee+'%':'flat'})</span><span>Rp ${feeAmt.toLocaleString('id-ID')}</span></div>
      <div style="display:flex;justify-content:space-between;padding:14px 0 0;">
        <span style="font-weight:600;font-size:.95rem;">Harga Jual</span>
        <span style="font-family:var(--f-display);font-size:1.5rem;font-weight:800;color:var(--gold);">Rp ${jual.toLocaleString('id-ID')}</span>
      </div>
      <div style="margin-top:8px;font-size:.8rem;color:var(--t3);">Estimasi profit per transaksi: <strong style="color:var(--green);">Rp ${profit.toLocaleString('id-ID')}</strong></div>
    </div>`;
}
</script>
<?php endif; ?>

<?php endif; ?>
</div>

<?php include __DIR__.'/../includes/footer.php'; ?>