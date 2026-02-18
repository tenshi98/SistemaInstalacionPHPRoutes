-- =====================================================
-- Script SQL de Instalación - Base de Datos de Ejemplo
-- =====================================================
-- Este archivo contiene la estructura y datos de ejemplo
-- para demostrar el funcionamiento del instalador.
-- =====================================================

-- Tabla: usuarios
-- Almacena información de usuarios del sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: categorias
-- Categorías para organizar productos
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(50),
    orden INT DEFAULT 0,
    activa BOOLEAN DEFAULT TRUE,
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: productos
-- Catálogo de productos del sistema
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    sku VARCHAR(50) UNIQUE,
    imagen_url VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE,
    INDEX idx_categoria (categoria_id),
    INDEX idx_sku (sku),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: pedidos
-- Registro de pedidos realizados
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10, 2) NOT NULL,
    estado ENUM('pendiente', 'procesando', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    direccion_envio TEXT,
    notas TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: detalle_pedidos
-- Detalle de productos en cada pedido
CREATE TABLE IF NOT EXISTS detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    INDEX idx_pedido (pedido_id),
    INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS DE EJEMPLO
-- =====================================================

-- Insertar usuarios de ejemplo
INSERT INTO usuarios (nombre, apellido, email, telefono, activo) VALUES
('Juan', 'Pérez', 'juan.perez@example.com', '+56912345678', TRUE),
('María', 'González', 'maria.gonzalez@example.com', '+56987654321', TRUE),
('Carlos', 'Rodríguez', 'carlos.rodriguez@example.com', '+56923456789', TRUE),
('Ana', 'Martínez', 'ana.martinez@example.com', '+56934567890', TRUE),
('Luis', 'Fernández', 'luis.fernandez@example.com', '+56945678901', TRUE),
('Carmen', 'López', 'carmen.lopez@example.com', '+56956789012', TRUE),
('Pedro', 'Sánchez', 'pedro.sanchez@example.com', '+56967890123', TRUE),
('Laura', 'Ramírez', 'laura.ramirez@example.com', '+56978901234', TRUE),
('Diego', 'Torres', 'diego.torres@example.com', '+56989012345', TRUE),
('Sofía', 'Flores', 'sofia.flores@example.com', '+56990123456', TRUE);

-- Insertar categorías de ejemplo
INSERT INTO categorias (nombre, descripcion, icono, orden, activa) VALUES
('Electrónica', 'Dispositivos y accesorios electrónicos', 'fa-laptop', 1, TRUE),
('Ropa', 'Vestimenta y accesorios de moda', 'fa-shirt', 2, TRUE),
('Hogar', 'Artículos para el hogar y decoración', 'fa-house', 3, TRUE),
('Deportes', 'Equipamiento y ropa deportiva', 'fa-dumbbell', 4, TRUE),
('Libros', 'Libros físicos y digitales', 'fa-book', 5, TRUE),
('Juguetes', 'Juguetes y juegos para niños', 'fa-gamepad', 6, TRUE),
('Alimentos', 'Productos alimenticios y bebidas', 'fa-utensils', 7, TRUE),
('Salud', 'Productos de salud y bienestar', 'fa-heart-pulse', 8, TRUE);

-- Insertar productos de ejemplo
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, sku, activo) VALUES
(1, 'Laptop HP Pavilion 15', 'Laptop con procesador Intel Core i5, 8GB RAM, 256GB SSD', 599990.00, 15, 'ELEC-LAP-001', TRUE),
(1, 'Mouse Logitech MX Master 3', 'Mouse inalámbrico ergonómico de alta precisión', 89990.00, 45, 'ELEC-MOU-001', TRUE),
(1, 'Teclado Mecánico Corsair K70', 'Teclado mecánico RGB con switches Cherry MX', 129990.00, 30, 'ELEC-TEC-001', TRUE),
(1, 'Monitor Samsung 27" 4K', 'Monitor 4K UHD de 27 pulgadas', 349990.00, 20, 'ELEC-MON-001', TRUE),
(2, 'Polera Nike Dri-FIT', 'Polera deportiva de secado rápido', 24990.00, 100, 'ROPA-POL-001', TRUE),
(2, 'Jeans Levi\'s 501', 'Jeans clásicos de corte recto', 49990.00, 75, 'ROPA-JEA-001', TRUE),
(2, 'Zapatillas Adidas Ultraboost', 'Zapatillas running con tecnología Boost', 119990.00, 50, 'ROPA-ZAP-001', TRUE),
(2, 'Chaqueta The North Face', 'Chaqueta impermeable para outdoor', 89990.00, 40, 'ROPA-CHA-001', TRUE),
(3, 'Juego de Sábanas King', 'Sábanas de algodón egipcio 400 hilos', 39990.00, 60, 'HOGA-SAB-001', TRUE),
(3, 'Lámpara de Escritorio LED', 'Lámpara LED regulable con puerto USB', 19990.00, 80, 'HOGA-LAM-001', TRUE),
(3, 'Set de Ollas Tefal', 'Set de 5 ollas antiadherentes', 79990.00, 35, 'HOGA-OLL-001', TRUE),
(3, 'Aspiradora Robot Xiaomi', 'Aspiradora robot con mapeo inteligente', 199990.00, 25, 'HOGA-ASP-001', TRUE),
(4, 'Bicicleta de Montaña Trek', 'Bicicleta MTB aro 29 con suspensión', 899990.00, 10, 'DEPO-BIC-001', TRUE),
(4, 'Pesas Ajustables 20kg', 'Set de pesas ajustables para gimnasio en casa', 59990.00, 40, 'DEPO-PES-001', TRUE),
(4, 'Colchoneta Yoga Premium', 'Colchoneta antideslizante 6mm grosor', 29990.00, 70, 'DEPO-COL-001', TRUE),
(5, 'Cien Años de Soledad', 'Novela de Gabriel García Márquez', 14990.00, 120, 'LIBR-NOV-001', TRUE),
(5, 'El Principito', 'Clásico de Antoine de Saint-Exupéry', 9990.00, 150, 'LIBR-INF-001', TRUE),
(6, 'LEGO Star Wars Millennium Falcon', 'Set de construcción 1351 piezas', 149990.00, 20, 'JUGU-LEG-001', TRUE),
(6, 'Muñeca Barbie Dreamhouse', 'Casa de muñecas con accesorios', 79990.00, 30, 'JUGU-MUN-001', TRUE),
(7, 'Café Grano Premium 1kg', 'Café de grano arábica tostado', 12990.00, 200, 'ALIM-CAF-001', TRUE),
(7, 'Aceite de Oliva Extra Virgen', 'Aceite de oliva premium 500ml', 8990.00, 150, 'ALIM-ACE-001', TRUE),
(8, 'Vitamina C 1000mg', 'Suplemento vitamínico 60 cápsulas', 15990.00, 100, 'SALU-VIT-001', TRUE),
(8, 'Proteína Whey 2kg', 'Proteína en polvo sabor chocolate', 39990.00, 60, 'SALU-PRO-001', TRUE);

