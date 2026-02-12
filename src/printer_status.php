<?php
require 'config.php';
require 'mqtt/bambu_client.php';

$id = $_GET['id'];
$printer = $pdo->query("SELECT * FROM printers WHERE id=$id")->fetch();
try {
	echo "connecting to mqtt";
	$client = bambu_connect($printer);
	if (!$client) echo "No mqtt connection";
	echo "client valid, subscribing";
	$client->subscribe(
	  "device/{$printer['serial_number']}/report",
	  function ($topic, $msg) use ($pdo, $printer) {
		//echo sprintf("Received message on topic [%s]: %s\n", $topic, $msg);
		$data = json_decode($msg, true);
		$status = $data['print']['status'] ?? 'unknown';

		$stmt = $pdo->prepare("UPDATE printers SET status=?, last_seen=NOW() WHERE id=?");
		$stmt->execute([$status, $printer['id']]);

		foreach ($data['ams']['ams'] ?? [] as $ams) {
		  foreach ($ams['tray'] as $slot) {
			$pdo->prepare(
			  "REPLACE INTO printer_ams_slots (printer_id,slot_index,filament_type,filament_color,updated_at)
			   VALUES (?,?,?,?,NOW())"
			)->execute([
			  $printer['id'],
			  $slot['tray_id'],
			  $slot['tray_material'],
			  $slot['tray_color']
			]);
		  }
		}
	  }, 0
	);
	echo "subscribe complete";
	$client->loop(true);
	$client->disconnect();
} catch (MqttClientException $e) {
    // MqttClientException is the base exception of all exceptions in the library
    echo "Connecting or publishing failed. An exception occurred.\n";
    echo $e->getMessage() . "\n";
} catch (Exception $e) {
    // MqttClientException is the base exception of all exceptions in the library
    echo "An exception occurred.\n";
    echo $e->getMessage() . "\n";
}


echo "Updated printer status.";
