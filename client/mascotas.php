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

// Variables para mensajes
$error = null;
$codigo = null;
$mascotas = [];

// Verificar si hay un token en la sesión
if (!isset($_SESSION['token'])) {
    // No hay token, redirigir a login
    header("Location: login.php");
    exit;
}

// Obtener el token de la sesión
$token = $_SESSION['token'];

// Crear cliente Guzzle
$guzzleClient = new Client(['http_errors' => false]);

try {
    // Realizar la petición GET al servicio web para obtener las mascotas
    $response = $guzzleClient->get('http://localhost:8000/api/mascotasLFV', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token
        ]
    ]);
    
    // Obtener el código de respuesta HTTP
    $codigo = $response->getStatusCode();
    
    // Obtener el cuerpo del mensaje
    $body = $response->getBody();
    
    // Decodificar la respuesta JSON
    $mascotas = json_decode($body, true);
    
    // Verificar si la petición fue exitosa (código 200)
    if ($codigo !== 200) {
        $error = "Error al obtener las mascotas: Código $codigo";
    }
} catch (RequestException $e) {
    // Capturar errores de la petición
    $error = "Error en la petición: " . $e->getMessage();
    $codigo = $e->getCode();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mis Mascotas - API REST Cliente</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .error {
            color: red;
            padding: 10px;
            background-color: #ffeeee;
            border: 1px solid red;
            margin-bottom: 15px;
        }
        .links {
            margin-top: 20px;
        }
        .links a {
            margin-right: 15px;
        }
        .actions a {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>Mis Mascotas</h1>
    
    <?php if ($error): ?>
    <div class="error">
        <p><?php echo htmlspecialchars($error); ?></p>
        <?php if ($codigo): ?>
        <p>Código de estado HTTP: <?php echo htmlspecialchars($codigo); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if (empty($mascotas) && !$error): ?>
    <p>No tienes mascotas registradas.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Tipo</th>
                <th>Pública</th>
                <th>Me gustas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mascotas as $mascota): ?>
            <tr>
                <td><?php echo htmlspecialchars($mascota['id']); ?></td>
                <td><?php echo htmlspecialchars($mascota['nombre']); ?></td>
                <td><?php echo htmlspecialchars($mascota['descripcion']); ?></td>
                <td><?php echo htmlspecialchars($mascota['tipo']); ?></td>
                <td><?php echo $mascota['publica'] ? 'Sí' : 'No'; ?></td>
                <td><?php echo htmlspecialchars($mascota['megustas']); ?></td>
                <td class="actions">
    <a href="modificarmascota.php?id=<?php echo $mascota['id']; ?>">Editar</a> | 
    <a href="borrarmascota.php?id=<?php echo $mascota['id']; ?>">Borrar</a>
</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    
    <div class="links">
        <a href="nuevamascota.html">Crear nueva mascota</a>
        
        <a href="logout.php">Cerrar sesión</a>
    </div>
</body>
</html>