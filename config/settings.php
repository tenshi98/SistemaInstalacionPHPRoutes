<?php
/*
*=================================================     Detalles    =================================================
*
* Configuración General del Instalador
*
*=================================================    Descripcion  =================================================
*
* Este archivo contiene todas las configuraciones generales del sistema de instalación.
* Modifica estos valores según tus necesidades antes de ejecutar el instalador.
*
*===================================================================================================================
*/

return [
    // Configuración de rutas
    'paths' => [
        'config_file' => __DIR__ . '/database.php',       // Ruta donde se guardará el archivo de configuración de la base de datos
        'sql_file'    => __DIR__ . '/../sql/install.sql', // Ruta del archivo SQL que se ejecutará durante la instalación
        'log_dir'     => __DIR__ . '/../logs',            // Ruta donde se guardarán los logs
    ],

    // URLs de navegación
    'urls' => [
        'return_welcome' => '/', // URL a la que redirigir cuando se presiona "Volver" en la página de bienvenida
        'return_finish'  => '/', // URL a la que redirigir cuando se completa la instalación exitosamente
    ],

    // Configuración de logging
    'logging' => [
        'enabled'         => true,          // Habilitar o deshabilitar el sistema de logs
        'filename_prefix' => 'installer',   // Nombre del archivo de log (se añadirá la fecha automáticamente)
        'date_format'     => 'Y-m-d H:i:s', // Formato de fecha para los logs
    ],

    // Configuración de base de datos
    'database' => [
        'default_host' => 'localhost',          // Host por defecto (puede ser modificado durante la instalación)
        'default_port' => 3306,                 // Puerto por defecto
        'charset'      => 'utf8mb4',            // Charset por defecto
        'collation'    => 'utf8mb4_unicode_ci', // Collation por defecto
    ],

    // Configuración de validación
    'validation' => [
        'db_name_pattern'    => '/^[a-zA-Z0-9_]+$/', // Patrón regex para validar nombres de base de datos, Solo permite letras, números y guiones bajos
        'db_name_min_length' => 3,                   // Longitud mínima del nombre de la base de datos
        'db_name_max_length' => 64,                  // Longitud máxima del nombre de la base de datos
    ],

    // Configuración de la aplicación
    'app' => [
        'name'     => 'Sistema de Instalación', // Nombre de la aplicación
        'version'  => '1.0.0',                  // Versión del instalador
        'timezone' => 'America/Santiago',       // Zona horaria
    ],
];
