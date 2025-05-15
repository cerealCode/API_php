<?php
// Verificar el estado de la sesión antes de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir el autoloader de Composer
require 'vendor/autoload.php';

// Importar las clases necesarias de Guzzle
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Variables para mensajes
$error = null;
$mensaje = null;
$codigo = null;

// Verificar si hay un token en la sesión
if (!isset($_SESSION['token'])) {
    // No hay token, mostrar enlace a login y finalizar
    echo '<p>No hay sesión activa. Por favor, <a href="login.php">inicie sesión</a> primero.</p>';
    exit;
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id_mascota = (int)$_POST['id'];
    $token = $_SESSION['token'];
    
    // Crear cliente Guzzle
    $guzzleClient = new Client(['http_errors' => false]);
    
    try {
        // Realizar la petición DELETE al servicio web
        $response = $guzzleClient->delete("http://localhost:8000/api/mascotaLFV/{$id_mascota}", [
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
        
        // Verificar la respuesta según el código
        if ($codigo === 200) {
            $mensaje = $data['message'] ?? 'Mascota eliminada correctamente';
        } else {
            $error = $data['message'] ?? 'Error al eliminar la mascota';
        }
    } catch (RequestException $e) {
        // Capturar errores de la petición
        $error = "Error en la petición: " . $e->getMessage();
        $codigo = $e->getCode();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Borrar Mascota - API REST Cliente</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        form {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background-color: #c0392b;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-left: 4px solid #c62828;
            margin-bottom: 15px;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-left: 4px solid #2e7d32;
            margin-bottom: 15px;
        }
        .status {
            font-weight: bold;
            margin-top: 5px;
        }
        a {
            color: #2196f3;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Borrar Mascota</h1>
    
    <?php if ($error): ?>
    <div class="error">
        <p><?php echo htmlspecialchars($error); ?></p>
        <?php if ($codigo): ?>
        <p class="status">Código de estado HTTP: <?php echo htmlspecialchars($codigo); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($mensaje): ?>
    <div class="success">
        <p><?php echo htmlspecialchars($mensaje); ?></p>
        <?php if ($codigo): ?>
        <p class="status">Código de estado HTTP: <?php echo htmlspecialchars($codigo); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Formulario para introducir el ID de la mascota -->
    <form method="POST" action="borrarmascota.php">
        <div>
            <label for="id">ID de la mascota a eliminar:</label>
            <input type="number" id="id" name="id" required>
        </div>
        <div>
            <input type="submit" value="Eliminar Mascota">
        </div>
    </form>

    <div>
        <a href="mascotas.php">Volver a la lista de mascotas</a>
    </div>
</body>
</html>