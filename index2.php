<?php
$db = new SQLite3(':memory:');
$db->exec("CREATE TABLE users (id INT, username TEXT, password TEXT)");
$db->exec("INSERT INTO users VALUES (1, 'admin', 'UCA_Cyber_2026!')");

$mensaje = "";
$sql_exitoso = false;

if (isset($_POST['username']) && isset($_POST['password'])) {
    $u = $_POST['username'];
    $p = $_POST['password'];
    $query = "SELECT * FROM users WHERE username = '$u' AND password = '$p'";
    $result = $db->query($query);
    if ($row = $result->fetchArray()) {
        $sql_exitoso = true;
        $mensaje = "<h3>¡Acceso concedido! Flag: <span style='color:red;'>UCA{sql_bypass_php}</span></h3>";
    } else {
        $mensaje = "<h3>Credenciales inválidas</h3>";
    }
}

$flag_enviada = isset($_POST['flag_input']) && $_POST['flag_input'] === 'UCA{sql_bypass_php}';
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Lab UCA</title></head>
<body>
    <h2>Laboratorio UCA - Reto Web Enlazado</h2>

    <?php if (!$flag_enviada): ?>
        <!-- FASE 1: Formulario de Login (SQLi) -->
        <h3>Paso 1: Iniciar Sesión (SQL Injection)</h3>
        <form method="POST">
            <input type="text" name="username" placeholder="Usuario" required><br><br>
            <input type="password" name="password" placeholder="Contraseña" required><br><br>
            <button type="submit">Entrar</button>
        </form>
        <?php echo $mensaje; ?>

        <?php if ($sql_exitoso): ?>
            <hr>
            <!-- FASE 2: Recuadro para introducir la flag -->
            <h3>Paso 2: Introduce la flag obtenida</h3>
            <form method="POST">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                <input type="hidden" name="password" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>">
                <input type="text" name="flag_input" placeholder="Pega la flag aquí" required>
                <button type="submit">Validar Flag</button>
            </form>
        <?php endif; ?>

    <?php else: ?>
        <!-- FASE 3 Y 4: Se desbloquean SOLAMENTE al ingresar la flag correcta -->
        <hr style="border: 2px solid green;">
        <h2 style="color: green;">¡Fases 1 y 2 Superadas!</h2>
        
        <h3>Paso 3: Buscador (XSS Reflejado)</h3>
        <form method="GET">
            <input type="text" name="busqueda" placeholder="Buscar...">
            <button type="submit">Buscar</button>
        </form>
        <?php 
        if(isset($_GET['busqueda'])) {
            echo "<p>Resultados para: " . $_GET['busqueda'] . "</p>";
        }
        ?>

        <hr style="border: 2px solid orange;">
        <h3>Paso 4: Panel de Subida de Archivos (RCE)</h3>
        <p>Sube tu archivo para completar el último nivel del laboratorio:</p>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="archivo" required>
            <button type="submit" name="subir">Subir</button>
        </form>

        <?php
        if (isset($_POST['subir']) && isset($_FILES['archivo'])) {
            $dir = "uploads/";
            if (!file_exists($dir)) { mkdir($dir, 0777, true); }
            $destino = $dir . basename($_FILES['archivo']['name']);
            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
                echo "<p style='color:green;'>¡Subido con éxito! Accede aquí: <a href='$destino' target='_blank'>$destino</a></p>";
            } else {
                echo "<p style='color:red;'>Error al subir el archivo.</p>";
            }
        }
        ?>
    <?php endif; ?>
</body>
</html>
