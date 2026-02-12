<?php

function parse_slicer_info($path) {
    if (!file_exists($path)) return [];

    $config = parse_ini_file($path, true, INI_SCANNER_TYPED);
    return $config;
}

function parse_gcode_header($path) {

    $data = [];

    foreach (file($path) as $line) {
        $line = trim($line);

        if ($line === '' || $line[0] !== ';') break;

        if (preg_match('/^;\s*([^=]+)\s*=\s*(.+)$/', $line, $m)) {
            $data[$m[1]] = array_map('trim', explode(';', $m[2]));
        }
    }

    return $data;
}

function insert_plate($pdo, $jobId, $filename) {
    $stmt = $pdo->prepare("
        INSERT INTO plates (job_id, filename)
        VALUES (?, ?)
    ");
    $stmt->execute([$jobId, $filename]);
    return $pdo->lastInsertId();
}

function insert_plate_filaments($pdo, $plateId, $header, $meta) {

    if (empty($header['filament_type'])) return;

    foreach ($header['filament_type'] as $idx => $type) {

        $stmt = $pdo->prepare("
            INSERT INTO plate_filaments
            (plate_id, filament_index, filament_type,
             filament_color, used_g, used_mm)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $plateId,
            $idx,
            $type,
            $header['filament_color'][$idx] ?? null,
            $header['filament_used_g'][$idx] ?? null,
            $header['filament_used_mm'][$idx] ?? null
        ]);
    }
}
