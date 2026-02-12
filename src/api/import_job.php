<?php
require '../config.php';
require '../lib/3mf_parser.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (
    !$input ||
    empty($input['url']) ||
    empty($input['subtask_name']) ||
    empty($input['param'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$url          = $input['url'];
$subtaskName  = preg_replace('/[^A-Za-z0-9_\-]/', '', $input['subtask_name']);
$param        = str_replace('\\', '/', $input['param']);
$plateIndex = $input['plate_idx'];

// 🔒 Security: prevent path traversal
if (strpos($param, '..') !== false || strpos($param, '/') === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid param path']);
    exit;
}

try {

    $renamedFile = $subtaskName . '.gcode.3mf';

    // 1️⃣ Insert job record
    $stmt = $pdo->prepare("
        INSERT INTO print_jobs (filename, extracted_path, created_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$renamedFile, $subtaskName]);

    $jobId = $pdo->lastInsertId();
	
	$jobDir = "uploads/3mf/$jobId";
	$pdo->prepare("UPDATE print_jobs SET extracted_path=? WHERE id=?")
    ->execute([$jobDir . "/extracted", $jobId]);

    $jobDir = __DIR__ . "/../uploads/3mf/$jobId";
    mkdir("$jobDir/extracted", 0777, true);

    $filePath = "$jobDir/$renamedFile";

    // 2️⃣ Download file
    $fileData = file_get_contents($url);
    if (!$fileData) {
        throw new Exception("Failed to download file");
    }

    file_put_contents($filePath, $fileData);

    // 3️⃣ Extract 3MF archive
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== TRUE) {
        throw new Exception("Failed to open 3MF archive");
    }

    $zip->extractTo("$jobDir/extracted");
    $zip->close();

    // 4️⃣ Open selected gcode from param
    $plateFile = "$jobDir/extracted/$param";

    if (!file_exists($plateFile)) {
        throw new Exception("Gcode file not found: $param");
    }

    // 6️⃣ Parse header
    $headerData = parse_gcode_header($plateFile);

    // 7️⃣ Parse slicer info if available
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

    echo json_encode([
        'success'        => true,
        'print_job_id'   => $jobId,
        'print_plate_id' => $plateId,
        'saved_filename' => $renamedFile,
        'gcode_used'     => $param
    ]);

} catch (Throwable $e) {

    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
