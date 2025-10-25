<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utility/response.php';

class CatalogoController
{
    public function index()
    {
        global $pdo;

        // Prelievo cataloghi
        $stmt = $pdo->query("
            SELECT c.id id_catalogo, c.nome nome_catalogo, c.anteprima_it anteprima_catalogo_it, c.anteprima_en anteprima_catalogo_en, i.slug img_slug, i.link img_link, i.alt_it img_alt_it, i.alt_en img_alt_en
            FROM vica_official_db.catalogo as c
            INNER JOIN immagine as i on c.id = i.catalogo_id
        ");
        $cataloghi = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Creazione response
        $data["cataloghi"] = $cataloghi;
        jsonResponse($data, $status = 200);
    }

    public function show($id)
    {
        global $pdo;

        // Prelievo catalogo singolo
        $stmt = $pdo->prepare("
            SELECT c.id id_catalogo, c.nome nome_catalogo, c.descrizione_it descrizione_catalogo_it, c.descrizione_en descrizione_catalogo_en, i.slug img_slug, i.link img_link, i.alt_it img_alt_it, i.alt_en img_alt_en
            FROM vica_official_db.catalogo as c
            INNER JOIN immagine as i on c.id = i.catalogo_id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $catalogo = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($catalogo) {

            // Prelievo articoli legati al catalogo
            $stmt = $pdo->prepare("SELECT * FROM articolo WHERE catalogo_id = ?");
            $stmt->execute([$id]);
            $articoli_collegati = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Prelievo articoli in evidenza legati al catalogo
            $stmt_2 = $pdo->prepare("SELECT * FROM articolo WHERE catalogo_id = ? AND preferito = 1");
            $stmt_2->execute([$id]);
            $articoli_preferiti = $stmt_2->fetchAll(\PDO::FETCH_ASSOC);
            
            // Creazione response
            $catalogo['articoli_preferiti'] = $articoli_preferiti;
            $catalogo['articoli_collegati'] = $articoli_collegati;
            jsonResponse($catalogo);
        } else {
            jsonResponse(['error' => 'Catalogo non trovato'], 404);
        }
    }
}