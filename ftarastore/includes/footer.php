<?php // footer.php ?>
<?php if($inAdmin ?? false): ?>
<script src="<?=asset('assets/js/main.js')?>"></script>
</body></html>
<?php return; endif; ?>
</main>
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="footer-logo"><span style="font-family:\'Orbitron\',sans-serif;font-size:14px;font-weight:500;letter-spacing:1px;color:#e8f4ff;">ftar<span style="color:#d40000;font-weight:700;">a</span><span style="color:#d40000;font-weight:300;">store</span></span></div>
      <p>Platform top up game yang aman, murah dan terpercaya. Proses otomatis 1–3 detik. Layanan 24 jam non-stop.</p>
      <div class="footer-social">
        <a href="https://wa.me/<?=getSetting('whatsapp_number','62')?>" target="_blank" aria-label="WhatsApp">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        </a>
        <a href="<?=getSetting('instagram_url','#')?>" target="_blank" aria-label="Instagram">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
        </a>
      </div>
      <span class="footer-pay-label">Metode Pembayaran</span>
      <div class="footer-pay-icons">
        <span class="pay-badge">QRIS</span><span class="pay-badge">GoPay</span><span class="pay-badge">OVO</span>
        <span class="pay-badge">Dana</span><span class="pay-badge">BCA</span><span class="pay-badge">BRI</span><span class="pay-badge">Mandiri</span>
      </div>
    </div>
    <div class="footer-col"><h4>Peta Situs</h4><ul>
      <li><a href="<?=asset('index.php')?>">Beranda</a></li>
      <li><a href="<?=asset('pages/cek-transaksi.php')?>">Cek Transaksi</a></li>
      <li><a href="<?=asset('pages/leaderboard.php')?>">Leaderboard</a></li>
      <li><a href="<?=asset('pages/kalkulator.php')?>">Kalkulator</a></li>
      <li><a href="<?=asset('user/profile.php')?>">Profil Saya</a></li>
    </ul></div>
    <div class="footer-col"><h4>Dukungan</h4><ul>
      <li><a href="https://wa.me/<?=getSetting('whatsapp_number','62')?>" target="_blank">WhatsApp CS</a></li>
      <li><a href="<?=getSetting('instagram_url','#')?>" target="_blank">Instagram</a></li>
      <li><a href="<?=asset('pages/faq.php')?>">FAQ</a></li>
      <li><a href="https://wa.me/<?=getSetting('whatsapp_number','62')?>" target="_blank">Hubungi Kami</a></li>
    </ul></div>
    <div class="footer-col"><h4>Legalitas</h4><ul>
      <li><a href="javascript:void(0)">Kebijakan Privasi</a></li>
      <li><a href="<?=asset('pages/terms.php')?>">Syarat &amp; Ketentuan</a></li>
      <li><a href="<?=asset('pages/faq.php')?>?q=refund">Kebijakan Refund</a></li>
      <li><a href="<?=asset('pages/faq.php')?>">FAQ</a></li>
<li><a href="<?=asset('pages/terms.php')?>">Syarat & Ketentuan</a></li> 
    </ul></div>
  </div>
  <div class="footer-bottom">
    <span>© <?=date('Y')?> ftarastore. All rights reserved.</span>
    <span>Powered by <strong>Digiflazz</strong> &amp; <strong>Midtrans</strong></span>
  </div>
