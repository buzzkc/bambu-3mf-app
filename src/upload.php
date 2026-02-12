<?php
require 'config.php';
require 'lib/3mf_parser.php';

$file = $_FILES['file'];
$stmt = $pdo->prepare("INSERT INTO print_jobs (filename) VALUES (?)");
$stmt->execute([$file['name']]);
$jobId = $pdo->lastInsertId();

$jobDir = "uploads/3mf/$jobId";
mkdir("$jobDir/extracted", 0777, true);

move_uploaded_file($file['tmp_name'], "$jobDir/original.3mf");

$zip = new ZipArchive();
$zip->open("$jobDir/original.3mf");
$zip->extractTo("$jobDir/extracted");
$zip->close();

$pdo->prepare("UPDATE print_jobs SET extracted_path=? WHERE id=?")
    ->execute([$jobDir . "/extracted", $jobId]);

$xml = simplexml_load_file("$jobDir/extracted/Metadata/slice_info.config");

$filaments = [];
foreach ($xml->plate->filament as $f) {
    $id = (int)$f['id'];
    $filaments[$id] = [
        'type' => (string)$f['type'],
        'color' => (string)$f['color'],
        'used_g' => (float)$f['used_g'],
        'used_m' => (float)$f['used_m'],
        'tray' => (string)$f['tray_info_idx']
    ];
}

foreach (glob("$jobDir/extracted/Metadata/plate_*.gcode") as $gcodePath) {
    preg_match('/plate_(\d+)\.gcode/', basename($gcodePath), $m);
    $plateIndex = (int)$m[1];

    $pdo->prepare(
        "INSERT INTO print_plates (print_job_id, plate_index, gcode_filename)
         VALUES (?, ?, ?)"
    )->execute([$jobId, $plateIndex, basename($gcodePath)]);

    $plateId = $pdo->lastInsertId();

    foreach (file($gcodePath) as $line) {
        if (preg_match('/^;\s*filament:\s*(.+)$/', trim($line), $m)) {
            foreach (explode(',', $m[1]) as $fid) {
                $f = $filaments[(int)$fid];
                $pdo->prepare(
                    "INSERT INTO plate_filaments
                     (plate_id, filament_id, filament_type, filament_color, used_g, used_m, tray_info_idx)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                )->execute([
                    $plateId, $fid, $f['type'], $f['color'],
                    $f['used_g'], $f['used_m'], $f['tray']
                ]);
            }
            break;
        }
    }
}

header("Location: index.php");
