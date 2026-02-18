<?php

/*
*=================================================     Detalles    =================================================
*
* Página de Finalización
*
*=================================================    Descripcion  =================================================
*
* Ejecuta la instalación:
* - Crea la base de datos
* - Guarda la configuración
* - Ejecuta el archivo SQL
* - Muestra el resultado
*
*===================================================================================================================
*/

// Verificar que venimos de las páginas anteriores
if (!isset($_SESSION['db_username']) || !isset($_SESSION['db_name'])) {
    header('Location: credentials');
    exit;
}

$configManager = new ConfigManager($config, $logger);
$success       = false;
$errors        = [];
$warnings      = [];
$info          = [];

// Solo ejecutar si es POST (viene del resumen)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logger->info('Iniciando proceso de instalación');

    try {
        // Paso 1: Conectar a MySQL
        $info[]   = 'Conectando a MySQL...';
        $dbConfig = [
            'host'     => $_SESSION['db_host'],
            'port'     => $config['database']['default_port'],
            'username' => $_SESSION['db_username'],
            'password' => $_SESSION['db_password'],
            'charset'  => $config['database']['charset']
        ];

        $db = new MySQLDatabase($dbConfig, $logger);
        $db->connect();
        $info[] = 'Conexión establecida exitosamente';

        // Paso 2: Crear base de datos
        $info[] = 'Creando base de datos...';
        $db->createDatabase(
            $_SESSION['db_name'],
            $config['database']['charset'],
            $config['database']['collation']
        );
        $info[] = 'Base de datos creada: ' . $_SESSION['db_name'];

        // Paso 3: Guardar configuración
        $info[]     = 'Generando archivo de configuración...';
        $configData = [
            'host'      => $_SESSION['db_host'],
            'port'      => $config['database']['default_port'],
            'database'  => $_SESSION['db_name'],
            'username'  => $_SESSION['db_username'],
            'password'  => $_SESSION['db_password'],
            'charset'   => $config['database']['charset'],
            'collation' => $config['database']['collation']
        ];

        $configResult = $configManager->generateDatabaseConfig($configData);

        if ($configResult['success']) {
            $info[]     = 'Archivo de configuración creado exitosamente';
        } else {
            $warnings[] = 'Advertencia al crear configuración: ' . $configResult['message'];
        }

        // Paso 4: Conectar a la base de datos recién creada
        $info[] = 'Conectando a la nueva base de datos...';
        $db->disconnect();
        $dbConfig['database'] = $_SESSION['db_name'];
        $db                   = new MySQLDatabase($dbConfig, $logger);
        $db->connect();

        // Paso 5: Ejecutar archivo SQL
        $sqlFile = $config['paths']['sql_file'];
        if (file_exists($sqlFile)) {
            $info[] = 'Ejecutando archivo SQL...';
            $db->executeFile($sqlFile);
            $info[] = 'Archivo SQL ejecutado exitosamente';
        } else {
            $warnings[] = 'Archivo SQL no encontrado, se omitió la ejecución';
        }

        $db->disconnect();

        $success = true;
        $logger->info('Instalación completada exitosamente');

        // Limpiar sesión
        $_SESSION = [];
    } catch (Exception $e) {
        $errors[] = 'Error durante la instalación: ' . $e->getMessage();
        $logger->error('Error durante la instalación', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
} else {
    // Si no es POST, redirigir al resumen
    header('Location: summary');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['app']['name']; ?> - Finalización</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="installer-container">
        <div class="installer-box">
            <!-- Header -->
            <div class="installer-header">
                <h1 class="title is-3">
                    <?php echo $success ? 'Instalación Completada' : 'Error en la Instalación'; ?>
                </h1>
                <p>Paso 4 de 4</p>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator mt-4">
                <div class="step completed">
                    <div class="step-number">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="step-line"></div>
                </div>
                <div class="step completed">
                    <div class="step-number">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="step-line"></div>
                </div>
                <div class="step completed">
                    <div class="step-number">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="step-line"></div>
                </div>
                <div class="step <?php echo $success ? 'completed' : 'active'; ?>">
                    <div class="step-number">
                        <?php echo $success ? '<i class="fas fa-check"></i>' : '4'; ?>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="installer-body">
                <div class="has-text-centered">
                    <?php if ($success){ ?>
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>

                        <h2 class="title is-4 has-text-success">¡Instalación Exitosa!</h2>
                        <p class="mb-4">El sistema se ha instalado correctamente.</p>
                    <?php }else{ ?>
                        <div class="error-icon">
                            <i class="fas fa-times"></i>
                        </div>

                        <h2 class="title is-4 has-text-danger">Error en la Instalación</h2>
                        <p class="mb-4">Se encontraron errores durante el proceso.</p>
                    <?php } ?>
                </div>

                <?php if (!empty($errors)){ ?>
                    <div class="notification is-danger">
                        <strong><i class="fas fa-times-circle"></i> Errores:</strong>
                        <ul class="mt-2">
                            <?php foreach ($errors as $error){ ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <?php if (!empty($warnings)){ ?>
                    <div class="notification is-warning">
                        <strong><i class="fas fa-exclamation-triangle"></i> Advertencias:</strong>
                        <ul class="mt-2">
                            <?php foreach ($warnings as $warning){ ?>
                                <li><?php echo htmlspecialchars($warning); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <?php if (!empty($info)){ ?>
                    <div class="box">
                        <h3 class="subtitle is-5 mb-3">
                            <i class="fas fa-list"></i> Proceso de Instalación:
                        </h3>
                        <ul>
                            <?php foreach ($info as $item){ ?>
                                <li>
                                    <i class="fas fa-check-circle has-text-success"></i>
                                    <?php echo htmlspecialchars($item); ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <?php if ($success){ ?>
                    <div class="box has-background-info-light">
                        <p class="has-text-info-dark">
                            <i class="fas fa-info-circle"></i>
                            <strong>Próximos pasos:</strong>
                        </p>
                        <ul class="mt-2">
                            <li>El archivo de configuración se encuentra en: <code><?php echo htmlspecialchars($config['paths']['config_file']); ?></code></li>
                            <li>Asegúrate de proteger este archivo con permisos restrictivos</li>
                            <li>Puedes comenzar a usar el sistema</li>
                        </ul>
                    </div>
                <?php } ?>
            </div>

            <!-- Footer -->
            <div class="installer-footer">
                <?php if (!$success){ ?>
                    <a href="welcome" class="button is-light">
                        <span class="icon">
                            <i class="fas fa-redo"></i>
                        </span>
                        <span>Reintentar</span>
                    </a>
                <?php }else{ ?>
                    <div></div>
                <?php } ?>

                <a href="<?php echo $config['urls']['return_finish']; ?>" class="button is-primary is-medium">
                    <span>Ir a la Aplicación</span>
                    <span class="icon">
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>
            </div>
        </div>
    </div>

    <script src="assets/js/installer.js"></script>
</body>

</html>
