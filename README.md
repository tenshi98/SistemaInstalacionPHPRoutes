# Sistema de Instalación PHP

Sistema completo de instalación con asistente paso a paso para configurar bases de datos MySQL con interfaz moderna usando Bulma CSS.

## Tabla de Contenidos

- [Tecnologías Utilizadas](#tecnologías-utilizadas)
- [Características](#características)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Cómo Ejecutar el Proyecto](#cómo-ejecutar-el-proyecto)
- [Ejemplos de Uso](#ejemplos-de-uso)
- [Estructura de Carpetas](#estructura-de-carpetas)
- [Explicación de Módulos](#explicación-de-módulos)
- [Migración a Otras Bases de Datos](#migración-a-otras-bases-de-datos)
- [Solución de Problemas](#solución-de-problemas)
- [Notas Adicionales](#notas-adicionales)

---

## Tecnologías Utilizadas

### Backend
- **PHP 7.0+**: Lenguaje de programación del lado del servidor
- **PDO (PHP Data Objects)**: Capa de abstracción para acceso a bases de datos
- **MySQL/MariaDB**: Sistema de gestión de bases de datos

### Frontend
- **Bulma CSS 0.9.4**: Framework CSS moderno y responsive
- **Font Awesome 6.4.0**: Biblioteca de iconos
- **JavaScript Vanilla**: Para validaciones del lado del cliente

### Arquitectura
- **MVC Simplificado**: Separación de lógica de negocio y presentación
- **Programación Orientada a Objetos**: Clases modulares y reutilizables
- **Patrón de Diseño**: Abstracción de base de datos para fácil migración

---

## Características

✅ **Asistente Paso a Paso**: Interfaz intuitiva con 4 pasos claramente definidos

✅ **Validaciones Completas**:
- Verificación de credenciales MySQL
- Validación de permisos de usuario
- Comprobación de existencia de base de datos
- Validación de nombres según estándares MySQL

✅ **Sistema de Logging Robusto**:
- Tres niveles: `info`, `warning`, `error`
- Rotación automática por fecha
- Formato estructurado con timestamp y contexto

✅ **Manejo de Errores**:
- Excepciones capturadas y registradas
- Mensajes amigables para el usuario
- Rollback automático en caso de fallo

✅ **Arquitectura Modular**:
- Separación clara de responsabilidades
- Fácil mantenimiento y extensión
- Abstracción de base de datos

✅ **Interfaz Moderna**:
- Diseño responsive con Bulma CSS
- Animaciones suaves
- Indicadores de progreso visuales

✅ **Seguridad**:
- Uso de consultas preparadas (PDO)
- Protección contra SQL Injection
- Permisos restrictivos en archivos de configuración

---

## Requisitos

### Requisitos del Servidor

- **PHP**: Versión 7.0 o superior
- **MySQL**: Versión 5.7 o superior (o MariaDB 10.2+)
- **Extensiones PHP requeridas**:
  - `pdo`
  - `pdo_mysql`
  - `json`
  - `mbstring`

### Permisos

- Usuario MySQL con privilegios `CREATE DATABASE`
- Permisos de escritura en las carpetas:
  - `config/`
  - `logs/`

### Navegadores Compatibles

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

---

## Instalación

### 1. Clonar o Descargar el Proyecto

```bash
cd /mnt/Desarrollos/Entornos/docker_entorno_lamp/www/
# El proyecto ya está en la carpeta 'instalador'
```

### 2. Verificar Permisos

```bash
cd instalador
chmod 755 config logs
chmod 644 config/settings.php
```

### 3. Verificar Extensiones PHP

```bash
php -m | grep -E 'pdo|pdo_mysql|json|mbstring'
```

Si falta alguna extensión, instálala:

```bash
# En Ubuntu/Debian
sudo apt-get install php-mysql php-mbstring php-json

# En CentOS/RHEL
sudo yum install php-mysqlnd php-mbstring php-json
```

### 4. Configurar Servidor Web

#### Apache

Asegúrate de que el módulo `mod_rewrite` esté habilitado:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Configuración de VirtualHost (opcional):

```apache
<VirtualHost *:80>
    ServerName instalador.local
    DocumentRoot /mnt/Desarrollos/Entornos/docker_entorno_lamp/www/instalador
    
    <Directory /mnt/Desarrollos/Entornos/docker_entorno_lamp/www/instalador>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name instalador.local;
    root /mnt/Desarrollos/Entornos/docker_entorno_lamp/www/instalador;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

## Configuración

### Archivo de Configuración Principal

Edita `config/settings.php` antes de ejecutar el instalador:

```php
return [
    'paths' => [
        'config_file' => __DIR__ . '/database.php',  // Ruta del archivo de configuración de BD
        'sql_file' => __DIR__ . '/../sql/install.sql',  // Ruta del archivo SQL
        'log_dir' => __DIR__ . '/../logs',  // Directorio de logs
    ],
    
    'urls' => [
        'return_welcome' => '/',  // URL de retorno en página de bienvenida
        'return_finish' => '/',   // URL de retorno al finalizar
    ],
    
    'database' => [
        'default_host' => 'localhost',  // Host por defecto
        'default_port' => 3306,         // Puerto por defecto
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    
    'validation' => [
        'db_name_pattern' => '/^[a-zA-Z0-9_]+$/',  // Patrón para nombres de BD
        'db_name_min_length' => 3,
        'db_name_max_length' => 64,
    ],
];
```

### Personalizar Archivo SQL

Reemplaza `sql/install.sql` con tu propio script SQL si es necesario. El archivo de ejemplo incluye:

- Tabla `usuarios`
- Tabla `categorias`
- Tabla `productos`
- Tabla `pedidos`
- Tabla `detalle_pedidos`
- Datos de ejemplo para demostración

---

## Cómo Ejecutar el Proyecto

### 1. Acceder al Instalador

Abre tu navegador y navega a:

```
http://localhost/instalador
```

O si configuraste un VirtualHost:

```
http://instalador.local
```

### 2. Seguir el Asistente

#### Paso 1: Bienvenida
- Lee la información del instalador
- Verifica que no exista una instalación previa
- Haz clic en **"Iniciar Instalación"**

#### Paso 2: Credenciales MySQL
- Ingresa el **host** (generalmente `localhost`)
- Ingresa el **usuario** con permisos de creación de BD
- Ingresa la **contraseña**
- Haz clic en **"Siguiente"**

El sistema verificará:
- ✅ Que las credenciales sean válidas
- ✅ Que el usuario tenga permisos `CREATE DATABASE`

#### Paso 3: Configuración de Base de Datos
- Ingresa el **nombre de la base de datos** a crear
- El nombre debe:
  - Tener entre 3 y 64 caracteres
  - Contener solo letras, números y guiones bajos
  - No existir previamente
- Haz clic en **"Siguiente"**

#### Paso 4: Resumen
- Revisa la configuración
- Verifica que el archivo SQL exista
- Haz clic en **"Ejecutar Instalación"**

#### Paso 5: Finalización
- El sistema ejecutará:
  1. Creación de la base de datos
  2. Generación del archivo de configuración
  3. Ejecución del script SQL
- Verás un resumen del proceso
- Haz clic en **"Ir a la Aplicación"**

### 3. Verificar la Instalación

Verifica que se hayan creado los archivos:

```bash
# Archivo de configuración
ls -la config/database.php

# Archivo de log
ls -la logs/installer_*.log
```

Verifica la base de datos:

```bash
mysql -u tu_usuario -p
```

```sql
USE nombre_de_tu_bd;
SHOW TABLES;
SELECT COUNT(*) FROM usuarios;
```

---

## Ejemplos de Uso

### Uso Básico

```bash
# 1. Navegar al instalador
http://localhost/instalador

# 2. Completar el asistente con:
Host: localhost
Usuario: root
Contraseña: tu_contraseña
Nombre BD: mi_sistema

# 3. El sistema creará automáticamente:
- Base de datos 'mi_sistema'
- Archivo config/database.php
- Log en logs/installer_YYYY-MM-DD.log
```

### Usar la Configuración Generada

```php
<?php
// En tu aplicación principal
$dbConfig = require __DIR__ . '/instalador/config/database.php';

$dsn = sprintf(
    "mysql:host=%s;port=%d;dbname=%s;charset=%s",
    $dbConfig['host'],
    $dbConfig['port'],
    $dbConfig['database'],
    $dbConfig['charset']
);

$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

// Ahora puedes usar $pdo para consultas
$stmt = $pdo->query("SELECT * FROM usuarios");
$usuarios = $stmt->fetchAll();
```

### Reinstalación

Si necesitas reinstalar:

```bash
# 1. Eliminar el archivo de configuración
rm config/database.php

# 2. (Opcional) Eliminar la base de datos
mysql -u root -p -e "DROP DATABASE IF EXISTS mi_sistema;"

# 3. Ejecutar el instalador nuevamente
http://localhost/instalador
```

---

## Estructura de Carpetas

```
instalador/
├── assets/                      # Recursos estáticos
│   ├── css/
│   │   └── custom.css          # Estilos personalizados
│   └── js/
│       └── installer.js        # JavaScript del cliente
├── config/                      # Configuración
│   ├── settings.php            # Configuración general
│   └── database.php            # Configuración de BD (generado)
├── core/                        # Módulos principales
│   ├── ConfigManager.php       # Gestión de configuración
│   ├── Database.php            # Clase abstracta de BD
│   ├── Logger.php              # Sistema de logging
│   ├── MySQLDatabase.php       # Implementación MySQL
│   └── Validator.php           # Validaciones
├── logs/                        # Archivos de log
│   ├── .gitkeep
│   └── installer_*.log         # Logs por fecha
├── pages/                       # Páginas del asistente
│   ├── credentials.php         # Paso 1: Credenciales
│   ├── database.php            # Paso 2: Configuración BD
│   ├── finish.php              # Paso 4: Finalización
│   ├── summary.php             # Paso 3: Resumen
│   └── welcome.php             # Página de bienvenida
├── sql/                         # Scripts SQL
│   └── install.sql             # Script de instalación
├── index.php                    # Punto de entrada
└── README.md                    # Este archivo
```

---

## Explicación de Módulos

### Core Modules

#### `Logger.php`
**Propósito**: Sistema de logging con tres niveles (info, warning, error)

**Métodos principales**:
- `info($message, $context)`: Registrar información
- `warning($message, $context)`: Registrar advertencias
- `error($message, $context)`: Registrar errores
- `readLog($lines)`: Leer contenido del log

**Características**:
- Rotación automática por fecha
- Formato estructurado con timestamp
- Contexto adicional en JSON

#### `Database.php`
**Propósito**: Clase abstracta para abstracción de base de datos

**Métodos abstractos**:
- `connect()`: Conectar a la BD
- `disconnect()`: Desconectar
- `query($query, $params)`: Ejecutar consulta
- `databaseExists($dbName)`: Verificar existencia
- `createDatabase($dbName)`: Crear BD
- `executeFile($filepath)`: Ejecutar archivo SQL

**Ventajas**:
- Facilita migración a otros motores (PostgreSQL, SQLite)
- Interfaz consistente
- Logging integrado

#### `MySQLDatabase.php`
**Propósito**: Implementación específica para MySQL/MariaDB

**Métodos adicionales**:
- `hasCreatePermission()`: Verificar permisos de creación

**Características**:
- Uso de PDO para seguridad
- Transacciones para ejecución de archivos SQL
- Rollback automático en caso de error

#### `Validator.php`
**Propósito**: Validaciones del sistema

**Métodos**:
- `configFileExists()`: Verificar instalación previa
- `validateCredentials($host, $user, $pass)`: Validar credenciales MySQL
- `validateCreatePermission($db)`: Verificar permisos
- `validateDatabaseName($name)`: Validar nombre de BD
- `checkDatabaseExists($db, $name)`: Verificar existencia
- `validateSqlFile()`: Verificar archivo SQL

#### `ConfigManager.php`
**Propósito**: Gestión de archivos de configuración

**Métodos**:
- `exists()`: Verificar si existe configuración
- `generateDatabaseConfig($config)`: Generar archivo
- `readDatabaseConfig()`: Leer configuración
- `validateConfig()`: Validar integridad
- `deleteConfig()`: Eliminar (para reinstalación)

### Pages (Páginas del Asistente)

#### `welcome.php`
- Página de bienvenida
- Verificación de instalación previa
- Información del proceso

#### `credentials.php`
- Formulario de credenciales MySQL
- Validación de conexión
- Verificación de permisos

#### `database.php`
- Formulario de nombre de BD
- Validación de formato
- Verificación de existencia

#### `summary.php`
- Resumen de configuración
- Verificación de archivo SQL
- Confirmación antes de ejecutar

#### `finish.php`
- Ejecución del proceso de instalación
- Creación de BD
- Generación de configuración
- Ejecución de SQL
- Resumen de resultados

---

## Migración a Otras Bases de Datos

El sistema está diseñado para facilitar la migración a otros motores de base de datos.

### Migrar a PostgreSQL

1. **Crear nueva clase de adaptador**:

```php
<?php
// core/PostgreSQLDatabase.php
require_once __DIR__ . '/Database.php';

class PostgreSQLDatabase extends Database {
    public function connect() {
        $dsn = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $this->host,
            $this->port ?: 5432,
            $this->database
        );
        
        $this->connection = new PDO($dsn, $this->username, $this->password);
        return true;
    }
    
    public function createDatabase($dbName, $charset = null, $collation = null) {
        $query = sprintf('CREATE DATABASE "%s"', $dbName);
        if ($charset) {
            $query .= sprintf(" ENCODING '%s'", $charset);
        }
        $this->connection->exec($query);
        return true;
    }
    
    // Implementar otros métodos abstractos...
}
```

2. **Modificar las páginas para usar el nuevo adaptador**:

```php
// En credentials.php, database.php, etc.
$db = new PostgreSQLDatabase($dbConfig, $logger);
```

### Migrar a SQLite

```php
<?php
// core/SQLiteDatabase.php
class SQLiteDatabase extends Database {
    public function connect() {
        $dsn = "sqlite:" . $this->database;
        $this->connection = new PDO($dsn);
        return true;
    }
    
    public function createDatabase($dbName, $charset = null, $collation = null) {
        // SQLite crea la BD automáticamente al conectar
        return true;
    }
    
    // Implementar otros métodos...
}
```

---

## Solución de Problemas

### Error: "Credenciales inválidas"

**Causa**: Usuario o contraseña incorrectos

**Solución**:
```bash
# Verificar credenciales
mysql -u tu_usuario -p

# Si olvidaste la contraseña de root
sudo mysql
ALTER USER 'root'@'localhost' IDENTIFIED BY 'nueva_contraseña';
FLUSH PRIVILEGES;
```

### Error: "Usuario no tiene permisos"

**Causa**: El usuario no tiene privilegio `CREATE DATABASE`

**Solución**:
```sql
-- Conectar como root
mysql -u root -p

-- Otorgar permisos
GRANT CREATE ON *.* TO 'tu_usuario'@'localhost';
FLUSH PRIVILEGES;
```

### Error: "Archivo SQL no encontrado"

**Causa**: La ruta en `config/settings.php` es incorrecta

**Solución**:
```php
// Verificar la ruta en config/settings.php
'sql_file' => __DIR__ . '/../sql/install.sql',

// Verificar que el archivo existe
ls -la sql/install.sql
```

### Error: "No se puede escribir el archivo de configuración"

**Causa**: Permisos insuficientes en la carpeta `config/`

**Solución**:
```bash
chmod 755 config/
chmod 644 config/settings.php
```

### Error: "La base de datos ya existe"

**Causa**: Ya existe una BD con ese nombre

**Solución**:
```sql
-- Opción 1: Usar otro nombre
-- Opción 2: Eliminar la BD existente
DROP DATABASE nombre_existente;
```

### Logs no se generan

**Causa**: Permisos insuficientes en carpeta `logs/`

**Solución**:
```bash
chmod 755 logs/
# Verificar que PHP puede escribir
sudo chown www-data:www-data logs/
```

### Error de PDO

**Causa**: Extensión PDO no instalada

**Solución**:
```bash
# Ubuntu/Debian
sudo apt-get install php-mysql php-pdo

# Verificar
php -m | grep pdo
```

---

## Notas Adicionales

### Seguridad

1. **Proteger archivos sensibles**:
```bash
chmod 640 config/database.php
chown www-data:www-data config/database.php
```

2. **Agregar a `.gitignore`**:
```gitignore
config/database.php
logs/*.log
```

3. **Usar HTTPS en producción**

4. **Eliminar el instalador después de usar**:
```bash
# En producción, eliminar o mover fuera del DocumentRoot
mv instalador /ruta/segura/fuera/de/web/
```

### Rendimiento

- Los logs se rotan automáticamente por fecha
- Se recomienda limpiar logs antiguos periódicamente:
```bash
find logs/ -name "*.log" -mtime +30 -delete
```

### Personalización

#### Cambiar colores del tema

Edita `assets/css/custom.css`:

```css
:root {
    --primary-color: #3273dc;  /* Cambiar color primario */
    --success-color: #48c774;  /* Cambiar color de éxito */
}
```

#### Cambiar idioma

Todos los textos están en español. Para traducir:
1. Buscar textos en las páginas PHP
2. Reemplazar con el idioma deseado
3. Actualizar también `assets/js/installer.js`

### Compatibilidad

- **PHP 7.0 - 8.2**: Totalmente compatible
- **MySQL 5.7+**: Recomendado
- **MariaDB 10.2+**: Compatible
- **PHP 8.3+**: Requiere pruebas adicionales

### Soporte

Para reportar problemas o sugerencias:
1. Revisar esta documentación
2. Verificar los logs en `logs/`
3. Consultar la sección de Solución de Problemas

---

**Versión**: 1.0.0  
**Última actualización**: 2026-01-29  
**Licencia**: MIT  
**Autor**: Sistema de Instalación PHP
