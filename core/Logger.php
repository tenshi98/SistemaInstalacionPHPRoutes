<?php

/*
*=================================================     Detalles    =================================================
*
* Clase Logger
*
*=================================================    Descripcion  =================================================
*
* Sistema de logging con tres niveles: info, warning y error.
* Los logs se guardan en archivos separados por fecha.
*
*===================================================================================================================
*/

class Logger {

    // Variables
    private $logDir;
    private $enabled;
    private $filenamePrefix;
    private $dateFormat;

    /*
    *===========================================================================
    * Constructor
    *
    * @param array $config Configuración del logger
    */
    public function __construct($config) {
        $this->logDir          = $config['paths']['log_dir'];
        $this->enabled         = $config['logging']['enabled'];
        $this->filenamePrefix  = $config['logging']['filename_prefix'];
        $this->dateFormat      = $config['logging']['date_format'];

        // Crear directorio de logs si no existe
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /*
    *===========================================================================
    * Registrar mensaje de información
    *
    * @param string $message  Mensaje a registrar
    * @param array  $context  Contexto adicional (opcional)
    */
    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }

    /*
    *===========================================================================
    * Registrar mensaje de advertencia
    *
    * @param string $message  Mensaje a registrar
    * @param array  $context  Contexto adicional (opcional)
    */
    public function warning($message, $context = []) {
        $this->log('WARNING', $message, $context);
    }

    /*
    *===========================================================================
    * Registrar mensaje de error
    *
    * @param string $message  Mensaje a registrar
    * @param array  $context  Contexto adicional (opcional)
    */
    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }

    /*
    *===========================================================================
    * Método privado para escribir en el archivo de log
    *
    * @param string $level    Nivel del log (INFO, WARNING, ERROR)
    * @param string $message  Mensaje a registrar
    * @param array $context Contexto adicional
    */
    private function log($level, $message, $context = []) {
        if (!$this->enabled) {
            return;
        }

        try {
            // Generar nombre del archivo con fecha actual
            $filename = $this->filenamePrefix . '_' . date('Y-m-d') . '.log';
            $filepath = $this->logDir . '/' . $filename;

            // Formatear timestamp
            $timestamp = date($this->dateFormat);

            // Construir línea de log
            $logLine = sprintf(
                "[%s] [%s] %s",
                $timestamp,
                $level,
                $message
            );

            // Añadir contexto si existe
            if (!empty($context)) {
                $logLine .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
            }

            $logLine .= PHP_EOL;

            // Escribir en el archivo
            file_put_contents($filepath, $logLine, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Si falla el logging, no hacer nada para evitar interrumpir la aplicación
            // En producción, esto podría enviarse a un sistema de monitoreo externo
        }
    }

    /*
    *===========================================================================
    * Obtener la ruta del archivo de log actual
    *
    * @return string Ruta del archivo de log
    */
    public function getCurrentLogFile() {
        $filename = $this->filenamePrefix . '_' . date('Y-m-d') . '.log';
        return $this->logDir . '/' . $filename;
    }

    /*
    *===========================================================================
    * Leer el contenido del log actual
    *
    * @param int $lines Número de líneas a leer (0 = todas)
    * @return string Contenido del log
    */
    public function readLog($lines = 0) {
        $filepath = $this->getCurrentLogFile();

        if (!file_exists($filepath)) {
            return '';
        }

        if ($lines === 0) {
            return file_get_contents($filepath);
        }

        // Leer últimas N líneas
        $file = new SplFileObject($filepath, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine  = $file->key();
        $startLine = max(0, $lastLine - $lines);

        $content = '';
        $file->seek($startLine);
        while (!$file->eof()) {
            $content .= $file->current();
            $file->next();
        }

        return $content;
    }
}
