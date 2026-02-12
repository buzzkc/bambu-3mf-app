<?php
require 'config.php';

$id = $_GET['id'] ?? null;
$printer = ['name'=>'','serial_number'=>'','mqtt_user'=>'','mqtt_access_code'=>'','ip_address'=>''];

if ($id) {
  $stmt = $pdo->prepare("SELECT * FROM printers WHERE id=?");
  $stmt->execute([$id]);
  $printer = $stmt->fetch();
}

if ($_POST) {
  $stmt = $id
    ? $pdo->prepare("UPDATE printers SET name=?,serial_number=?,mqtt_user=?,mqtt_access_code=?,ip_address=? WHERE id=?")
    : $pdo->prepare("INSERT INTO printers (name,serial_number,mqtt_user,mqtt_access_code,ip_address) VALUES (?,?,?,?,?)");

  $data = array_values($_POST);
  if ($id) $data[] = $id;
  $stmt->execute($data);
  header("Location: printers.php");
}
?>

<form method="post">
Name <input name="name" value="<?= $printer['name'] ?>"><br>
Serial <input name="serial_number" value="<?= $printer['serial_number'] ?>"><br>
MQTT User <input name="mqtt_user" value="<?= $printer['mqtt_user'] ?>"><br>
Access Code <input name="mqtt_access_code" value="<?= $printer['mqtt_access_code'] ?>"><br>
IP Address <input name="ip_address" value="<?= $printer['ip_address'] ?>"><br>
<button>Save</button>
</form>
