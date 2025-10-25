<?php
// index.php per API puro
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/app/Router.php';
require_once __DIR__ . '/utility/response.php';

$router = new Router();
require_once __DIR__ . '/app/routes.php';

// Dispatch della richiesta corrente
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

// IMPORTANTE: stoppa l'esecuzione qui
exit;