<?php
$pdo = new PDO(
    "mysql:host=mysql;dbname=bambu;charset=utf8mb4",
    "bambu",
    "bambu",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
