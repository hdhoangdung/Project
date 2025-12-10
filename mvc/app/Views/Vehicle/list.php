<!doctype html>
<html>
<head><meta charset="utf-8"><title>Vehicles</title></head>
<body>
<h1>Vehicles</h1>
<a href="index.php?c=Vehicle&m=add">Add Vehicle</a>
<?php if (!empty($vehicles)): ?>
<table border="1">
  <tr><th>MaXe</th><th>BienSoXe</th><th>SoKhung</th><th>SoMay</th><th>MaKH</th></tr>
  <?php foreach($vehicles as $v): ?>
  <tr>
    <td><?=htmlspecialchars($v['MaXe'])?></td>
    <td><?=htmlspecialchars($v['BienSoXe'])?></td>
    <td><?=htmlspecialchars($v['SoKhung'])?></td>
    <td><?=htmlspecialchars($v['SoMay'])?></td>
    <td><?=htmlspecialchars($v['MaKH'])?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php else: ?>
<p>No vehicles found.</p>
<?php endif; ?>
</body>
</html>
