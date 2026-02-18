<?php

/*
*=================================================     Detalles    =================================================
*
* Página de Bienvenida
*
*=================================================    Descripcion  =================================================
*
* Primera página del asistente de instalación.
* Muestra mensaje de bienvenida y verifica si ya existe una instalación.
*
*===================================================================================================================
*/

// Inicializar validador y config manager
$validator     = new Validator($config, $logger);
$configManager = new ConfigManager($config, $logger);

// Variables para la vista
$alreadyInstalled = $configManager->exists();
$error            = null;

// Si se presiona el botón de continuar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_install'])) {
    if ($alreadyInstalled) {
        $error = 'El sistema ya está instalado. Si deseas reinstalar, elimina el archivo de configuración primero.';
        $logger->warning('Intento de instalación con configuración existente');
    } else {
        // Limpiar sesión y redirigir a credenciales
        $_SESSION = [];
        header('Location: credentials');
        exit;
    }
}

$logger->info('Acceso a página de bienvenida');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['app']['name']; ?> - Bienvenida</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="installer-container">
        <div class="installer-box">
            <!-- Header -->
            <div class="installer-header">
                <h1 class="title is-2">
                    <i class="fas fa-rocket"></i>
                    <?php echo $config['app']['name']; ?>
                </h1>
                <p>Versión <?php echo $config['app']['version']; ?></p>
            </div>

            <!-- Body -->
            <div class="installer-body">
                <div class="content has-text-centered">
                    <div class="mb-5">
                        <i class="fas fa-database" style="font-size: 4rem; color: #667eea;"></i>
                    </div>

                    <h2 class="title is-4">¡Bienvenido al Asistente de Instalación!</h2>

                    <p class="mb-4">
                        Este asistente te guiará paso a paso en el proceso de instalación del sistema.
                        Se configurará la conexión a la base de datos y se ejecutarán los scripts necesarios.
                    </p>

                    <?php if ($alreadyInstalled){ ?>
                        <div class="notification is-warning">
                            <button class="delete"></button>
                            <strong><i class="fas fa-exclamation-triangle"></i> Sistema ya instalado</strong>
                            <p>El sistema ya ha sido instalado previamente. Si deseas reinstalar, elimina el archivo de configuración ubicado en:</p>
                            <code><?php echo $config['paths']['config_file']; ?></code>
                        </div>
                    <?php } ?>

                    <?php if ($error){ ?>
                        <div class="notification is-danger">
                            <button class="delete"></button>
                            <strong><i class="fas fa-times-circle"></i> Error</strong>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    <?php } ?>

                    <div class="box has-text-left mt-5">
                        <h3 class="subtitle is-5 mb-3">
                            <i class="fas fa-list-check"></i> El proceso incluye:
                        </h3>
                        <ul>
                            <li><i class="fas fa-check has-text-success"></i> Validación de credenciales MySQL</li>
                            <li><i class="fas fa-check has-text-success"></i> Verificación de permisos</li>
                            <li><i class="fas fa-check has-text-success"></i> Creación de base de datos</li>
                            <li><i class="fas fa-check has-text-success"></i> Ejecución de scripts SQL</li>
                            <li><i class="fas fa-check has-text-success"></i> Generación de archivo de configuración</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="installer-footer">
                <a href="<?php echo $config['urls']['return_welcome']; ?>" class="button is-light">
                    <span class="icon">
                        <i class="fas fa-arrow-left"></i>
                    </span>
                    <span>Volver</span>
                </a>

                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="start_install" value="1">
                    <button type="submit" class="button is-primary is-medium" <?php echo $alreadyInstalled ? 'disabled' : ''; ?>>
                        <span class="icon">
                            <i class="fas fa-play"></i>
                        </span>
                        <span>Iniciar Instalación</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/installer.js"></script>
</body>

</html>