</footer>
<style>
/* ══ CS BUTTON — modern floating style ══ */
.chat-cs-wrap {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 10px;
}
.chat-cs-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #0a0c15;
  border: 1.5px solid rgba(212,0,0,.35);
  border-radius: 50px;
  padding: 12px 20px 12px 14px;
  color: white;
  text-decoration: none;
  cursor: pointer;
  transition: all .3s cubic-bezier(.34,1.56,.64,1);
  box-shadow: 0 4px 20px rgba(0,0,0,.5), 0 0 0 0 rgba(227,24,55,0);
  white-space: nowrap;
  max-width: 160px;
  overflow: hidden;
}
.chat-cs-btn:hover {
  border-color: rgba(212,0,0,.8);
  box-shadow: 0 4px 24px rgba(0,0,0,.6), 0 0 20px rgba(212,0,0,.25), 0 0 40px rgba(212,0,0,.12);
  transform: translateY(-2px);
  background: #0c1020;
}
.chat-cs-icon {
  width: 36px; height: 36px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  background: linear-gradient(135deg,#25d366,#128c7e);
  box-shadow: 0 2px 10px rgba(37,211,102,.4);
  transition: transform .3s;
}
.chat-cs-btn:hover .chat-cs-icon { transform: rotate(-5deg) scale(1.08); }
.chat-cs-label {
  display: flex; flex-direction: column; gap: 1px;
}
.chat-cs-label .cs-title {
  font-size: .78rem; font-weight: 700; color: #e8eaf0; letter-spacing: .2px;
}
.chat-cs-label .cs-sub {
  font-size: .65rem; color: #64748b;
}
/* Pulse ring */
.chat-cs-btn::before {
  content: '';
  position: absolute;
  inset: -1px; border-radius: 50px;
  border: 1px solid rgba(212,0,0,.3);
  animation: csPulse 3s ease infinite;
}
@keyframes csPulse {
  0%,100%{opacity:0;transform:scale(1)}
  50%{opacity:1;transform:scale(1.02)}
}
</style>

<style>
.cs-fab-wrap { position:fixed;bottom:28px;right:28px;z-index:9999;display:flex;flex-direction:column;align-items:flex-end;gap:10px; }
.cs-fab-btn {
  display:flex;align-items:center;gap:10px;
  background:#0a0c15;border:1.5px solid rgba(212,0,0,.35);border-radius:50px;
  padding:10px 18px 10px 12px;color:white;text-decoration:none;cursor:pointer;
  transition:all .3s cubic-bezier(.34,1.56,.64,1);
  box-shadow:0 4px 20px rgba(0,0,0,.5);white-space:nowrap;position:relative;
}
.cs-fab-btn:hover {
  border-color:rgba(227,24,55,.8);
  box-shadow:0 4px 24px rgba(0,0,0,.6),0 0 20px rgba(212,0,0,.2);
  transform:translateY(-2px);background:#0c1020;
}
.cs-fab-icon {
  width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;
  background:linear-gradient(135deg,#25d366,#128c7e);box-shadow:0 2px 10px rgba(37,211,102,.4);
}
.cs-fab-label { display:flex;flex-direction:column;gap:1px; }
.cs-fab-label .cs-title { font-size:.78rem;font-weight:700;color:#e8eaf0; }
.cs-fab-label .cs-sub { font-size:.65rem;color:#64748b; }
/* Popup menu */
.cs-popup { display:none;flex-direction:column;gap:6px;margin-bottom:6px; }
.cs-popup.open { display:flex; }
.cs-option {
  display:flex;align-items:center;gap:10px;padding:9px 14px;
  background:#0c1020;border:1px solid rgba(255,255,255,.08);border-radius:10px;
  color:white;text-decoration:none;font-size:.8rem;font-weight:500;
  transition:all .2s;cursor:pointer;
}
.cs-option:hover { transform:translateX(-4px);border-color:rgba(212,0,0,.3); }
.cs-option-icon { width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
</style>

<div class="cs-fab-wrap">
  <!-- Popup options -->
  <div class="cs-popup" id="cs-popup">
    <a href="https://wa.me/<?=getSetting('whatsapp_number','62')?>" target="_blank" class="cs-option">
      <div class="cs-option-icon" style="background:linear-gradient(135deg,#25d366,#128c7e);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
      </div>
      WhatsApp CS
    </a>
    <?php $tg=getSetting('telegram_url',''); if($tg): ?>
    <a href="<?=htmlspecialchars($tg)?>" target="_blank" class="cs-option">
      <div class="cs-option-icon" style="background:linear-gradient(135deg,#2aabee,#1d8cc8);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.19 13.671l-2.948-.924c-.64-.203-.658-.64.135-.954l11.57-4.461c.537-.194 1.006.131.947.889z"/></svg>
      </div>
      Telegram CS
    </a>
    <?php $tgch=getSetting('telegram_channel_url',''); if($tgch): ?>
    <a href="<?=htmlspecialchars($tgch)?>" target="_blank" class="cs-option">
      <div class="cs-option-icon" style="background:linear-gradient(135deg,#2aabee,#1d8cc8);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.19 13.671l-2.948-.924c-.64-.203-.658-.64.135-.954l11.57-4.461c.537-.194 1.006.131.947.889z"/></svg>
      </div>
      Channel Telegram
    </a>
    <?php endif; endif; ?>
  </div>
  <!-- FAB button -->
  <button class="cs-fab-btn" id="cs-fab-btn" onclick="document.getElementById('cs-popup').classList.toggle('open')">
    <div class="cs-fab-icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </div>
    <div class="cs-fab-label">
      <span class="cs-title">Chat CS</span>
      <span class="cs-sub">Siap membantu</span>
    </div>
  </button>
</div>
<script src="<?=asset('assets/js/main.js')?>"></script>
</body></html>