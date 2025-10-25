<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utility/response.php';

class FaqController {
    public function index() {
        global $pdo;
        $stmt = $pdo->query("
            SELECT f.id faq_id, f.domanda_it, f.domanda_en, f.risposta_it, f.risposta_en
            FROM faq f;
        ");
        $faq = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        jsonResponse($faq);
    }
}