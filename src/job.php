<?php
require 'config.php';
$id = (int)$_GET['id'];

$job = $pdo->query("SELECT * FROM print_jobs WHERE id=$id")->fetch();
echo "<h2>{$job['filename']}</h2>";

$plates = $pdo->query("SELECT * FROM print_plates WHERE print_job_id=$id");
foreach ($plates as $plate) {
    echo "<h3><a href='plate.php?id={$plate['id']}'>Plate {$plate['plate_index']}</a></h3>";
    echo "<img src='{$job['extracted_path']}/Metadata/plate_{$plate['plate_index']}.png' width='300'>";
}
