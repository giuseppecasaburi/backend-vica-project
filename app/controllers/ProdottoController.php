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

        // Query articolo (senza varianti)
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.nome nome_articolo, 
                a.anteprima_it articolo_anteprima_it, 
                a.anteprima_en articolo_anteprima_en, 
                a.descrizione_it articolo_descrizione_it, 
                a.descrizione_en articolo_descrizione_en, 
                a.catalogo_id articolo_catalogo_id
            FROM articolo a
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $articolo = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($articolo === false) {
            jsonResponse(['error' => 'Articolo non trovato'], 404);
            return;
        }

        // Query varianti dell'articolo
        $stmt_varianti = $pdo->prepare("
            SELECT 
                id,
                altezza, 
                larghezza, 
                profondita, 
                nome_variante,
                articolo_id
            FROM variante_articolo
            WHERE articolo_id = ?
        ");
        $stmt_varianti->execute([$id]);
        $varianti = $stmt_varianti->fetchAll(\PDO::FETCH_ASSOC);

        // Salvataggio catalogo_id per query successiva
        $catalogo_id = $articolo["articolo_catalogo_id"];

        // Aggiungi array varianti all'articolo
        $articolo['varianti_articolo'] = $varianti;
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
                SELECT
                    c.id AS colore_id,
                    c.nome_it AS nome_colore_it,
                    c.nome_en AS nome_colore_en,
                    c.tipologia AS tipologia_colore,
                    i.link AS link_img,
                    i.alt_it AS img_alt_it,
                    i.alt_en AS img_alt_en
                FROM colore c
                INNER JOIN immagine i ON i.colore_id = c.id
                WHERE c.catalogo_id = ?
                ORDER BY c.tipologia, c.nome_it
            ");
            $stmt_colori->execute([$catalogo_id]);
            $colori = $stmt_colori->fetchAll(\PDO::FETCH_ASSOC);

            $articolo_colori = [];

            foreach ($colori as $colore) {
                $tipologia = $colore['tipologia_colore'];
                $articolo_colori[$tipologia][] = $colore;
            }

            // Prelievo articoli correlati all'articolo id=n
            $stmt_correlati = $pdo->prepare("
                SELECT a.id articolo_id ,a.nome nome_articolo, a.anteprima_it articolo_anteprima_it, a.anteprima_en articolo_anteprima_en, a.catalogo_id articolo_catalogo_id, i.link as img_link, i.alt_it as img_alt_it, i.alt_en as img_alt_en
                FROM articolo a
                INNER JOIN immagine i ON a.id = i.articolo_id 
                AND i.id = (SELECT MIN(id) FROM immagine WHERE articolo_id = a.id)
                WHERE a.catalogo_id = ?
                AND a.id != ?
                ORDER BY RAND()
                LIMIT 4;
            ");
            $stmt_correlati->execute([$catalogo_id, $id]);
            $articolo_correlati = $stmt_correlati->fetchAll(\PDO::FETCH_ASSOC);

            // Prelievo accessori correlati all'articolo id=n
            $stmt_accessori_correlati = $pdo->prepare("
                SELECT la.id articolo_id, la.nome nome_articolo, la.catalogo_id linea_accessorio_catalogo_id, i.link as img_link, i.alt_it as img_alt_it, i.alt_en as img_alt_en
                FROM linea_accessorio la
                INNER JOIN immagine i ON la.id = i.linea_accessorio_id 
                AND i.id = (SELECT MIN(id) FROM immagine WHERE linea_accessorio_id = la.id)
                ORDER BY RAND()
                LIMIT 4;
            ");
            $stmt_accessori_correlati->execute();
            $accessori_correlati = $stmt_accessori_correlati->fetchAll(\PDO::FETCH_ASSOC);

            // Creazione response
            $data["immagini_articolo"] = $articolo_immagini;
            $data["colori_articolo"] = $articolo_colori;
            $data["correlati_articolo"] = $articolo_correlati;
            $data["correlati_accessori"] = $accessori_correlati;
            jsonResponse($data);
        } else {
            jsonResponse(['error' => 'Articolo non trovato'], 404);
        }
    }
}
