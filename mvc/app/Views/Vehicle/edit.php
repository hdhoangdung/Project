<!doctype html>
<html>
<head><meta charset="utf-8"><title>Edit Vehicle</title></head>
<body>
<?php if (empty($vehicle)) { echo 'Vehicle not found'; exit; } ?>
<h1>Edit Vehicle <?=htmlspecialchars($vehicle['MaXe'])?></h1>
<form method="post" action="index.php?c=Vehicle&m=edit&id=<?=urlencode($vehicle['MaXe'])?>">
  <label>BienSoXe: <input type="text" name="BienSoXe" value="<?=htmlspecialchars($vehicle['BienSoXe'])?>"></label><br>
  <label>SoKhung: <input type="text" name="SoKhung" value="<?=htmlspecialchars($vehicle['SoKhung'])?>"></label><br>
  <label>SoMay: <input type="text" name="SoMay" value="<?=htmlspecialchars($vehicle['SoMay'])?>"></label><br>
  <label>HangXe: <input type="text" name="HangXe" value="<?=htmlspecialchars($vehicle['HangXe'])?>"></label><br>
  <label>NamSX: <input type="number" name="NamSX" value="<?=htmlspecialchars($vehicle['NamSX'])?>"></label><br>
  <label>LoaiXe: <input type="text" name="LoaiXe" value="<?=htmlspecialchars($vehicle['LoaiXe'])?>"></label><br>
  <button type="submit">Save</button>
</form>
</body>
</html>
