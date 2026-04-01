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
            WHERE tipologia_catalogo_id = 1
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

            // Verifica se ci sono collezioni
            $stmt = $pdo->prepare("
                SELECT c.id id_catalogo, c.nome nome_catalogo, c.descrizione_it descrizione_catalogo_it, c.descrizione_en descrizione_catalogo_en, i.slug img_slug, i.link img_link, i.alt_it img_alt_it, i.alt_en img_alt_en
                FROM vica_official_db.catalogo as c
                LEFT JOIN immagine as i on c.id = i.catalogo_id
                WHERE c.id_parente = ?
            ");
            $stmt->execute([$id]);
            $collezioni = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($collezioni)) {
                // Prelievo articoli legati al catalogo
                $stmt = $pdo->prepare("
                    SELECT a.*, i.id as img_id, i.link, i.alt_it, i.alt_en 
                    FROM articolo a
                    INNER JOIN immagine i ON a.id = i.articolo_id 
                        AND i.id = (SELECT MIN(id) FROM immagine WHERE articolo_id = a.id)
                    WHERE a.catalogo_id = ?
                ");
                $stmt->execute([$id]);
                $articoli_collegati = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                // Prelievo articoli in evidenza legati al catalogo
                $stmt_2 = $pdo->prepare("
                    SELECT a.*, i.id as img_id, i.link, i.alt_it, i.alt_en 
                    FROM articolo a
                    INNER JOIN immagine i ON a.id = i.articolo_id 
                        AND i.id = (SELECT MIN(id) FROM immagine WHERE articolo_id = a.id)
                    WHERE a.catalogo_id = ?
                    AND preferito = 1
                ");
                $stmt_2->execute([$id]);
                $articoli_preferiti = $stmt_2->fetchAll(\PDO::FETCH_ASSOC);

                // Creazione response
                $catalogo['articoli_preferiti'] = $articoli_preferiti;
                $catalogo['articoli_collegati'] = $articoli_collegati;
            } else {
                $articoli_preferiti = [];

                foreach ($collezioni as &$collezione) {
                    $stmt_2 = $pdo->prepare("
                        SELECT a.*, i.id as img_id, i.link, i.alt_it, i.alt_en 
                        FROM articolo a
                        INNER JOIN immagine i ON a.id = i.articolo_id 
                            AND i.id = (SELECT MIN(id) FROM immagine WHERE articolo_id = a.id)
                        WHERE a.catalogo_id = ?
                        AND preferito = 1
                        LIMIT 1
                    ");
                    $stmt_2->execute([$collezione["id_catalogo"]]);
                    $preferito = $stmt_2->fetch(\PDO::FETCH_ASSOC);

                    if ($preferito) {
                        $articoli_preferiti[] = $preferito;
                    }
                }
                unset($collezione);

                $catalogo['collezioni'] = $collezioni;
                $catalogo['articoli_preferiti'] = $articoli_preferiti;
                $catalogo['has_collections'] = 1;
            }

            jsonResponse($catalogo);
        } else {
            jsonResponse(['error' => 'Catalogo non trovato'], 404);
        }
    }
}
