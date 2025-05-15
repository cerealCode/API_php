<?php
// Iniciar sesión
session_start();

// Incluir el autoloader de Composer
require 'vendor/autoload.php';

// Importar las clases necesarias de Guzzle
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Comprobar si ya hay un token en la sesión
if (isset($_SESSION['token'])) {
    echo "<h2>Ya hay un usuario autenticado</h2>";
    echo "<p>Token: " . $_SESSION['token'] . "</p>";
    echo "<p><a href='logout.php'>Cerrar sesión</a></p>";
    echo "<p><a href='mascotasLFV.php'>Ver mis mascotas</a></p>";
    exit;
}

// Variables para manejar mensajes y errores
$error = null;
$mensaje = null;
$codigo = null;

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Crear cliente Guzzle con la configuración base
    $guzzleClient = new Client(['http_errors' => false,]);
    
    try {
        // Realizar la petición POST al servicio web de autenticación
        $response = $guzzleClient->post('http://localhost/dwes06/server/public/api/login', [
            'form_params' => [
                'email' => $email,
                'password' => $password
            ]
        ]);
        
        // Obtener el código de respuesta HTTP
        $code = $response->getStatusCode();
        
        // Obtener el cuerpo del mensaje
        $body = $response->getBody()->getContents();
        
        // Decodificar la respuesta JSON
        $data = json_decode($body, true);
        
        // Verificar si la autenticación fue exitosa (código 200)
        if ($code === 200 && isset($data['token'])) {
            // Guardar el token en la sesión
            $_SESSION['token'] = $data['token'];
            $_SESSION['user_id'] = $data['user']['id'] ?? null;
            $_SESSION['user_email'] = $email;
            
            // Redirigir a la página principal o mostrar mensaje de éxito
            $mensaje = "Autenticacado. Token generado y almacenado.";
            
            // Redirigir a la página de mascotas
            header("Location: mascotas.php");
            exit;
        } else {
            // Mostrar el mensaje de error recibido
            $error = $data['message'] ?? 'Error de autenticación';
            $codigo = $code;
        }
    } catch (RequestException $e) {
        $error = "Error de conexión al servidor";
        $codigo = 500;
        
        if ($e->hasResponse()) {
            $error = $e->getResponse()->getBody()->getContents();
            $codigo = $e->getResponse()->getStatusCode();
        }
        
        // Log the error for debugging
        error_log("Login error: " . $error);
        error_log("Request URL: " . $e->getRequest()->getUri());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - API REST Cliente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Login - API REST Cliente</h1>
    
    <?php if ($error): ?>
    <div class="error">
        <p>Error: <?php echo htmlspecialchars($error); ?></p>
        <?php if ($codigo): ?>
        <p>Código de estado HTTP: <?php echo htmlspecialchars($codigo); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($mensaje): ?>
    <div class="success">
        <p><?php echo htmlspecialchars($mensaje); ?></p>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <input type="submit" value="Iniciar sesión">
        </div>
    </form>
</body>
</html>