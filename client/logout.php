<?php
// Incluir el autoloader de Composer
require 'vendor/autoload.php';

// Importar las clases necesarias de Guzzle
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Verificar el estado de la sesión antes de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si ya hay un token en la sesión
if (isset($_SESSION['token'])) {
    // Si ya hay un token, mostrar mensaje de usuario ya autenticado
    echo "Ya hay un usuario autenticado";
    echo '<a href="client\login.php"> Ir a login</a>';
    exit;
}

// Variables para mensajes
$mensaje = '';
$error = '';
$codigo = '';

// Verificar si hay un token en la sesión
if (isset($_SESSION['token'])) {
    // Hay un token, intentar hacer logout
    $token = $_SESSION['token'];
    
    // Crear cliente Guzzle
    $guzzleClient = new Client(['http_errors' => false]);
    
    try {
        // Realizar la petición POST al servicio web de logout
        $response = $guzzleClient->post('http://localhost:8000/api/auth/logout', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        
        // Obtener el código de respuesta HTTP
        $codigo = $response->getStatusCode();
        
        // Obtener el cuerpo del mensaje
        $body = $response->getBody();
        
        // Decodificar la respuesta JSON
        $data = json_decode($body, true);
        
        // Destruir la sesión independientemente de la respuesta
        session_unset();
        session_destroy();
        
        $mensaje = "Has cerrado sesión correctamente.";
    } catch (RequestException $e) {
        // Capturar errores de la petición
        $error = "Error en la petición: " . $e->getMessage();
        $codigo = $e->getCode();
    }
} else {
    // No hay token en la sesión
    $mensaje = "No hay sesión activa para cerrar.";
}


?>
<!DOCTYPE html>
<html>
<head>
    <title>Logout - API REST Cliente</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Cierre de sesión</h1>
    
    <?php if ($error): ?>
    <div style="color: red;">
        <p>Error: <?php echo htmlspecialchars($error); ?></p>
        <?php if ($codigo): ?>
        <p>Código de estado HTTP: <?php echo htmlspecialchars($codigo); ?></p>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div style="color: green;">
        <p><?php echo htmlspecialchars($mensaje); ?></p>
    </div>
    <?php endif; ?>
    
    <p><a href="login.php">Volver a la página de login</a></p>
</body>
</html>