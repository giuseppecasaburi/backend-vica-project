<?php
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

try {
    $stmt = $pdo->query("SELECT * FROM vica_official_db.catalogo;");

    echo json_encode([
        "status" => "ok",
        "message" => $stmt->fetchAll()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}