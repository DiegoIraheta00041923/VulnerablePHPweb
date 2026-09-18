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
<h3>4. Perfil de Usuario (IDOR)</h3>
<p>Introduce un ID de usuario para ver su información privada (ej. ID 1 o ID 2):</p>
<form method="GET">
    <input type="number" name="id_perfil" placeholder="ID de usuario">
    <button type="submit">Ver Perfil</button>
</form>
<?php
if (isset($_GET['id_perfil'])) {
    $id_user = intval($_GET['id_perfil']);
    // Simulamos una tabla de perfiles privados
    $db_idor = new SQLite3(':memory:');
    $db_idor->exec("CREATE TABLE perfiles (id INT, nombre TEXT, tarjeta_credito TEXT)");
    $db_idor->exec("INSERT INTO perfiles VALUES (1, 'Admin Principal', '4000-1234-5678-9010')");
    $db_idor->exec("INSERT INTO perfiles VALUES (2, 'Diego (Tú)', '4532-8888-9999-1111')");

    $res = $db_idor->query("SELECT * FROM perfiles WHERE id = $id_user");
    if ($row = $res->fetchArray()) {
        echo "<p style='color:blue;'>ID: {$row['id']} | Nombre: {$row['nombre']} | Tarjeta: <strong>{$row['tarjeta_credito']}</strong></p>";
    } else {
        echo "<p style='color:red;'>Usuario no encontrado.</p>";
    }
}
?>
</body>
</html>
