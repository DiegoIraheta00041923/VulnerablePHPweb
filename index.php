<?php
// Conexión simple a SQLite en PHP (para no complicarse con MySQL)
$db = new SQLite3(':memory:');
$db->exec("CREATE TABLE users (id INT, username TEXT, password TEXT)");
$db->exec("INSERT INTO users VALUES (1, 'admin', 'UCA_Cyber_2026!')");

$mensaje = "";
if (isset($_POST['username']) && isset($_POST['password'])) {
    $u = $_POST['username'];
    $p = $_POST['password'];
    
    // VULNERABILIDAD: Inyección SQL clásica por concatenación
    $query = "SELECT * FROM users WHERE username = '$u' AND password = '$p'";
    $result = $db->query($query);
    
    if ($row = $result->fetchArray()) {
        $mensaje = "<h3>¡Acceso concedido! Flag: UCA{sql_bypass_php}</h3>";
    } else {
        $mensaje = "<h3>Credenciales inválidas</h3>";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Lab UCA - PHP</title></head>
<body>
    <h2>Laboratorio UCA - Web Vulnerable (Apache + PHP)</h2>
    
    <h3>Login (SQLi)</h3>
    <form method="POST">
        <input type="text" name="username" placeholder="Usuario"><br>
        <input type="password" name="password" placeholder="Contraseña"><br>
        <button type="submit">Entrar</button>
    </form>
    <?php echo $mensaje; ?>

    <h3>XSS Reflejado en Buscador</h3>
    <form method="GET" action="">
        <input type="text" name="busqueda" placeholder="Buscar...">
        <button type="submit">Buscar</button>
    </form>
    <?php 
    if(isset($_GET['busqueda'])) {
        // VULNERABILIDAD: XSS Reflejado (imprime la entrada sin sanitizar)
        echo "<p>No se encontraron resultados para: " . $_GET['busqueda'] . "</p>";
    }
    ?>
</body>
</html>
