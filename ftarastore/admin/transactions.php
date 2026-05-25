<?php
require_once __DIR__.'/../includes/functions.php';
Security::boot(['admin']);
$pageTitle = 'Transaksi — Admin';

//  Handle POST actions 
if($_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf();
    $act = $_POST['action']??'';

    if($act==='retry_topup'){
        $id = (int)$_POST['order_id'];
        $ord = db()->prepare("SELECT o.*, p.digi_code FROM orders o JOIN products p ON p.id=o.product_id WHERE o.id=?");
        $ord->execute([$id]); $ord = $ord->fetch();
        if($ord && in_array($ord['status'],['failed','paid','processing'])){
            $cn = $ord['game_user_id'].($ord['server_id']?'|'.$ord['server_id']:'');
            $dr = digiTopUp($ord['digi_code'], $cn, $ord['order_code'].'-R'.time());
            $ds = strtolower($dr['data']['status']??'gagal');
            $fs = $ds==='sukses'?'success':($ds==='process'||$ds==='pending'?'processing':'failed');
            db()->prepare("UPDATE orders SET status=?,updated_at=NOW() WHERE id=?")->execute([$fs,$id]);
            $tx = db()->prepare("SELECT id FROM transactions WHERE order_id=?"); $tx->execute([$id]);
            if($tx->fetch()){
                db()->prepare("UPDATE transactions SET status=?,message=?,sn=?,raw_response=?,updated_at=NOW() WHERE order_id=?")->execute([$ds,$dr['data']['message']??null,$dr['data']['sn']??null,json_encode($dr),$id]);
            } else {
                db()->prepare("INSERT INTO transactions (order_id,digi_buyer_sku,status,message,sn,raw_response) VALUES (?,?,?,?,?,?)")->execute([$id,$ord['digi_code'],$ds,$dr['data']['message']??null,$dr['data']['sn']??null,json_encode($dr)]);
            }
            setFlash($ds==='sukses'?'success':'error','Retry: '.($dr['data']['message']??$ds));
        } else { setFlash('error','Order tidak dapat di-retry.'); }
    }
    elseif($act==='delete_one'){
        $id = (int)$_POST['order_id'];
        // Hapus data terkait dulu
        db()->prepare("DELETE FROM transactions WHERE order_id=?")->execute([$id]);
        db()->prepare("DELETE FROM payments WHERE order_id=?")->execute([$id]);
        db()->prepare("DELETE FROM orders WHERE id=?")->execute([$id]);
        Security::audit('ORDER_DELETED',"Order #$id dihapus oleh admin");
        setFlash('success','Transaksi berhasil dihapus.');
    }

    elseif($act==='delete_bulk'){
        $ids = array_map('intval', $_POST['ids']??[]);
        if(!empty($ids)){
            $in = implode(',', $ids);
            db()->exec("DELETE FROM transactions WHERE order_id IN ($in)");
            db()->exec("DELETE FROM payments WHERE order_id IN ($in)");
            db()->exec("DELETE FROM orders WHERE id IN ($in)");
            Security::audit('ORDER_BULK_DELETED',count($ids).' order dihapus bulk');
            setFlash('success',count($ids).' transaksi berhasil dihapus.');
        }
    }

    header('Location: '.asset('admin/transactions.php').'?'
        .http_build_query(['q'=>$_GET['q']??'','status'=>$_GET['status']??'','page'=>$_GET['page']??1]));
    exit;
}

//  Query 
$page   = max(1,(int)($_GET['page']??1));
$limit  = 20; $offset = ($page-1)*$limit;
$q      = trim($_GET['q']??'');
$sf     = trim($_GET['status']??'');
$where  = 'WHERE 1=1'; $params = [];
if($q){ $where .= " AND (o.order_code LIKE ? OR o.buyer_email LIKE ? OR o.game_user_id LIKE ?)"; $params = [...$params,"%$q%","%$q%","%$q%"]; }
if($sf){ $where .= " AND o.status=?"; $params[] = $sf; }

$total = (int)db()->prepare("SELECT COUNT(*) FROM orders o $where")->execute($params) ? db()->prepare("SELECT COUNT(*) FROM orders o $where") : 0;
$totalStmt = db()->prepare("SELECT COUNT(*) FROM orders o $where"); $totalStmt->execute($params); $total = (int)$totalStmt->fetchColumn();
$pages = max(1, ceil($total/$limit));

