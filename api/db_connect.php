<?php
// api/db.php
$host = "127.0.0.1";
$port = "8889"; 
$dbname = "studyspot_db";
$user = "root";
$pass = "root";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    die("Erro de ligação: " . $e->getMessage());
}
?>