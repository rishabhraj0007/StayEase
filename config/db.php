<?php
$host     = "sql211.infinityfree.com";
$dbname   = "if0_42063029_student";
$username = "if0_42063029";
$password = "RishabhRaj";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