$stmt = db()->prepare("
    SELECT o.*, py.payment_method, py.midtrans_id, t.status AS ts
    FROM orders o
    LEFT JOIN payments py ON py.order_id = o.id
    LEFT JOIN transactions t ON t.order_id = o.id
    $where ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset
");
$stmt->execute($params); $orders = $stmt->fetchAll();

$stMap = ['pending'=>'pending','paid'=>'process','processing'=>'process','success'=>'success','failed'=>'failed','refunded'=>'pending'];

include __DIR__.'/../includes/header.php';
?>
<div class="admin-wrap">
<?php include __DIR__.'/sidebar.php'; ?>
<div class="admin-main">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div class="admin-title" style="margin:0;display:flex;align-items:center;gap:10px;">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      Semua Transaksi
    </div>
    <!-- Bulk delete button (hidden by default) -->
    <button id="btn-bulk-del" onclick="submitBulkDelete()"
      style="display:none;align-items:center;gap:7px;padding:8px 18px;border-radius:8px;border:1px solid rgba(239,68,68,.3);color:#fca5a5;background:rgba(239,68,68,.06);font-size:.82rem;font-weight:600;cursor:pointer;transition:all .15s;"
      onmouseover="this.style.background='rgba(239,68,68,.15)'" onmouseout="this.style.background='rgba(239,68,68,.06)'">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
      Hapus Terpilih (<span id="sel-count">0</span>)
    </button>
  </div>

  <!-- Filter -->
  <form method="GET" style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;">
    <input type="text" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Cari kode, email, ID game..." class="finput" style="flex:1;min-width:200px;max-width:340px;"/>
    <select name="status" class="finput" style="width:auto;">
      <option value="">Semua Status</option>
      <?php foreach(['pending','paid','processing','success','failed','refunded'] as $s): ?>
      <option value="<?=$s?>" <?=$sf===$s?'selected':''?>><?=ucfirst($s)?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-gold" style="padding:10px 20px;font-size:.88rem;">Filter</button>
    <?php if($q||$sf): ?>
    <a href="<?=asset('admin/transactions.php')?>" class="btn-ghost" style="padding:10px 16px;display:flex;align-items:center;font-size:.84rem;">Reset</a>
    <?php endif; ?>
  </form>

  <!-- Bulk delete form -->
  <form id="bulk-form" method="POST">
    <input type="hidden" name="_token" value="<?=csrfToken()?>">
    <input type="hidden" name="action" value="delete_bulk">
    <div id="bulk-ids"></div>
  </form>

  <div class="admin-card">
    <div class="admin-card-head">
      <h3>Daftar Transaksi <span style="font-size:.78rem;color:var(--t3);font-weight:400;">(<?=number_format($total)?> total)</span></h3>
      <?php if(!empty($orders)): ?>
      <label style="display:flex;align-items:center;gap:7px;font-size:.78rem;color:var(--t2);cursor:pointer;">
        <input type="checkbox" id="check-all" onchange="toggleAll(this)" style="accent-color:var(--gold);width:14px;height:14px;cursor:pointer;"/>
        Pilih Semua
      </label>
      <?php endif; ?>
    </div>
    <div class="table-wrap">
      <table class="dtable">
        <thead><tr>
          <th style="width:36px;"></th>
          <th>Kode Order</th><th>Produk</th><th>ID Game</th>
          <th>Email</th><th>Total</th><th>Bayar</th><th>Top-Up</th>
          <th>Waktu</th><th>Aksi</th>
        </tr></thead>
        <tbody>
        <?php if(empty($orders)): ?>
        <tr><td colspan="10" style="text-align:center;padding:40px;color:var(--t3);">Tidak ada transaksi ditemukan.</td></tr>
        <?php else: foreach($orders as $o):
          $sc = $stMap[$o['status']] ?? 'pending';
        ?>
        <tr id="row-<?=$o['id']?>">
          <!-- Checkbox -->
          <td style="padding-left:16px;">
            <input type="checkbox" class="row-check" value="<?=$o['id']?>" onchange="updateBulkBar()"
                   style="accent-color:var(--gold);width:14px;height:14px;cursor:pointer;"/>
          </td>
          <td><span class="code" style="font-size:.82rem;"><?=htmlspecialchars($o['order_code'])?></span></td>
          <td style="font-size:.82rem;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($o['product_name'])?></td>
          <td style="font-size:.81rem;color:var(--t2);"><?=htmlspecialchars($o['game_user_id'])?></td>
          <td style="font-size:.77rem;color:var(--t3);"><?=htmlspecialchars($o['buyer_email'])?></td>
          <td style="color:var(--gold);font-weight:700;font-size:.88rem;"><?=formatRupiah($o['amount'])?></td>
          <td><span class="badge badge-<?=$sc?>" style="font-size:.69rem;"><?=ucfirst($o['status'])?></span></td>
          <td>
            <?php if($o['ts']): $tc=$o['ts']==='sukses'?'success':($o['ts']==='gagal'?'failed':'process'); ?>
            <span class="badge badge-<?=$tc?>" style="font-size:.69rem;"><?=ucfirst($o['ts'])?></span>
            <?php else: ?><span style="color:var(--t3);font-size:.77rem;">—</span><?php endif; ?>
          </td>
          <td style="font-size:.75rem;color:var(--t3);white-space:nowrap;"><?=date('d/m/y H:i',strtotime($o['created_at']))?></td>
          <!-- Aksi -->
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
              <!-- Detail -->
              <button type="button" class="btn-sm btn-sm-edit" style="font-size:.72rem;"
                data-order="<?=htmlspecialchars(json_encode($o),ENT_QUOTES)?>"
                onclick="showDetailEl(this)">Detail</button>
              <!-- Retry -->
              <?php if(in_array($o['status'],['failed','paid','processing'])): ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="retry_topup">
                <input type="hidden" name="order_id" value="<?=$o['id']?>">
                <button type="submit" class="btn-sm btn-sm-edit" style="font-size:.72rem;color:#38bdf8;border-color:rgba(56,189,248,.3);"
                  onclick="return confirm('Retry top-up order ini?')">Retry</button>
              </form>
              <?php endif; ?>
              <!-- Hapus -->
              <form method="POST" style="display:inline;">
                <input type="hidden" name="_token" value="<?=csrfToken()?>">
                <input type="hidden" name="action" value="delete_one">
                <input type="hidden" name="order_id" value="<?=$o['id']?>">
                <button type="submit"
                  data-code="<?=htmlspecialchars($o['order_code'],ENT_QUOTES)?>"
                  onclick="return confirm('Hapus transaksi '+this.dataset.code+' secara permanen?')"
                  class="btn-sm btn-sm-danger" style="font-size:.72rem;">Hapus</button>
              </form>
            </div>
          </td>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if($pages>1): ?>
    <div style="display:flex;gap:5px;padding:16px 20px;flex-wrap:wrap;">
      <?php for($i=1;$i<=$pages;$i++): ?>
      <a href="?page=<?=$i?>&q=<?=urlencode($q)?>&status=<?=urlencode($sf)?>"
         style="padding:6px 12px;border-radius:6px;font-size:.82rem;<?=$i===$page?'background:var(--gold);color:#0b0d14;font-weight:700':'background:var(--card2);color:var(--t2);border:1px solid var(--b1)'?>"><?=$i?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>

</div>
</div>

<script>
function updateBulkBar(){
  const checked = document.querySelectorAll('.row-check:checked');
  const btn = document.getElementById('btn-bulk-del');
  const cnt = document.getElementById('sel-count');
  cnt.textContent = checked.length;
  btn.style.display = checked.length >0 ? 'inline-flex' : 'none';
  // Highlight selected rows
  document.querySelectorAll('.row-check').forEach(cb => {
    const row = document.getElementById('row-' + cb.value);
    if(row) row.style.background = cb.checked ? 'rgba(239,68,68,.05)' : '';
  });
}

function toggleAll(master){
  document.querySelectorAll('.row-check').forEach(cb => { cb.checked = master.checked; });
  updateBulkBar();
}

function submitBulkDelete(){
  const checked = document.querySelectorAll('.row-check:checked');
  if(!checked.length) return;
  if(!confirm('Hapus ' + checked.length + ' transaksi secara permanen? Tindakan ini tidak bisa dibatalkan!')) return;
  const container = document.getElementById('bulk-ids');
  container.innerHTML = '';
  checked.forEach(cb => {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
    container.appendChild(inp);
  });
  document.getElementById('bulk-form').submit();
}
</script>

<!-- Detail Modal -->
<div class="modal" id="modal-detail">
  <div class="modal-box" style="max-width:560px;">
    <div class="modal-head"><h3>Detail Transaksi</h3><button class="modal-close" data-modal-close="modal-detail">✕</button></div>
    <div id="detail-content"></div>
    <div class="modal-footer" style="margin-top:16px;padding-top:14px;border-top:1px solid var(--b1);">
      <a id="detail-inv-btn" href="javascript:void(0)" target="_blank" class="btn-submit" style="flex:1;display:flex;align-items:center;justify-content:center;text-decoration:none;">Invoice</a>
      <button class="btn-ghost" data-modal-close="modal-detail" style="flex:1;border-radius:10px;padding:13px;">Tutup</button>
    </div>
  </div>
</div>
<script>
function showDetailEl(btn){
  var o = JSON.parse(btn.dataset.order);
  var sc={'pending':'var(--amber)','paid':'var(--blue)','processing':'var(--cyan)','success':'var(--green)','failed':'var(--red)','refunded':'var(--t3)'};
  var rows=[['Kode Order','<strong style="color:var(--cyan)">'+o.order_code+'</strong>'],['Produk',o.product_name],['ID Akun Game',o.game_user_id+(o.server_id?' / '+o.server_id:'')],['Email',o.buyer_email],['HP',o.buyer_phone||'—'],['Total','<strong>Rp '+parseInt(o.amount).toLocaleString('id-ID')+'</strong>'],['Metode Bayar',o.payment_method?o.payment_method.toUpperCase():'—'],['Status','<span style="color:'+(sc[o.status]||'var(--t2)')+'">'+o.status.toUpperCase()+'</span>'],['Status Top-Up',o.ts?o.ts.toUpperCase():'—'],['Waktu',o.created_at]];
  var html='<table style="width:100%;border-collapse:collapse;">';
  rows.forEach(function(r){ html+='<tr><td style="padding:8px 0;color:var(--t3);width:130px;border-bottom:1px solid var(--b0)">'+r[0]+'</td><td style="padding:8px 0;border-bottom:1px solid var(--b0)">'+r[1]+'</td></tr>'; });
  html+='</table>';
  document.getElementById('detail-content').innerHTML=html;
  document.getElementById('detail-inv-btn').href='<?=asset("pages/invoice.php")?>?code='+o.order_code;
  document.getElementById('modal-detail').classList.add('show');
}
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>