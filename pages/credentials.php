<?php

/*
*=================================================     Detalles    =================================================
*
* Página de Credenciales
*
*=================================================    Descripcion  =================================================
*
* Solicita y valida las credenciales de MySQL.
* Verifica que el usuario tenga permisos para crear bases de datos.
*
*===================================================================================================================
*/

$validator = new Validator($config, $logger);
$error = null;
$success = null;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host     = trim($_POST['host'] ?? $config['database']['default_host']);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validar que no estén vacíos
    if (empty($username)) {
        $error = 'El nombre de usuario es requerido';
    } else {
        // Validar credenciales
        $result = $validator->validateCredentials($host, $username, $password);

        if ($result['success']) {
            // Verificar permisos
            $permissionResult = $validator->validateCreatePermission($result['db']);

            if ($permissionResult['success']) {
                // Guardar credenciales en sesión (no guardamos la conexión porque PDO no es serializable)
                $_SESSION['db_host']     = $host;
                $_SESSION['db_username'] = $username;
                $_SESSION['db_password'] = $password;

                $logger->info('Credenciales validadas y guardadas en sesión', [
                    'host'     => $host,
                    'username' => $username
                ]);

                // Cerrar la conexión de prueba
                $result['db']->disconnect();

                if (isset($_SESSION['db_error'])) {
                    unset($_SESSION['db_error']);
                }

                // Redirigir a la siguiente página
                header('Location: database');
                exit;
            } else {
                $error = $permissionResult['message'];
                $result['db']->disconnect();
            }
        } else {
            $error = $result['message'];
        }
    }
}

$logger->info('Acceso a página de credenciales');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['app']['name']; ?> - Credenciales</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="installer-container">
        <div class="installer-box">
            <!-- Header -->
            <div class="installer-header">
                <h1 class="title is-3">Credenciales de MySQL</h1>
                <p>Paso 1 de 4</p>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator mt-4">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-line"></div>
                </div>
                <div class="step">
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
                    <p>Ingresa las credenciales de un usuario de MySQL que tenga permisos para crear bases de datos.</p>
                </div>

                <form action="credentials" method="POST">
                    <div class="form-group">
                        <label class="label" for="host">
                            <i class="fas fa-server"></i> Host de MySQL
                        </label>
                        <div class="control has-icons-left">
                            <input type="text" class="input" id="host" name="host" value="<?php echo htmlspecialchars($_POST['host'] ?? ''); ?>" placeholder="localhost" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-server"></i>
                            </span>
                        </div>
                        <p class="help">Dirección del servidor MySQL (generalmente localhost)</p>
                    </div>

                    <div class="form-group">
                        <label class="label" for="username">
                            <i class="fas fa-user"></i> Usuario
                        </label>
                        <div class="control has-icons-left">
                            <input type="text" class="input" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" placeholder="root" required autocomplete="username">
                            <span class="icon is-small is-left">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                        <p class="help">Usuario con permisos de creación de bases de datos</p>
                    </div>

                    <div class="form-group">
                        <label class="label" for="password">
                            <i class="fas fa-lock"></i> Contraseña
                        </label>
                        <div class="control has-icons-left">
                            <input type="password" class="input" id="password" name="password" placeholder="••••••••" autocomplete="current-password">
                            <span class="icon is-small is-left">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                        <p class="help">Contraseña del usuario MySQL</p>
                    </div>

                    <div class="field is-grouped is-grouped-centered mt-5">
                        <p class="control">
                            <a href="welcome" class="button is-light">
                                <span class="icon">
                                    <i class="fas fa-arrow-left"></i>
                                </span>
                                <span>Atrás</span>
                            </a>
                        </p>
                        <p class="control">
                            <button type="submit" class="button is-primary">
                                <span>Siguiente</span>
                                <span class="icon">
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                            </button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/installer.js"></script>
</body>

</html>
