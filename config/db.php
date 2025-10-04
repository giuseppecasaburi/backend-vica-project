<?php
require_once __DIR__ . '/env.php';

caricaEnv(__DIR__ . "/../.env");

$host = getenv("DB_HOST");
$port = getenv("DB_PORT");
$dbname = getenv("DB_NAME");
$username = getenv("DB_USER");
$password = getenv("DB_PASS");


try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $error) {
    http_response_code(500);
    echo json_encode(['errore' => 'Connessione fallita: ' . $error->getMessage()]);
    exit;
}