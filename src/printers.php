<?php
require 'config.php';
$printers = $pdo->query("SELECT * FROM printers")->fetchAll();
?>

<h1>Printers</h1>
<a href="printer_edit.php">Add Printer</a>

<table border="1">
<tr>
  <th>Name</th><th>Status</th><th>Last Seen</th><th></th>
</tr>
<?php foreach ($printers as $p): ?>
<tr>
  <td><?= htmlspecialchars($p['name']) ?></td>
  <td><?= $p['status'] ?? 'unknown' ?></td>
  <td><?= $p['last_seen'] ?></td>
  <td>
    <a href="printer_edit.php?id=<?= $p['id'] ?>">Edit</a> |
    <a href="printer_status.php?id=<?= $p['id'] ?>">Poll</a>
  </td>
</tr>
<?php endforeach; ?>
</table>
