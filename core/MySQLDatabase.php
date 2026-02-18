<?php

/*
*=================================================     Detalles    =================================================
*
* Clase MySQLDatabase
*
*=================================================    Descripcion  =================================================
*
* Implementación específica para MySQL/MariaDB usando PDO.
* Extiende la clase abstracta Database.
*
*===================================================================================================================
*/

require_once __DIR__ . '/Database.php';

class MySQLDatabase extends Database {

    /*
    *===========================================================================
    * Conectar a MySQL
    *
    * @return bool True si la conexión fue exitosa
    * @throws PDOException Si la conexión falla
    */
    public function connect() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%d;charset=%s",
                $this->host,
                $this->port,
                $this->charset
            );

            // Añadir nombre de BD si está especificado
            if (!empty($this->database)) {
                $dsn .= ";dbname=" . $this->database;
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);

            $this->log('info', 'Conexión a MySQL establecida exitosamente', [
                'host'     => $this->host,
                'database' => $this->database
            ]);

            return true;
        } catch (PDOException $e) {
            $this->log('error', 'Error al conectar a MySQL: ' . $e->getMessage(), [
                'host'     => $this->host,
                'username' => $this->username
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Desconectar de MySQL
    */
    public function disconnect() {
        $this->connection = null;
        $this->log('info', 'Conexión a MySQL cerrada');
    }

    /*
    *===========================================================================
    * Ejecutar una consulta SQL
    *
    * @param string $query   Consulta SQL
    * @param array  $params  Parámetros para consulta preparada
    * @return mixed Resultado de la consulta
    * @throws PDOException Si la consulta falla
    */
    public function query($query, $params = []) {
        try {
            if (empty($params)) {
                $result = $this->connection->query($query);
            } else {
                $stmt = $this->connection->prepare($query);
                $stmt->execute($params);
                $result = $stmt;
            }

            return $result;
        } catch (PDOException $e) {
            $this->log('error', 'Error al ejecutar consulta: ' . $e->getMessage(), [
                'query' => $query
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Verificar si la base de datos existe
    *
    * @param string $dbName Nombre de la base de datos
    * @return bool True si existe
    */
    public function databaseExists($dbName) {
        try {
            $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
            $stmt  = $this->connection->prepare($query);
            $stmt->execute([$dbName]);

            $exists = $stmt->fetch() !== false;

            $this->log('info', 'Verificación de existencia de base de datos', [
                'database' => $dbName,
                'exists'   => $exists
            ]);

            return $exists;
        } catch (PDOException $e) {
            $this->log('error', 'Error al verificar existencia de base de datos: ' . $e->getMessage());
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Crear una base de datos
    *
    * @param string $dbName     Nombre de la base de datos
    * @param string $charset    Charset (opcional)
    * @param string $collation  Collation (opcional)
    * @return bool True si se creó exitosamente
    * @throws PDOException Si falla la creación
    */
    public function createDatabase($dbName, $charset = null, $collation = null) {
        try {
            $charset   = $charset ?? $this->charset;
            $collation = $collation ?? 'utf8mb4_unicode_ci';

            $query = sprintf(
                "CREATE DATABASE `%s` CHARACTER SET %s COLLATE %s",
                $dbName,
                $charset,
                $collation
            );

            $this->connection->exec($query);

            $this->log('info', 'Base de datos creada exitosamente', [
                'database'  => $dbName,
                'charset'   => $charset,
                'collation' => $collation
            ]);

            return true;
        } catch (PDOException $e) {
            $this->log('error', 'Error al crear base de datos: ' . $e->getMessage(), [
                'database' => $dbName
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Ejecutar un archivo SQL
    *
    * @param string $filepath Ruta del archivo SQL
    * @return bool True si se ejecutó exitosamente
    * @throws Exception Si el archivo no existe o falla la ejecución
    */
    public function executeFile($filepath) {
        if (!file_exists($filepath)) {
            $this->log('error', 'Archivo SQL no encontrado', ['filepath' => $filepath]);
            throw new Exception("Archivo SQL no encontrado: $filepath");
        }

        try {
            $sql = file_get_contents($filepath);

            // Dividir el archivo en consultas individuales
            // Eliminar comentarios SQL
            $sql = preg_replace('/--.*$/m', '', $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

            // Dividir por punto y coma
            $queries = array_filter(
                array_map('trim', explode(';', $sql)),
                function ($query) {
                    return !empty($query);
                }
            );

            $this->log('info', 'Iniciando ejecución de archivo SQL', [
                'filepath'      => $filepath,
                'queries_count' => count($queries)
            ]);

            // Ejecutar cada consulta
            foreach ($queries as $index => $query) {
                if (!empty(trim($query))) {
                    $this->connection->exec($query);
                }
            }

            $this->log('info', 'Archivo SQL ejecutado exitosamente', [
                'filepath' => $filepath
            ]);

            return true;
        } catch (PDOException $e) {
            $this->log('error', 'Error al ejecutar archivo SQL: ' . $e->getMessage(), [
                'filepath' => $filepath
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Verificar si el usuario tiene permisos para crear bases de datos
    *
    * @return bool True si tiene permisos
    */
    public function hasCreatePermission() {
        try {
            // Intentar obtener los privilegios del usuario actual
            $query  = "SHOW GRANTS FOR CURRENT_USER()";
            $stmt   = $this->connection->query($query);
            $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Buscar privilegio CREATE
            foreach ($grants as $grant) {
                if (
                    stripos($grant, 'ALL PRIVILEGES') !== false ||
                    stripos($grant, 'CREATE') !== false
                ) {
                    $this->log('info', 'Usuario tiene permisos de creación de bases de datos');
                    return true;
                }
            }

            $this->log('warning', 'Usuario no tiene permisos de creación de bases de datos');
            return false;
        } catch (PDOException $e) {
            $this->log('error', 'Error al verificar permisos: ' . $e->getMessage());
            return false;
        }
    }
}
