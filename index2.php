<?php
$db = new SQLite3(':memory:');
$db->exec("CREATE TABLE users (id INT, username TEXT, password TEXT)");
$db->exec("INSERT INTO users VALUES (1, 'admin', 'UCA_Cyber_2026!')");

$mensaje = "";

// Procesar Login (SQLi)
if (isset($_POST['username']) && isset($_POST['password'])) {
    $u = $_POST['username'];
    $p = $_POST['password'];
    $query = "SELECT * FROM users WHERE username = '$u' AND password = '$p'";
    $result = $db->query($query);
    if ($row = $result->fetchArray()) {
        $mensaje = "<p style='color:green;'>¡Acceso concedido! Flag: <strong>UCA{sql_bypass_php}</strong></p>";
    } else {
        $mensaje = "<p style='color:red;'>Credenciales inválidas</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lab UCA - Ciberseguridad</title>
</head>
<body>
    <h2>Laboratorio UCA - Reto Web Abierto</h2>
    <p>Todas las secciones de prueba se encuentran disponibles en este panel.</p>

    <!-- SECCIÓN 1: Login (SQL Injection) -->
    <hr>
    <h3>1. Panel de Inicio de Sesión (SQL Injection)</h3>
    <form method="POST">
        <input type="text" name="username" placeholder="Usuario" required><br><br>
        <input type="password" name="password" placeholder="Contraseña" required><br><br>
        <button type="submit">Entrar</button>
    </form>
    <?php echo $mensaje; ?>

    <!-- SECCIÓN 2: Buscador (XSS Reflejado) -->
    <hr>
    <h3>2. Buscador del Sitio (XSS Reflejado)</h3>
    <form method="GET">
        <input type="text" name="busqueda" placeholder="Buscar producto...">
        <button type="submit">Buscar</button>
    </form>
    <?php 
    if(isset($_GET['busqueda'])) {
        // VULNERABILIDAD: XSS Reflejado sin escapar
        echo "<p>Resultados para la búsqueda: " . $_GET['busqueda'] . "</p>";
    }
    ?>

    <!-- SECCIÓN 3: Subida de Archivos (RCE) -->
    <hr>
    <h3>3. Panel de Subida de Avatares (File Upload / RCE)</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="archivo" required>
        <button type="submit" name="subir">Subir Archivo</button>
    </form>
    <?php
    if (isset($_POST['subir']) && isset($_FILES['archivo'])) {
        $dir = "uploads/";
        if (!file_exists($dir)) { mkdir($dir, 0777, true); }
        $destino = $dir . basename($_FILES['archivo']['name']);
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
            echo "<p style='color:green;'>¡Archivo subido con éxito! Accede aquí: <a href='$destino' target='_blank'>$destino</a></p>";
        } else {
            echo "<p style='color:red;'>Error al subir el archivo.</p>";
        }
    }
    ?>
    <hr>
    <h3>4. Panel de Perfil de Usuario (IDOR)</h3>
    <p>Visualiza y gestiona tu información de perfil de usuario:</p>
    
    <form method="GET">
        <!-- El input está fijo o limitado visualmente al usuario actual (ID 2) -->
        <label>ID de Usuario:</label>
        <input type="number" name="id_perfil" value="2" readonly style="background-color: #e0e0e0;"><br><br>
        <button type="submit">Cargar Perfil</button>
    </form>

    <?php
    // Si el parámetro no viaja por GET, por defecto cargamos el ID 2 del propio usuario
    $id_user = isset($_GET['id_perfil']) ? intval($_GET['id_perfil']) : 2;

    // Simulamos la base de datos de perfiles
    $db_idor = new SQLite3(':memory:');
    $db_idor->exec("CREATE TABLE perfiles (id INT, nombre TEXT, rol TEXT, tarjeta_credito TEXT)");
    $db_idor->exec("INSERT INTO perfiles VALUES (1, 'Admin Principal', 'Administrador', '4000-1234-5678-9010')");
    $db_idor->exec("INSERT INTO perfiles VALUES (2, 'Estudiante (Tú)', 'Usuario Regular', '4532-8888-9999-1111')");

    $res = $db_idor->query("SELECT * FROM perfiles WHERE id = $id_user");
    
    if ($row = $res->fetchArray()) {
        echo "<div style='background: #f9f9f9; padding: 10px; margin-top: 10px; border: 1px solid #ccc;'>";
        echo "<p><strong>ID:</strong> {$row['id']}</p>";
        echo "<p><strong>Nombre:</strong> {$row['nombre']}</p>";
        echo "<p><strong>Rol:</strong> {$row['rol']}</p>";
        echo "<p><strong>Tarjeta de Crédito:</strong> <span style='color:red;'>{$row['tarjeta_credito']}</span></p>";
        echo "</div>";
    } else {
        echo "<p style='color:red;'>Usuario no encontrado.</p>";
    }
    ?>
</body>
</html>
