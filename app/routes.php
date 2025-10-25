<?php
// Rotta di avvio della home
$router->get('/', 'HomeController@index');

// Rotte per catalogo
$router->get('/cataloghi', 'CatalogoController@index');
$router->get('/cataloghi/{id}', 'CatalogoController@show');

// Rotta per recensione
$router->get('/recensioni', 'RecensioniController@index');

// Rotta per FAQ
$router->get('/faq', 'FaqController@index');

// Rotte per articolo
$router->get('/prodotto', 'ProdottoController@index');
$router->get('/prodotto/{id}', 'ProdottoController@show');