-- Insertar pedidos de ejemplo
INSERT INTO pedidos (usuario_id, total, estado, direccion_envio, notas) VALUES
(1, 689980.00, 'entregado', 'Av. Libertador 1234, Santiago', 'Entregar en horario de oficina'),
(2, 169980.00, 'enviado', 'Calle Principal 567, Valparaíso', NULL),
(3, 49990.00, 'procesando', 'Pasaje Los Aromos 89, Concepción', 'Llamar antes de entregar'),
(4, 299970.00, 'pendiente', 'Av. España 2345, Viña del Mar', NULL),
(5, 899990.00, 'entregado', 'Calle Larga 456, La Serena', 'Dejar en portería'),
(6, 129980.00, 'cancelado', 'Av. Colón 789, Antofagasta', 'Cliente solicitó cancelación'),
(7, 59990.00, 'entregado', 'Paseo Bulnes 123, Santiago', NULL),
(8, 229970.00, 'procesando', 'Calle Arturo Prat 345, Temuco', NULL),
(9, 39990.00, 'enviado', 'Av. Bernardo O\'Higgins 678, Rancagua', NULL),
(10, 55980.00, 'entregado', 'Calle Maipú 901, Puerto Montt', 'Entregar en la mañana');

-- Insertar detalles de pedidos
INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES
-- Pedido 1
(1, 1, 1, 599990.00, 599990.00),
(1, 2, 1, 89990.00, 89990.00),
-- Pedido 2
(2, 3, 1, 129990.00, 129990.00),
(2, 10, 2, 19990.00, 39980.00),
-- Pedido 3
(3, 6, 1, 49990.00, 49990.00),
-- Pedido 4
(4, 4, 1, 349990.00, 349990.00),
-- Pedido 5
(5, 13, 1, 899990.00, 899990.00),
-- Pedido 6
(6, 7, 1, 119990.00, 119990.00),
(6, 9, 1, 9990.00, 9990.00),
-- Pedido 7
(7, 14, 1, 59990.00, 59990.00),
-- Pedido 8
(8, 18, 1, 149990.00, 149990.00),
(8, 19, 1, 79990.00, 79990.00),
-- Pedido 9
(9, 11, 1, 39990.00, 39990.00),
-- Pedido 10
(10, 20, 2, 12990.00, 25980.00),
(10, 22, 2, 15990.00, 30000.00);

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
