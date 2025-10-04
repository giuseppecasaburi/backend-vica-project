<?php
function caricaEnv($path) {
    if (!file_exists($path)) {
        throw new Exception(".env non trovato al percorso $path");
    }

    // file() restituisce un'array di tutte le righe trovate
    // In questo modo le prende ignorando i \n e le righe vuote
    $righe = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach($righe as $riga) {
        // Questo if evita le righe commentate
        if(str_starts_with(trim($riga), '#')) continue;

        // Divide la riga in chiave e valore e si assicura di non dividere in più pezzi
        list($nome, $valore) = explode('=', $riga, 2);

        // putenv() aggiunge una variabile d'ambiente a quelle di PHP rendendola accessibile con getenv(NOME_VARIBILE)
        putenv(trim($nome) . '=' . trim($valore));
    }
}