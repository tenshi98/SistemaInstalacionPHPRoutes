<?php

/*
*=================================================     Detalles    =================================================
*
* Página de Resumen
*
*=================================================    Descripcion  =================================================
*
* Muestra un resumen de la configuración antes de ejecutar la instalación.
*
*===================================================================================================================
*/

// Verificar que venimos de las páginas anteriores
if (!isset($_SESSION['db_username']) || !isset($_SESSION['db_name'])) {
    header('Location: credentials');
    exit;
}

$validator = new Validator($config, $logger);

// Validar que el archivo SQL existe
$sqlValidation = $validator->validateSqlFile();

$logger->info('Acceso a página de resumen');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['app']['name']; ?> - Resumen</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="installer-container">
        <div class="installer-box">
            <!-- Header -->
            <div class="installer-header">
                <h1 class="title is-3">Resumen de Instalación</h1>
                <p>Paso 3 de 4</p>
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
                <div class="step active">
                    <div class="step-number">3</div>
                    <div class="step-line"></div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                </div>
            </div>

            <!-- Body -->
            <div class="installer-body">
                <div class="content mb-4">
                    <p>Revisa la configuración antes de ejecutar la instalación. Una vez iniciada, se crearán los recursos en el servidor.</p>
                </div>

                <div class="summary-item">
                    <strong><i class="fas fa-server"></i> Host de MySQL</strong>
                    <span><?php echo htmlspecialchars($_SESSION['db_host']); ?></span>
                </div>

                <div class="summary-item">
                    <strong><i class="fas fa-user"></i> Usuario</strong>
                    <span><?php echo htmlspecialchars($_SESSION['db_username']); ?></span>
                </div>

                <div class="summary-item">
                    <strong><i class="fas fa-database"></i> Base de Datos</strong>
                    <span><?php echo htmlspecialchars($_SESSION['db_name']); ?></span>
                </div>

                <div class="summary-item">
                    <strong><i class="fas fa-file-code"></i> Archivo SQL</strong>
                    <span><?php echo htmlspecialchars(basename($config['paths']['sql_file'])); ?></span>
                </div>

                <?php if (!$sqlValidation['success']){ ?>
                    <div class="notification is-danger mt-4">
                        <button class="delete"></button>
                        <strong><i class="fas fa-exclamation-triangle"></i> Advertencia</strong>
                        <p><?php echo htmlspecialchars($sqlValidation['message']); ?></p>
                        <p class="mt-2">Ruta esperada: <code><?php echo htmlspecialchars($sqlValidation['path']); ?></code></p>
                    </div>
                <?php } ?>

                <div class="box has-background-warning-light mt-4">
                    <p class="has-text-warning-dark">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Importante:</strong> Al ejecutar la instalación se realizarán las siguientes acciones:
                    </p>
                    <ul class="mt-2">
                        <li>Se creará la base de datos <strong><?php echo htmlspecialchars($_SESSION['db_name']); ?></strong></li>
                        <li>Se ejecutará el archivo SQL de instalación</li>
                        <li>Se generará el archivo de configuración</li>
                    </ul>
                </div>

                <div class="installer-footer" style="margin: 0 -2rem -2rem -2rem;">
                    <a href="database" class="button is-light">
                        <span class="icon">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span>Atrás</span>
                    </a>

                    <form method="POST" action="finish" style="margin: 0;">
                        <button
                            type="submit"
                            id="execute-install"
                            class="button is-success is-medium"
                            <?php echo !$sqlValidation['success'] ? 'disabled' : ''; ?>>
                            <span class="icon">
                                <i class="fas fa-rocket"></i>
                            </span>
                            <span>Ejecutar Instalación</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/installer.js"></script>
</body>

</html>
