<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utility/response.php';

class ProdottoController
{
    public function index()
    {
        global $pdo;

        // Prelievo articoli
        $stmt = $pdo->query("SELECT * FROM articolo");
        $articoli = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Creazione response
        jsonResponse($articoli);
    }

    public function show($id)
    {
        global $pdo;
        $data = [];

        // Prelievo articolo
        $stmt = $pdo->prepare("
            SELECT 
	        a.nome nome_articolo, a.anteprima_it articolo_anteprima_it, a.anteprima_en articolo_anteprima_en, a.descrizione_it articolo_descizione_it, a.descrizione_en articolo_descrizione_en, a.catalogo_id articolo_catalogo_id
            FROM articolo a
            WHERE a.id = ?;
        ");
        $stmt->execute([$id]);
        $articolo = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Salvataggio catalogo_id per query successiva
        $catalogo_id = $articolo["articolo_catalogo_id"];
        $data["articolo"] = $articolo;

        if ($articolo) {
            // Prelievo immagini collegate all'articolo
            $stmt_immagini = $pdo->prepare("
            SELECT 	i.articolo_id immagine_articolo_id, i.id immagine_id, i.slug immagine_slug, i.link immagine_link, i.alt_it immagine_alt_it, i.alt_en immagine_alt_en, i.tipologia_immagine_id
            FROM immagine i
            WHERE i.articolo_id = ?;
            ");
            $stmt_immagini->execute([$id]);
            $articolo_immagini = $stmt_immagini->fetchAll(\PDO::FETCH_ASSOC);

            // Prelievo colori collegati all'articolo
            $stmt_colori = $pdo->prepare("
                SELECT c.id colore_id, c.nome_it nome_colore_it, c.nome_en nome_colore_en, c.tipologia tipologia_colore
                FROM colore c
                WHERE c.catalogo_id = ?;
            ");
            $stmt_colori->execute([$catalogo_id]);
            $articolo_colori = $stmt_colori->fetchAll(\PDO::FETCH_ASSOC);

            // Prelievo articoli correlati all'articolo id=n
            $stmt_correlati = $pdo->prepare("
                SELECT a.id articolo_id ,a.nome nome_articolo, a.anteprima_it articolo_anteprima_it, a.anteprima_en articolo_anteprima_en, a.catalogo_id articolo_catalogo_id
                FROM articolo a
                WHERE a.catalogo_id = ?
                AND a.id != ?
                LIMIT 4;;
            ");
            $stmt_correlati->execute([$catalogo_id, $id]);
            $articolo_correlati = $stmt_correlati->fetchAll(\PDO::FETCH_ASSOC);

            // Creazione response
            $data["immagini_articolo"] = $articolo_immagini;
            $data["colori_articolo"] = $articolo_colori;
            $data["correlati_articolo"] = $articolo_correlati;
            jsonResponse($data);
        } else {
            jsonResponse(['error' => 'Articolo non trovato'], 404);
        }
    }
}
