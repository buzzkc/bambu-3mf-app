<?php
require 'config.php';

$q = $pdo->query("
  SELECT q.*, p.gcode_filename, pr.name AS printer
  FROM print_queue q
  JOIN print_plates p ON q.plate_id=p.id
  LEFT JOIN printers pr ON q.printer_id=pr.id
")->fetchAll();
?>

<h1>Print Queue</h1>

<table border="1">
<tr>
  <th>Plate</th><th>Status</th><th>Printer</th>
</tr>
<?php foreach ($q as $row): ?>
<tr>
  <td><?= $row['gcode_filename'] ?></td>
  <td><?= $row['status'] ?></td>
  <td><?= $row['printer'] ?? '-' ?></td>
</tr>
<?php endforeach; ?>
</table>
