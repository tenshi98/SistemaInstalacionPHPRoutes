<?php

/*
*=================================================     Detalles    =================================================
*
* Clase Database
*
*=================================================    Descripcion  =================================================
*
* Clase abstracta para gestionar conexiones a bases de datos.
* Facilita la migración a otros motores de BD (PostgreSQL, SQLite, etc.)
* mediante la implementación de adaptadores específicos.
*
*===================================================================================================================
*/

abstract class Database {

    // Variables
    protected $connection;
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $database;
    protected $charset;
    protected $logger;

    /*
    *===========================================================================
    * Constructor
    *
    * @param array  $config  Configuración de conexión
    * @param Logger $logger  Instancia del logger
    */
    public function __construct($config, $logger = null) {
        $this->host     = $config['host'] ?? 'localhost';
        $this->port     = $config['port'] ?? 3306;
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->database = $config['database'] ?? '';
        $this->charset  = $config['charset'] ?? 'utf8mb4';
        $this->logger   = $logger;
    }

    /*
    *===========================================================================
    * Conectar a la base de datos
    * Método abstracto que debe ser implementado por cada adaptador
    *
    * @return bool True si la conexión fue exitosa
    * @throws Exception Si la conexión falla
    */
    abstract public function connect();

    /*
    *===========================================================================
    * Desconectar de la base de datos
    */
    abstract public function disconnect();

    /*
    *===========================================================================
    * Ejecutar una consulta SQL
    *
    * @param string $query   Consulta SQL
    * @param array  $params  Parámetros para consulta preparada
    * @return mixed Resultado de la consulta
    */
    abstract public function query($query, $params = []);

    /*
    *===========================================================================
    * Verificar si la base de datos existe
    *
    * @param string $dbName Nombre de la base de datos
    * @return bool True si existe
    */
    abstract public function databaseExists($dbName);

    /*
    *===========================================================================
    * Crear una base de datos
    *
    * @param string $dbName     Nombre de la base de datos
    * @param string $charset    Charset (opcional)
    * @param string $collation  Collation (opcional)
    * @return bool True si se creó exitosamente
    */
    abstract public function createDatabase($dbName, $charset = null, $collation = null);

    /*
    *===========================================================================
    * Ejecutar un archivo SQL
    *
    * @param string $filepath Ruta del archivo SQL
    * @return bool True si se ejecutó exitosamente
    */
    abstract public function executeFile($filepath);

    /*
    *===========================================================================
    * Obtener la conexión actual
    *
    * @return mixed Objeto de conexión
    */
    public function getConnection() {
        return $this->connection;
    }

    /*
    *===========================================================================
    * Verificar si está conectado
    *
    * @return bool True si hay conexión activa
    */
    public function isConnected() {
        return $this->connection !== null;
    }

    /*
    *===========================================================================
    * Registrar log si el logger está disponible
    *
    * @param string $level    Nivel del log (info, warning, error)
    * @param string $message  Mensaje
    * @param array $context Contexto adicional
    */
    protected function log($level, $message, $context = []) {
        if ($this->logger) {
            $this->logger->$level($message, $context);
        }
    }
}
