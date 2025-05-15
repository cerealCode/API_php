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
$code = null;
$mensaje = null;
$id_mascota = null;
$nombre_usuario = null;

// Verificar si hay un token en la sesión
if (!isset($_SESSION['token'])) {
    // No hay token, mostrar enlace a login y finalizar
    echo '<p>No hay sesión activa. Por favor, <a href="login.php">inicie sesión</a> primero.</p>';
    exit;
}

// Procesar los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    // Checkbox convertida a enum
    $publica = isset($_POST['publica']) ? 'Si' : 'No';
    
    // Crear cliente Guzzle con la configuración base
    $guzzleClient = new Client([
        'base_uri' => 'http://localhost',
        'http_errors' => false,
        'verify' => false,
        'headers' => [
            'Accept' => 'application/json'
        ]
    ]);
    
    try {
        // Realizar la petición POST al servicio web
        $response = $guzzleClient->post('/dwes06/server/public/api/crearmascotaLFV', [
            'headers' => [
                'Authorization' => 'Bearer ' . $_SESSION['token']
            ],
            'form_params' => [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'tipo' => $tipo,
                'publica' => $publica
            ]
        ]);
        

        // Validación y sanitización de datos
$errores = [];

// Validar nombre (requerido y longitud)
$nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS));
if (empty($nombre)) {
    $errores[] = 'El nombre es obligatorio';
} elseif (strlen($nombre) > 50) {  // Asumiendo un límite máximo
    $errores[] = 'El nombre no puede exceder los 50 caracteres';
}

// Validar descripción 
$descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS));
if (strlen($descripcion) > 500) {  // Asumiendo un límite máximo
    $errores[] = 'La descripción no puede exceder los 500 caracteres';
}

// Validar tipo (asumiendo que es una lista predefinida)
$tiposPermitidos = ['perro', 'gato', 'ave', 'roedor', 'reptil', 'otro']; // Ajusta según tus necesidades
$tipo = trim(filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS));
if (!in_array(strtolower($tipo), $tiposPermitidos)) {
    $errores[] = 'El tipo seleccionado no es válido';
}

// Checkbox publica (convertir a enum Si/No)
$publica = (filter_input(INPUT_POST, 'publica', FILTER_VALIDATE_BOOLEAN)) ? 'Si' : 'No';

// Si hay errores, mostrarlos y no continuar
if (!empty($errores)) {
    $error = implode('. ', $errores);
    // Aquí podrías redirigir de vuelta al formulario o mostrar los errores
} else {
    try {
        // Ahora que los datos están validados y sanitizados, realizar la petición
        $response = $guzzleClient->post('/dwes06/server/public/api/crearmascotaLFV', [
            'headers' => [
                'Authorization' => 'Bearer ' . $_SESSION['token']
            ],
            //TODO VALIDATE POST: VALIDATE/FILTER SANITIZE
            'form_params' => [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'tipo' => $tipo,
                'publica' => $publica
            ]
        ]);
        

        // Obtener el código de respuesta HTTP
        $code = $response->getStatusCode();
        
        // Obtener el cuerpo del mensaje
        $body = $response->getBody();
        
        // Decodificar la respuesta JSON
        $data = json_decode($body, true);
        
        // Verificar si la petición fue exitosa (código 200)
        if ($code === 200) {
            // Guardar el ID de la mascota y el nombre del usuario para mostrarlos
            $id_mascota = $data['id'] ?? 'No disponible';
            $nombre_usuario = $data['user'] ?? 'Usuario';
        } else {
            // Guardar los mensajes de error si los hubiera
            if (isset($data['errors'])) {
                $errores_detalles = [];
                foreach ($data['errors'] as $campo => $mensajes) {
                    $errores_detalles[] = "$campo: " . implode(", ", $mensajes);
                }
                $error = implode(". ", $errores_detalles);
            } else {
                $error = $data['message'] ?? 'Error desconocido';
            }
        }
    } catch (RequestException $e) {
        // Capturar errores de la petición
        $error = "Error en la petición: " . $e->getMessage();
        $code = $e->getCode();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Resultado - API REST Cliente</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .error {
            color: red;
            padding: 10px;
            background-color: #ffeeee;
            border: 1px solid red;
            margin-bottom: 15px;
        }
        .success {
            color: green;
            padding: 10px;
            background-color: #eeffee;
            border: 1px solid green;
            margin-bottom: 15px;
        }
        .status {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .links {
            margin-top: 20px;
        }
        .links a {
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <h1>Resultado de la operación</h1>
    
    <?php if ($code): ?>
    <div class="status">
        <p>Código de estado HTTP: <?php echo htmlspecialchars($code); ?></p>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="error">
        <p>Error en los datos: <?php echo htmlspecialchars($error); ?></p>
    </div>
    <?php endif; ?>
    
    <?php if ($code === 200): ?>
    <div class="success">
        <p>La mascota se ha creado correctamente</p>
        <p>ID de la mascota: <?php echo htmlspecialchars($id_mascota); ?></p>
        <p>Usuario: <?php echo htmlspecialchars($nombre_usuario); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="links">
        <a href="nuevamascota.html">Crear otra mascota</a>
        <a href="mascotas.php">Ver todas las mascotas</a>
        <a href="logout.php">Cerrar sesión</a>
    </div>
</body>
</html>