<?php

/*
*=================================================     Detalles    =================================================
*
* Clase Validator
*
*=================================================    Descripcion  =================================================
*
* Validaciones del sistema de instalación:
* - Verificar existencia de archivo de configuración
* - Validar credenciales MySQL
* - Verificar permisos de usuario
* - Validar nombres de base de datos
*
*===================================================================================================================
*/

require_once __DIR__ . '/MySQLDatabase.php';

class Validator {

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
    * Verificar si ya existe un archivo de configuración
    *
    * @return bool True si existe
    */
    public function configFileExists() {
        $configPath = $this->config['paths']['config_file'];
        $exists = file_exists($configPath);

        if ($exists) {
            $this->logger->warning('Archivo de configuración ya existe', [
                'path' => $configPath
            ]);
        }

        return $exists;
    }

    /*
    *===========================================================================
    * Validar credenciales de MySQL
    *
    * @param string $host      Host de MySQL
    * @param string $username  Usuario
    * @param string $password  Contraseña
    * @return array ['success' => bool, 'message' => string, 'db' => MySQLDatabase|null]
    */
    public function validateCredentials($host, $username, $password) {
        try {
            $dbConfig = [
                'host'     => $host,
                'port'     => $this->config['database']['default_port'],
                'username' => $username,
                'password' => $password,
                'charset'  => $this->config['database']['charset']
            ];

            $db = new MySQLDatabase($dbConfig, $this->logger);
            $db->connect();

            $this->logger->info('Credenciales MySQL validadas exitosamente', [
                'host'     => $host,
                'username' => $username
            ]);

            return [
                'success' => true,
                'message' => 'Credenciales válidas',
                'db'      => $db
            ];
        } catch (PDOException $e) {
            $this->logger->error('Error al validar credenciales MySQL', [
                'host'     => $host,
                'username' => $username,
                'error'    => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Credenciales inválidas: ' . $e->getMessage(),
                'db'      => null
            ];
        }
    }

    /*
    *===========================================================================
    * Verificar si el usuario tiene permisos para crear bases de datos
    *
    * @param MySQLDatabase $db Instancia de la base de datos conectada
    * @return array ['success' => bool, 'message' => string]
    */
    public function validateCreatePermission($db) {
        try {
            $hasPermission = $db->hasCreatePermission();

            if ($hasPermission) {
                $this->logger->info('Usuario tiene permisos de creación de bases de datos');
                return [
                    'success' => true,
                    'message' => 'El usuario tiene permisos para crear bases de datos'
                ];
            } else {
                $this->logger->warning('Usuario no tiene permisos de creación de bases de datos');
                return [
                    'success' => false,
                    'message' => 'El usuario no tiene permisos para crear bases de datos'
                ];
            }
        } catch (Exception $e) {
            $this->logger->error('Error al verificar permisos', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al verificar permisos: ' . $e->getMessage()
            ];
        }
    }

    /*
    *===========================================================================
    * Validar nombre de base de datos
    *
    * @param string $dbName Nombre de la base de datos
    * @return array ['success' => bool, 'message' => string]
    */
    public function validateDatabaseName($dbName) {
        // Verificar que no esté vacío
        if (empty(trim($dbName))) {
            $this->logger->warning('Nombre de base de datos vacío');
            return [
                'success' => false,
                'message' => 'El nombre de la base de datos no puede estar vacío'
            ];
        }

        // Verificar longitud mínima
        $minLength = $this->config['validation']['db_name_min_length'];
        if (strlen($dbName) < $minLength) {
            $this->logger->warning('Nombre de base de datos demasiado corto', [
                'name'       => $dbName,
                'length'     => strlen($dbName),
                'min_length' => $minLength
            ]);
            return [
                'success' => false,
                'message' => "El nombre debe tener al menos $minLength caracteres"
            ];
        }

        // Verificar longitud máxima
        $maxLength = $this->config['validation']['db_name_max_length'];
        if (strlen($dbName) > $maxLength) {
            $this->logger->warning('Nombre de base de datos demasiado largo', [
                'name'       => $dbName,
                'length'     => strlen($dbName),
                'max_length' => $maxLength
            ]);
            return [
                'success' => false,
                'message' => "El nombre no puede superar los $maxLength caracteres"
            ];
        }

        // Verificar patrón (solo letras, números y guiones bajos)
        $pattern = $this->config['validation']['db_name_pattern'];
        if (!preg_match($pattern, $dbName)) {
            $this->logger->warning('Nombre de base de datos con caracteres inválidos', [
                'name' => $dbName
            ]);
            return [
                'success' => false,
                'message' => 'El nombre solo puede contener letras, números y guiones bajos'
            ];
        }

        $this->logger->info('Nombre de base de datos válido', ['name' => $dbName]);
        return [
            'success' => true,
            'message' => 'Nombre válido'
        ];
    }

    /*
    *===========================================================================
    * Verificar si la base de datos ya existe
    *
    * @param MySQLDatabase $db      Instancia de la base de datos conectada
    * @param string        $dbName  Nombre de la base de datos
    * @return array ['success' => bool, 'message' => string, 'exists' => bool]
    */
    public function checkDatabaseExists($db, $dbName) {
        try {
            $exists = $db->databaseExists($dbName);

            if ($exists) {
                $this->logger->warning('La base de datos ya existe', ['name' => $dbName]);
                return [
                    'success' => false,
                    'message' => 'La base de datos ya existe',
                    'exists'  => true
                ];
            } else {
                $this->logger->info('La base de datos no existe, se puede crear', ['name' => $dbName]);
                return [
                    'success' => true,
                    'message' => 'La base de datos no existe, se puede crear',
                    'exists'  => false
                ];
            }
        } catch (Exception $e) {
            $this->logger->error('Error al verificar existencia de base de datos', [
                'name'  => $dbName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al verificar la base de datos: ' . $e->getMessage(),
                'exists'  => null
            ];
        }
    }

    /*
    *===========================================================================
    * Verificar que el archivo SQL de instalación existe
    *
    * @return array ['success' => bool, 'message' => string, 'path' => string]
    */
    public function validateSqlFile() {
        $sqlPath = $this->config['paths']['sql_file'];

        if (!file_exists($sqlPath)) {
            $this->logger->error('Archivo SQL de instalación no encontrado', [
                'path' => $sqlPath
            ]);
            return [
                'success' => false,
                'message' => 'Archivo SQL de instalación no encontrado',
                'path'    => $sqlPath
            ];
        }

        if (!is_readable($sqlPath)) {
            $this->logger->error('Archivo SQL de instalación no es legible', [
                'path' => $sqlPath
            ]);
            return [
                'success' => false,
                'message' => 'Archivo SQL de instalación no es legible',
                'path'    => $sqlPath
            ];
        }

        $this->logger->info('Archivo SQL de instalación válido', ['path' => $sqlPath]);
        return [
            'success' => true,
            'message' => 'Archivo SQL válido',
            'path'    => $sqlPath
        ];
    }
}
