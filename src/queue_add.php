<?php
require 'config.php';

$plate_id = $_GET['plate_id'];
$pdo->prepare(
  "INSERT INTO print_queue (plate_id,job_id,status,requested_at)
   SELECT id, job_id, 'queued', NOW() FROM print_plates WHERE id=?"
)->execute([$plate_id]);

header("Location: queue.php");
