<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utility/response.php';

class AccessorioController
{
    public function index()
    {
        global $pdo;

        // Prelievo accessori
        $stmt = $pdo->query("
            SELECT c.id id_catalogo, c.nome nome_catalogo, c.anteprima_it anteprima_catalogo_it, c.anteprima_en anteprima_catalogo_en, i.slug img_slug, i.link img_link, i.alt_it img_alt_it, i.alt_en img_alt_en
            FROM vica_official_db.catalogo as c
            INNER JOIN immagine as i on c.id = i.catalogo_id
            WHERE tipologia_catalogo_id = 2
        ");
        $accessori = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Creazione response
        $data["accessori"] = $accessori;
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

            // Prelievo accessori legati al catalogo
            $stmt = $pdo->prepare("
                    SELECT lin_acc.*, i.id as img_id, i.link, i.alt_it, i.alt_en 
                    FROM linea_accessorio AS lin_acc
                    INNER JOIN immagine i ON lin_acc.id = i.linea_accessorio_id 
                    AND i.id = (SELECT MIN(id) FROM immagine WHERE linea_accessorio_id = lin_acc.id)
                    WHERE lin_acc.catalogo_id = ?
                ");
            $stmt->execute([$id]);
            $accessori_collegati = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // var_dump($accessori_collegati);

            // Prelievo accessori in evidenza legati al catalogo
            $stmt_2 = $pdo->prepare("
                    SELECT lin_acc.*, i.id as img_id, i.link, i.alt_it, i.alt_en 
                    FROM linea_accessorio AS lin_acc
                    INNER JOIN immagine i ON lin_acc.id = i.linea_accessorio_id 
                        AND i.id = (SELECT MIN(id) FROM immagine WHERE linea_accessorio_id = lin_acc.id)
                    WHERE lin_acc.catalogo_id = ?
                    AND lin_acc.preferito = 1
                ");
            $stmt_2->execute([$id]);
            $accessori_preferiti = $stmt_2->fetchAll(\PDO::FETCH_ASSOC);

            // var_dump($accessori_preferiti);

            // Creazione response
            $catalogo['accessori_preferiti'] = $accessori_preferiti;
            $catalogo['accessori_collegati'] = $accessori_collegati;

            jsonResponse($catalogo);
        } else {
            jsonResponse(['error' => 'Accessorio non trovato'], 404);
        }
    }

    public function show_accessorio($id)
    {
        global $pdo;

        // Prelievo catalogo singolo
        $stmt = $pdo->prepare("
            SELECT c.id id_linea, c.nome nome_linea, c.descrizione_it descrizione_linea_it, c.descrizione_en descrizione_linea_en, i.slug img_slug, i.link img_link, i.alt_it img_alt_it, i.alt_en img_alt_en
            FROM vica_official_db.linea_accessorio as c
            INNER JOIN immagine as i on c.id = i.linea_accessorio_id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $catalogo = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($catalogo) {
            $catalogo_id = 4;

            $stmt_immagini = $pdo->prepare("
                SELECT 	i.linea_accessorio_id immagine_articolo_id, i.id immagine_id, i.slug immagine_slug, i.link immagine_link, i.alt_it immagine_alt_it, i.alt_en immagine_alt_en, i.tipologia_immagine_id
                FROM immagine i
                WHERE i.linea_accessorio_id = ?;
            ");
            $stmt_immagini->execute([$id]);
            $accessorio_immagini = $stmt_immagini->fetchAll(\PDO::FETCH_ASSOC);

            // Prelievo accessori correlati all'articolo id=n
            $stmt_correlati = $pdo->prepare("
                SELECT a.id articolo_id ,a.nome nome_articolo, a.catalogo_id articolo_catalogo_id, i.link as img_link, i.alt_it as img_alt_it, i.alt_en as img_alt_en
                FROM linea_accessorio a
                INNER JOIN immagine i ON a.id = i.linea_accessorio_id
                AND i.id = (SELECT MIN(id) FROM immagine WHERE linea_accessorio_id = a.id)
                WHERE a.catalogo_id = ?
                AND a.id != ?
                ORDER BY RAND()
                LIMIT 4;
            ");
            $stmt_correlati->execute([$catalogo_id, $id]);
            $accessorio_correlati = $stmt_correlati->fetchAll(\PDO::FETCH_ASSOC);

            // Prelievo articoli correlati all'articolo id=n
            $stmt_correlati = $pdo->prepare("
                SELECT a.id articolo_id ,a.nome nome_articolo, a.anteprima_it articolo_anteprima_it, a.anteprima_en articolo_anteprima_en, a.catalogo_id articolo_catalogo_id, i.link as img_link, i.alt_it as img_alt_it, i.alt_en as img_alt_en
                FROM articolo a
                INNER JOIN immagine i ON a.id = i.articolo_id 
                AND i.id = (SELECT MIN(id) FROM immagine WHERE articolo_id = a.id)
                ORDER BY RAND()
                LIMIT 4;
            ");
            $stmt_correlati->execute();
            $articolo_correlati = $stmt_correlati->fetchAll(\PDO::FETCH_ASSOC);

            $data["accessorio"] = $catalogo;
            $data["immagini_articolo"] = $accessorio_immagini;
            $data["correlati_accessorio"] = $accessorio_correlati;
            $data["correlati_articolo"] = $articolo_correlati;
            jsonResponse($data);
        } else {
            jsonResponse(['error' => 'Accessorio non trovato'], 404);
        }
    }
}
