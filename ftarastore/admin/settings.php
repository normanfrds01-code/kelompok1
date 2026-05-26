<?php
require_once __DIR__.'/../includes/functions.php';

$pageTitle='Pengaturan — Admin';
if($_SERVER['REQUEST_METHOD']==='POST'){
  verifyCsrf();
  foreach([
    'site_name','site_tagline',
    'whatsapp_number','instagram_url','telegram_url','telegram_channel_url',
    'midtrans_env','midtrans_server_key_sandbox','midtrans_client_key_sandbox',
    'midtrans_server_key_production','midtrans_client_key_production',
    'digi_username','digi_api_key_dev','digi_api_key_prod','digi_env','digi_webhook_secret',
    'fee_type','fee_value','system_status'
  ] as $k){
    $v=trim($_POST[$k]??'');
    db()->prepare("INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=?")->execute([$k,$v,$v]);
  }
  setFlash('success','Pengaturan berhasil disimpan.');
  header('Location: '.asset('admin/settings.php'));exit;
}
$s=[];foreach(db()->query("SELECT `key`,`value` FROM settings")->fetchAll() as $r) $s[$r['key']]=$r['value'];
include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-title">⚙️ Pengaturan</div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:900px">
    <form method="POST" style="grid-column:1/-1">
      <input type="hidden" name="_token" value="<?=csrfToken()?>">

      <!-- Site Info -->
      <div class="admin-card" style="margin-bottom:20px">
        <div class="admin-card-head"><h3>🌐 Informasi Website</h3></div>
        <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div class="fg">
            <label class="flabel">Nama Website</label>
            <input type="text" name="site_name" class="finput" value="<?=htmlspecialchars($s['site_name']??'ftarastore')?>"/>
          </div>
          <div class="fg">
            <label class="flabel">Tagline</label>
            <input type="text" name="site_tagline" class="finput" value="<?=htmlspecialchars($s['site_tagline']??'')?>"/>
          </div>
        </div>
      </div>

      <!-- Kontak -->
      <div class="admin-card" style="margin-bottom:20px">
        <div class="admin-card-head"><h3>📱 Info Kontak</h3></div>
        <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:16px">

          <!-- WhatsApp -->
          <div class="fg">
            <label class="flabel">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="#25d366" style="vertical-align:-2px;margin-right:4px"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
              No. WhatsApp CS
            </label>
            <input type="text" name="whatsapp_number" class="finput" placeholder="628xxx"
                   value="<?=htmlspecialchars($s['whatsapp_number']??'')?>"/>
            <div class="fhint">Format: 628xxxxxxxxxx (tanpa +)</div>
          </div>

          <!-- Instagram -->
          <div class="fg">
            <label class="flabel">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="#e1306c" style="vertical-align:-2px;margin-right:4px"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
              URL Instagram
            </label>
            <input type="url" name="instagram_url" class="finput" placeholder="https://instagram.com/ftarastore"
                   value="<?=htmlspecialchars($s['instagram_url']??'')?>"/>
          </div>

          <!-- Telegram CS — BARU -->
          <div class="fg">
            <label class="flabel">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="#29b6f6" style="vertical-align:-2px;margin-right:4px"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
              URL Telegram CS
            </label>
            <input type="url" name="telegram_url" class="finput" placeholder="https://t.me/ftarastore_cs"
                   value="<?=htmlspecialchars($s['telegram_url']??'')?>"/>
            <div class="fhint">Akun CS untuk chat langsung dengan customer</div>
          </div>

          <!-- Telegram Channel — BARU -->
          <div class="fg">
            <label class="flabel">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="#29b6f6" style="vertical-align:-2px;margin-right:4px;opacity:.7"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
              URL Channel Telegram
            </label>
            <input type="url" name="telegram_channel_url" class="finput" placeholder="https://t.me/ftarastore"
                   value="<?=htmlspecialchars($s['telegram_channel_url']??'')?>"/>
            <div class="fhint">Channel broadcast untuk promo & info update (boleh kosong)</div>
          </div>

        </div>
      </div>

      <!-- Payment -->
      <div class="admin-card" style="margin-bottom:20px">
        <div class="admin-card-head"><h3>💳 Konfigurasi Pembayaran</h3></div>
        <div style="padding:20px">
          <div class="fg">
            <label class="flabel">Midtrans Environment</label>
            <select name="midtrans_env" class="finput" style="max-width:300px">
              <option value="sandbox"    <?=($s['midtrans_env']??'')==='sandbox'   ?'selected':''?>>🧪 Sandbox (Testing)</option>
              <option value="production" <?=($s['midtrans_env']??'')==='production'?'selected':''?>>🚀 Production (Live)</option>
            </select>
            <div class="fhint">Gunakan Sandbox saat testing, Production saat sudah live.</div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="fg">
              <label class="flabel">Jenis Fee Default</label>
              <select name="fee_type" class="finput">
                <option value="percent" <?=($s['fee_type']??'')==='percent'?'selected':''?>>Persentase (%)</option>
                <option value="flat"    <?=($s['fee_type']??'')==='flat'   ?'selected':''?>>Nominal Flat (Rp)</option>
              </select>
            </div>
            <div class="fg">
              <label class="flabel">Nilai Fee</label>
              <input type="number" name="fee_value" class="finput"
                     value="<?=htmlspecialchars($s['fee_value']??'5')?>" step="0.01"/>
            </div>
          </div>
        </div>
      </div>

      <!-- DigiFlazz API -->
      <div class="admin-card" style="margin-bottom:20px">
        <div class="admin-card-head" style="display:flex;align-items:center;justify-content:space-between;">
          <h3>🔄 Konfigurasi DigiFlazz</h3>
          <?php $digiOk=!empty($s['digi_username']); ?>
          <span class="badge <?=$digiOk?'badge-success':'badge-failed'?>" style="font-size:.68rem;">
            <?=$digiOk?'✅ Terkonfigurasi':'❌ Belum diset'?>
          </span>
        </div>
        <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div class="fg" style="grid-column:1/-1">
            <label class="flabel">DigiFlazz Username</label>
            <input type="text" name="digi_username" class="finput"
                   value="<?=htmlspecialchars($s['digi_username']??DIGI_USERNAME??'')?>"
                   placeholder="username DigiFlazz kamu"/>
          </div>
          <div class="fg">
            <label class="flabel">API Key Development</label>
            <input type="text" name="digi_api_key_dev" class="finput"
                   value="<?=htmlspecialchars($s['digi_api_key_dev']??DIGI_API_KEY_DEV??'')?>"
                   placeholder="dev_xxxxxxxxxx"/>
            <div class="fhint">Untuk testing (tidak transaksi nyata)</div>
          </div>
          <div class="fg">
            <label class="flabel">API Key Production</label>
            <input type="text" name="digi_api_key_prod" class="finput"
                   value="<?=htmlspecialchars($s['digi_api_key_prod']??DIGI_API_KEY_PROD??'')?>"
                   placeholder="prod_xxxxxxxxxx"/>
            <div class="fhint">Untuk transaksi nyata</div>
          </div>
          <div class="fg">
            <label class="flabel">Mode DigiFlazz</label>
            <select name="digi_env" class="finput">
              <option value="dev"  <?=($s['digi_env']??DIGI_ENV??'dev')==='dev' ?'selected':''?>>🧪 Development</option>
              <option value="prod" <?=($s['digi_env']??DIGI_ENV??'')==='prod'   ?'selected':''?>>🚀 Production</option>
            </select>
          </div>
          <div class="fg">
            <label class="flabel">Webhook Secret</label>
            <input type="text" name="digi_webhook_secret" class="finput"
                   value="<?=htmlspecialchars($s['digi_webhook_secret']??DIGI_WEBHOOK_SECRET??'')?>"
                   placeholder="webhook secret"/>
          </div>
        </div>
        <div style="padding:0 20px 16px">
          <div style="background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.15);border-radius:7px;padding:10px 14px;font-size:.75rem;color:#fbbf24;">
            💡 <strong>digiflazz.com</strong> → Member Area → Atur Profile → API Credentials
          </div>
        </div>
      </div>

      <!-- Midtrans API -->
      <div class="admin-card" style="margin-bottom:20px">
        <div class="admin-card-head" style="display:flex;align-items:center;justify-content:space-between;">
          <h3>💳 API Keys Midtrans</h3>
          <?php $midOk=!empty($s['midtrans_server_key_sandbox'])||!empty($s['midtrans_server_key_production']); ?>
          <span class="badge <?=$midOk?'badge-success':'badge-failed'?>" style="font-size:.68rem;">
            <?=$midOk?'✅ Terkonfigurasi':'❌ Belum diset'?>
          </span>
        </div>
        <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div class="fg">
            <label class="flabel">Server Key Sandbox</label>
            <input type="text" name="midtrans_server_key_sandbox" class="finput"
                   value="<?=htmlspecialchars($s['midtrans_server_key_sandbox']??MIDTRANS_SERVER_KEY_SANDBOX??'')?>"
                   placeholder="SB-Mid-server-xxxx"/>
          </div>
          <div class="fg">
            <label class="flabel">Client Key Sandbox</label>
            <input type="text" name="midtrans_client_key_sandbox" class="finput"
                   value="<?=htmlspecialchars($s['midtrans_client_key_sandbox']??MIDTRANS_CLIENT_KEY_SANDBOX??'')?>"
                   placeholder="SB-Mid-client-xxxx"/>
          </div>
          <div class="fg">
            <label class="flabel">Server Key Production</label>
            <input type="text" name="midtrans_server_key_production" class="finput"
                   value="<?=htmlspecialchars($s['midtrans_server_key_production']??MIDTRANS_SERVER_KEY_PRODUCTION??'')?>"
                   placeholder="Mid-server-xxxx"/>
          </div>
          <div class="fg">
            <label class="flabel">Client Key Production</label>
            <input type="text" name="midtrans_client_key_production" class="finput"
                   value="<?=htmlspecialchars($s['midtrans_client_key_production']??MIDTRANS_CLIENT_KEY_PRODUCTION??'')?>"
                   placeholder="Mid-client-xxxx"/>
          </div>
        </div>
        <div style="padding:0 20px 16px">
          <div style="background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.15);border-radius:7px;padding:10px 14px;font-size:.75rem;color:#60a5fa;">
            💡 <strong>dashboard.midtrans.com</strong> → Settings → Access Keys → Server Key & Client Key
          </div>
        </div>
      </div>

      <!-- Callback URLs -->
      <div class="admin-card" style="margin-bottom:20px">
        <div class="admin-card-head"><h3>📡 Callback URLs</h3></div>
        <div style="padding:16px 20px;display:flex;flex-direction:column;gap:14px;">
          <div>
            <div style="font-size:.72rem;font-weight:700;color:var(--t2);margin-bottom:6px;">Midtrans — Payment Notification URL:</div>
            <div style="display:flex;align-items:center;gap:8px;">
              <code id="mid-cb" style="flex:1;font-size:.74rem;color:var(--red);background:rgba(227,24,55,.06);padding:7px 10px;border-radius:5px;word-break:break-all;"><?=asset('api/midtrans-callback.php')?></code>
              <button type="button" class="btn-sm btn-sm-edit" onclick="copyUrl('mid-cb',this)">Salin</button>
            </div>
            <div style="font-size:.67rem;color:var(--t3);margin-top:4px;">Settings → Configuration → Payment Notification URL</div>
          </div>
          <div>
            <div style="font-size:.72rem;font-weight:700;color:var(--t2);margin-bottom:6px;">DigiFlazz — Callback URL:</div>
            <div style="display:flex;align-items:center;gap:8px;">
              <code id="digi-cb" style="flex:1;font-size:.74rem;color:var(--red);background:rgba(227,24,55,.06);padding:7px 10px;border-radius:5px;word-break:break-all;"><?=asset('api/digiflazz-callback.php')?></code>
              <button type="button" class="btn-sm btn-sm-edit" onclick="copyUrl('digi-cb',this)">Salin</button>
            </div>
            <div style="font-size:.67rem;color:var(--t3);margin-top:4px;">Atur Profile → Callback URL</div>
          </div>
        </div>
      </div>

      <!-- Status Sistem -->
      <div class="admin-card" style="margin-bottom:20px">
        <div class="admin-card-head">
          <h3 style="display:flex;align-items:center;gap:8px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            Status Sistem
          </h3>
        </div>
        <div style="padding:20px;">
          <div class="fg">
            <label class="flabel">Status Saat Ini</label>
            <select name="system_status" class="finput" style="max-width:300px;">
              <option value="normal"      <?=($s['system_status']??'normal')==='normal'     ?'selected':''?>>● Sistem Normal</option>
              <option value="degraded"    <?=($s['system_status']??'')==='degraded'         ?'selected':''?>>⚠ Ada Gangguan</option>
              <option value="maintenance" <?=($s['system_status']??'')==='maintenance'      ?'selected':''?>>✕ Maintenance</option>
            </select>
            <div class="fhint">Tampil di navbar sebagai indikator status untuk semua pengunjung.</div>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-submit" style="max-width:300px">💾 Simpan Pengaturan</button>
    </form>
  </div>
</div>
</div>
<script>
function copyUrl(id, btn) {
  navigator.clipboard.writeText(document.getElementById(id).textContent.trim()).then(function(){
    var o = btn.textContent; btn.textContent = '✅ Disalin!';
    setTimeout(function(){ btn.textContent = o; }, 2000);
  });
}
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>