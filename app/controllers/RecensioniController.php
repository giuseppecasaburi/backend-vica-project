<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utility/response.php';

class RecensioniController {
    public function index() {
        global $pdo;
        $stmt = $pdo->query("
            SELECT r.id id_recensione, r.nome_utente, r.recensione, i.id id_foto_utente, i.slug slug_foto_utente, i.link link_foto_utente, i.alt_it alt_it_foto_utente, i.alt_en alt_en_foto_utente
            FROM recensione r
            INNER JOIN immagine i on i.recensione_id = r.id;
        ");
        $recensioni = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        jsonResponse($recensioni);
    }
}