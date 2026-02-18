<?php

/*
*=================================================     Detalles    =================================================
*
* Página de Configuración de Base de Datos
*
*=================================================    Descripcion  =================================================
*
* Solicita el nombre de la base de datos a crear.
* Verifica que no exista y que se pueda crear.
*
*===================================================================================================================
*/

// Verificar que venimos de la página anterior
if (!isset($_SESSION['db_username'])) {
    header('Location: credentials');
    exit;
}

$validator = new Validator($config, $logger);
$error     = null;

// Reconstruir conexión desde sesión
$dbConfig = [
    'host'     => $_SESSION['db_host'],
    'port'     => $config['database']['default_port'],
    'username' => $_SESSION['db_username'],
    'password' => $_SESSION['db_password'],
    'charset'  => $config['database']['charset']
];
$db = new MySQLDatabase($dbConfig, $logger);

try {
    $db->connect();
} catch (Exception $e) {
    $error = 'Error al conectar con MySQL. Por favor, verifica las credenciales.';
    $logger->error('Error al reconectar en página de database', ['error' => $e->getMessage()]);
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $dbName = trim($_POST['db_name'] ?? '');

    // Validar nombre
    $nameValidation = $validator->validateDatabaseName($dbName);

    if (!$nameValidation['success']) {
        $error = $nameValidation['message'];
    } else {
        // Verificar si existe
        $existsCheck = $validator->checkDatabaseExists($db, $dbName);

        if (!$existsCheck['success']) {
            $error = $existsCheck['message'];
        } else {
            // Guardar en sesión
            $_SESSION['db_name'] = $dbName;

            $logger->info('Nombre de base de datos validado', ['name' => $dbName]);

            // Redirigir a resumen
            header('Location: summary');
            exit;
        }
    }
}

$logger->info('Acceso a página de configuración de base de datos');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['app']['name']; ?> - Base de Datos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="installer-container">
        <div class="installer-box">
            <!-- Header -->
            <div class="installer-header">
                <h1 class="title is-3">Configuración de Base de Datos</h1>
                <p>Paso 2 de 4</p>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator mt-4">
                <div class="step completed">
                    <div class="step-number">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="step-line"></div>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <div class="step-line"></div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-line"></div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                </div>
            </div>

            <!-- Body -->
            <div class="installer-body">
                <?php if ($error){ ?>
                    <div class="notification is-danger">
                        <button class="delete"></button>
                        <strong><i class="fas fa-times-circle"></i> Error</strong>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php } ?>

                <div class="content mb-4">
                    <p>Ingresa el nombre de la base de datos que deseas crear para el sistema.</p>
                </div>

                <form action="database" method="POST">
                    <div class="form-group">
                        <label class="label" for="db_name">
                            <i class="fas fa-database"></i> Nombre de la Base de Datos
                        </label>
                        <div class="control has-icons-left">
                            <input type="text" class="input" id="db_name" name="db_name" value="<?php echo htmlspecialchars($_POST['db_name'] ?? ''); ?>" placeholder="mi_base_datos" required pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="64">
                            <span class="icon is-small is-left">
                                <i class="fas fa-database"></i>
                            </span>
                        </div>
                        <p class="help" id="db-name-help">Solo letras, números y guiones bajos</p>
                    </div>

                    <div class="box has-background-info-light">
                        <p class="has-text-info-dark">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong> El nombre debe tener entre 3 y 64 caracteres y solo puede contener letras, números y guiones bajos.
                        </p>
                    </div>

                    <div class="installer-footer" style="margin: 0 -2rem -2rem -2rem;">
                        <a href="credentials" class="button is-light">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Atrás</span>
                        </a>

                        <button type="submit" class="button is-primary">
                            <span>Siguiente</span>
                            <span class="icon">
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/installer.js"></script>
</body>

</html>
