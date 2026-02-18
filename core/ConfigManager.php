<?php

/*
*=================================================     Detalles    =================================================
*
* Clase ConfigManager
*
*=================================================    Descripcion  =================================================
*
* Gestión de archivos de configuración:
* - Verificar existencia
* - Generar archivo de configuración de base de datos
* - Validar integridad
*
*===================================================================================================================
*/

class ConfigManager {

    // Variables
    private $config;
    private $logger;

    /*
    *===========================================================================
    * Constructor
    *
    * @param array  $config  Configuración general
    * @param Logger $logger  Instancia del logger
    */
    public function __construct($config, $logger) {
        $this->config = $config;
        $this->logger = $logger;
    }

    /*
    *===========================================================================
    * Verificar si existe el archivo de configuración
    *
    * @return bool True si existe
    */
    public function exists() {
        $configPath = $this->config['paths']['config_file'];
        return file_exists($configPath);
    }

    /*
    *===========================================================================
    * Generar archivo de configuración de base de datos
    *
    * @param array $dbConfig Configuración de la base de datos
    * @return array ['success' => bool, 'message' => string, 'path' => string]
    */
    public function generateDatabaseConfig($dbConfig) {
        $configPath = $this->config['paths']['config_file'];

        // Verificar que no exista ya
        if (file_exists($configPath)) {
            $this->logger->warning('Intento de sobrescribir archivo de configuración existente', [
                'path' => $configPath
            ]);
            return [
                'success' => false,
                'message' => 'El archivo de configuración ya existe',
                'path'    => $configPath
            ];
        }

        try {
            // Crear contenido del archivo
            $content = $this->buildConfigContent($dbConfig);

            // Crear directorio si no existe
            $dir = dirname($configPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Escribir archivo
            $written = file_put_contents($configPath, $content);

            if ($written === false) {
                throw new Exception('No se pudo escribir el archivo de configuración');
            }

            // Establecer permisos restrictivos
            chmod($configPath, 0640);

            $this->logger->info('Archivo de configuración generado exitosamente', [
                'path' => $configPath,
                'size' => $written
            ]);

            return [
                'success' => true,
                'message' => 'Archivo de configuración creado exitosamente',
                'path'    => $configPath
            ];
        } catch (Exception $e) {
            $this->logger->error('Error al generar archivo de configuración', [
                'path'  => $configPath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear el archivo de configuración: ' . $e->getMessage(),
                'path'    => $configPath
            ];
        }
    }

    /*
    *===========================================================================
    * Construir el contenido del archivo de configuración
    *
    * @param array $dbConfig Configuración de la base de datos
    * @return string Contenido del archivo PHP
    */
    private function buildConfigContent($dbConfig) {
        $timestamp = date('Y-m-d H:i:s');

        $content = <<<PHP
<?php
/*
*===========================================================================
* Configuración de Base de Datos
*
* Este archivo fue generado automáticamente por el instalador
* Fecha de generación: {$timestamp}
*
* IMPORTANTE: Este archivo contiene información sensible.
* Asegúrate de que los permisos sean restrictivos (0640 o similar).
*/

return [
    // Configuración de conexión
    'host'      => '{$dbConfig['host']}',
    'port'      => {$dbConfig['port']},
    'database'  => '{$dbConfig['database']}',
    'username'  => '{$dbConfig['username']}',
    'password'  => '{$dbConfig['password']}',
    'charset'   => '{$dbConfig['charset']}',
    'collation' => '{$dbConfig['collation']}',

    // Opciones de PDO
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],

    // Metadatos de instalación
    'installed_at'      => '{$timestamp}',
    'installer_version' => '{$this->config['app']['version']}',
];

PHP;

        return $content;
    }

    /*
    *===========================================================================
    * Leer configuración de base de datos
    *
    * @return array|null Configuración o null si no existe
    */
    public function readDatabaseConfig() {
        $configPath = $this->config['paths']['config_file'];

        if (!file_exists($configPath)) {
            $this->logger->warning('Archivo de configuración no encontrado', [
                'path' => $configPath
            ]);
            return null;
        }

        try {
            $config = require $configPath;

            $this->logger->info('Configuración de base de datos leída exitosamente');

            return $config;
        } catch (Exception $e) {
            $this->logger->error('Error al leer configuración de base de datos', [
                'path'  => $configPath,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /*
    *===========================================================================
    * Validar integridad del archivo de configuración
    *
    * @return array ['success' => bool, 'message' => string]
    */
    public function validateConfig() {
        $config = $this->readDatabaseConfig();

        if ($config === null) {
            return [
                'success' => false,
                'message' => 'No se pudo leer el archivo de configuración'
            ];
        }

        // Verificar campos requeridos
        $requiredFields = ['host', 'database', 'username', 'password'];

        foreach ($requiredFields as $field) {
            if (!isset($config[$field])) {
                $this->logger->error('Campo requerido faltante en configuración', [
                    'field' => $field
                ]);
                return [
                    'success' => false,
                    'message' => "Campo requerido faltante: $field"
                ];
            }
        }

        $this->logger->info('Configuración validada exitosamente');

        return [
            'success' => true,
            'message' => 'Configuración válida'
        ];
    }

    /*
    *===========================================================================
    * Eliminar archivo de configuración (útil para reinstalación)
    *
    * @return array ['success' => bool, 'message' => string]
    */
    public function deleteConfig() {
        $configPath = $this->config['paths']['config_file'];

        if (!file_exists($configPath)) {
            return [
                'success' => true,
                'message' => 'El archivo de configuración no existe'
            ];
        }

        try {
            $deleted = unlink($configPath);

            if ($deleted) {
                $this->logger->info('Archivo de configuración eliminado', [
                    'path' => $configPath
                ]);
                return [
                    'success' => true,
                    'message' => 'Archivo de configuración eliminado'
                ];
            } else {
                throw new Exception('No se pudo eliminar el archivo');
            }
        } catch (Exception $e) {
            $this->logger->error('Error al eliminar archivo de configuración', [
                'path'  => $configPath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al eliminar el archivo: ' . $e->getMessage()
            ];
        }
    }
}
