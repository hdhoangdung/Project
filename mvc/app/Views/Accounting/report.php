<!doctype html>
<html>
<head><meta charset="utf-8"><title>Accounting Report</title></head>
<body>
<h1>Accounting Report (<?=htmlspecialchars($data['type'])?>)</h1>
<p>Year: <?=htmlspecialchars($data['year'])?> Month: <?=htmlspecialchars($data['month'])?></p>
<p>Total Receipts: <?=number_format($data['receipts'],2)?></p>
<p>Total Payouts: <?=number_format($data['payouts'],2)?></p>
<p>Net: <?=number_format($data['net'],2)?></p>
</body>
</html>
