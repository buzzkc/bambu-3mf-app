<?php
require 'config.php';
$id = (int)$_GET['id'];

$plate = $pdo->query("SELECT * FROM print_plates WHERE id=$id")->fetch();
echo "<h2>Plate {$plate['plate_index']}</h2>";

$rows = $pdo->query("SELECT * FROM plate_filaments WHERE plate_id=$id");

echo "<table border=1 cellpadding=5>";
echo "<tr><th>Type</th><th>Color</th><th>g</th><th>m</th><th>Tray</th></tr>";
foreach ($rows as $r) {
    echo "<tr>
        <td>{$r['filament_type']}</td>
        <td style='background:{$r['filament_color']}'>{$r['filament_color']}</td>
        <td>{$r['used_g']}</td>
        <td>{$r['used_m']}</td>
        <td>{$r['tray_info_idx']}</td>
    </tr>";
}
echo "</table>";
