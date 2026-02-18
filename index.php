<?php

/*
* Punto de Entrada del Instalador
*
* Este archivo maneja el enrutamiento básico entre las diferentes páginas del asistente.
*/

// Iniciar sesión
session_start();

// Configurar zona horaria
date_default_timezone_set('America/Santiago');

// Cargar configuración
$config = require __DIR__ . '/config/settings.php';

// Cargar clases core
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/MySQLDatabase.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/ConfigManager.php';
require_once __DIR__ . '/core/Router.php';

// Inicializar logger
$logger = new Logger($config);

// Inicializar router
$router = new Router($config, $logger);

// Definir rutas
// Welcome
$router->get('welcome', 'pages/welcome.php');
$router->post('welcome', 'pages/welcome.php');

// Credentials
$router->get('credentials', 'pages/credentials.php');
$router->post('credentials', 'pages/credentials.php');

// Database
$router->get('database', 'pages/database.php');
$router->post('database', 'pages/database.php');

// Summary
$router->get('summary', 'pages/summary.php');
$router->post('summary', 'pages/summary.php');

// Finish
$router->get('finish', 'pages/finish.php');
$router->post('finish', 'pages/finish.php');

// Despachar
$router->dispatch();
