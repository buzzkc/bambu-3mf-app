<h2>Upload .3mf</h2>
<form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button>Upload</button>
</form>
<hr>
<h2>Jobs</h2>

<?php
require 'config.php';
foreach ($pdo->query("SELECT * FROM print_jobs ORDER BY id DESC") as $job) {
    echo "<p><a href='job.php?id={$job['id']}'>{$job['filename']}</a><br/>";
    $plates = $pdo->query("SELECT * FROM print_plates WHERE print_job_id={$job['id']}");
    foreach ($plates as $plate) {
        echo "<h3><a href='plate.php?id={$plate['id']}'>Plate {$plate['plate_index']}</a></h3>";
        echo "<img src='{$job['extracted_path']}/Metadata/plate_{$plate['plate_index']}.png' width='300'>";
        
        $rows = $pdo->query("SELECT * FROM plate_filaments WHERE plate_id={$plate['id']}");

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

    }
    echo "</p>";
}
?>

