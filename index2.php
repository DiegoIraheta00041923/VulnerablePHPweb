<?php
// Conexión simple a SQLite en memoria
$db = new SQLite3(':memory:');
$db->exec("CREATE TABLE users (id INT, username TEXT, password TEXT)");
$db->exec("INSERT INTO users VALUES (1, 'admin', 'UCA_Cyber_2026!')");

$mensaje = "";
$sql_exitoso = false;

// 1. Procesar Login (SQLi)
if (isset($_POST['username']) && isset($_POST['password'])) {
    $u = $_POST['username'];
    $p = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$u' AND password = '$p'";
    $result = $db->query($query);
    
    if ($row = $result->fetchArray()) {
        $sql_exitoso = true;
        $mensaje = "<h3>¡Acceso concedido! Copia la flag: <span style='color:red;'>UCA{sql_bypass_php}</span></h3>";
    } else {
        $mensaje = "<h3>Credenciales inválidas</h3>";
    }
}

// 2. Verificar si se envió la flag para desbloquear el XSS
$flag_enviada = isset($_POST['flag_input']) && $_POST['flag_input'] === 'UCA{sql_bypass_php}';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lab UCA - Ciberseguridad</title>
</head>
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
            <h3>Paso 2: Introduce la flag obtenida para desbloquear el siguiente nivel</h3>
            <form method="POST">
                <!-- Mantenemos los datos de sesión anteriores ocultos o permitimos ingresarla directamente -->
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($_POST['username']); ?>">
                <input type="hidden" name="password" value="<?php echo htmlspecialchars($_POST['password']); ?>">
                
                <input type="text" name="flag_input" placeholder="Pega la flag aquí (UCA{...})" style="width: 300px;" required>
                <button type="submit">Validar Flag</button>
            </form>
        <?php endif; ?>

    <?php else: ?>
        <!-- FASE 3: Desbloqueado el buscador con XSS -->
        <hr style="border: 2px solid green;">
        <h2 style="color: green;">¡Fase 1 y 2 Superadas!</h2>
        <p>Has validado la flag correctamente. Ahora tienes acceso al buscador del sistema.</p>

        <h3>Paso 3: Buscador (XSS Reflejado)</h3>
        <form method="GET" action="">
            <input type="text" name="busqueda" placeholder="Buscar producto...">
            <button type="submit">Buscar</button>
        </form>

        <?php 
        if(isset($_GET['busqueda'])) {
            // VULNERABILIDAD: XSS Reflejado (imprime la entrada sin sanitizar)
            echo "<p>No se encontraron resultados para: " . $_GET['busqueda'] . "</p>";
        }
        ?>
    <?php endif; ?>
</body>
</html>
