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

// Rotte per accessori
$router->get('/accessori', 'AccessorioController@index');
$router->get('/accessori/{id}', 'AccessorioController@show');

// Rotte per accessorio
$router->get('/accessorio/{id}', 'AccessorioController@show_accessorio');

// Rotta per il form di contatto
$router->post('/contatto', 'ContattoController@send');