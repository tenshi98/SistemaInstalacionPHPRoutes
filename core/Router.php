<?php

/*
*=================================================     Detalles    =================================================
*
* Clase Router
*
*=================================================    Descripcion  =================================================
*
* Maneja el enrutamiento de la aplicación basado en parámetros y métodos HTTP.
*
*===================================================================================================================
*/

class Router {

    // Variables
    private $routes = [];
    private $config;
    private $logger;

    public function __construct($config, $logger) {
        $this->config = $config;
        $this->logger = $logger;
    }

    /*
    *===========================================================================
    * Registrar una ruta GET
    */
    public function get($uri, $file) {
        $this->addRoute('GET', $uri, $file);
    }

    /*
    *===========================================================================
    * Registrar una ruta POST
    */
    public function post($uri, $file) {
        $this->addRoute('POST', $uri, $file);
    }

    /*
    *===========================================================================
    * Añadir ruta al array interno
    */
    private function addRoute($method, $uri, $file) {
        $this->routes[$method][$uri] = $file;
    }

    /*
    *===========================================================================
    * Despachar la petición actual
    */
    public function dispatch() {
        // Obtener URI y limpiar
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Eliminar base path si existe (ajustar según entorno)
        $basePath = '/instalador_routes/';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        // Si URI está vacío o es /, ir a welcome
        if ($uri === '' || $uri === '/') {
            $uri = 'welcome';
        }

        $method = $_SERVER['REQUEST_METHOD'];

        // Verificar si la ruta existe para el método actual
        if (array_key_exists($uri, $this->routes[$method])) {
            $file = $this->routes[$method][$uri];
            $this->loadPage($file);
        } else {
            $this->logger->warning("Ruta no encontrada: [$method] $uri");
            $this->redirect('welcome');
        }
    }

    /*
    *===========================================================================
    * Cargar el archivo de la página
    */
    private function loadPage($file) {
        $filePath = __DIR__ . '/../' . $file;

        if (file_exists($filePath)) {
            // Variables disponibles para las vistas
            $config = $this->config;
            $logger = $this->logger;

            include $filePath;
        } else {
            $this->logger->error("Archivo de vista no encontrado: $filePath");
            die("Error 500: Archivo de vista no encontrado.");
        }
    }

    /*
    *===========================================================================
    * Helpers para redirección
    */
    public function redirect($page) {
        header('Location: ' . $page);
        exit;
    }
}
