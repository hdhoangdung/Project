<!doctype html>
<html>
<head><meta charset="utf-8"><title>Claim Detail</title></head>
<body>
<h1>Claim Detail</h1>
<?php if (empty($item)) { echo 'Not found'; exit;} ?>
<p>MaYC: <?=htmlspecialchars($item['MaYC'])?></p>
<p>MaHD: <?=htmlspecialchars($item['MaHD'])?></p>
<p>MaKH: <?=htmlspecialchars($item['MaKH'])?></p>
<p>MaXe: <?=htmlspecialchars($item['MaXe'])?></p>
<p>NoiDung: <?=htmlspecialchars($item['NoiDung'])?></p>

<h2>Assess</h2>
<form method="post" action="index.php?c=Claims&m=assess">
  <input type="hidden" name="MaYC" value="<?=htmlspecialchars($item['MaYC'])?>">
  <label>KetQua: <textarea name="KetQua"></textarea></label><br>
  <button type="submit">Assess</button>
</form>

<h2>Approve</h2>
<form method="post" action="index.php?c=Claims&m=approve">
  <input type="hidden" name="MaTD" value=""><!-- fill after assessment -->
  <input type="hidden" name="MaYC" value="<?=htmlspecialchars($item['MaYC'])?>">
  <label>QuyetDinh: <textarea name="QuyetDinh"></textarea></label><br>
  <button type="submit">Approve</button>
</form>

</body>
</html>
