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
$mascota = null;

// Verificar si hay un token en la sesión
if (!isset($_SESSION['token'])) {
    // No hay token, mostrar enlace a login y finalizar
    echo '<p>No hay sesión activa. Por favor, <a href="login.php">inicie sesión</a> primero.</p>';
    exit;
}

// Verificar si se ha proporcionado un ID de mascota
// if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['id'])) {
//     echo '<p>Debe proporcionar un ID de mascota. <a href="mascotas.php">Volver a la lista</a></p>';
//     exit;
// }

// Obtener el ID de la mascota
//TODO PROBAR TERNARIOS 
//$id_mascota = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if (isset($_GET['id'])) {
    $id_mascota = (int) $_GET['id'];
} else if (isset($_POST['id'])) {
    $id_mascota = (int) $_POST['id'];
} else {
    $id_mascota = 0;
}
// Obtener los datos de la mascota si no es un envío de formulario
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_SESSION['token'];
    $guzzleClient = new Client(['http_errors' => false]);

    try {
        // Realizar la petición GET al servicio web para obtener la lista de mascotas
        $response = $guzzleClient->get('http://localhost:8000/dwes06/server/public/api/mascotasLFV', [
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

        // Buscar la mascota con el ID proporcionado
        $mascota = null;
        foreach ($mascotas as $m) {
            if ($m['id'] == $id_mascota) {
                $mascota = $m;
                break;
            }
        }

        // Verificar si se encontró la mascota
        if (!$mascota) {
            echo '<p>Mascota no encontrada. <a href="mascotas.php">Volver a la lista</a></p>';
            exit;
        }

    } catch (RequestException $e) {
        // Capturar errores de la petición
        $error = "Error en la petición: " . $e->getMessage();
        $codigo = $e->getCode();
    }
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $id_mascota = $_POST['id'] ?? 0;
    $descripcion = $_POST['descripcion'] ?? '';
    $publica = $_POST['publica'] ?? '';

    // Crear cliente Guzzle
    $guzzleClient = new Client(['http_errors' => false]);
    $token = $_SESSION['token'];

    try {
        // Realizar la petición PUT al servicio web
        $response = $guzzleClient->put("http://localhost/api/mascotaLFV/{$id_mascota}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'descripcion' => $descripcion,
                'publica' => $publica
            ]
        ]);

        // Obtener el código de respuesta HTTP
        $codigo = $response->getStatusCode();

        // Obtener el cuerpo del mensaje
        $body = $response->getBody();

        // Decodificar la respuesta JSON
        $data = json_decode($body, true);

        // Actualizar los datos de la mascota con los nuevos valores para mostrarlos en el formulario
        $mascota = [
            'id' => $id_mascota,
            'descripcion' => $descripcion,
            'publica' => ($publica == 'Si'),
            'nombre' => $data['message'] ?? 'Mascota' // Extraer nombre si está en el mensaje
        ];

        // Manejo explícito de los diferentes códigos de estado HTTP
        if ($codigo === 200) {
            $mensaje = $data['message'] ?? 'Mascota modificada correctamente';
        } else if ($codigo === 403) {
            $error = 'La mascota no es del usuario (403): ' . ($data['message'] ?? 'No tienes permiso para modificar esta mascota');
        } else if ($codigo === 404) {
            $error = 'La mascota no existe (404): ' . ($data['message'] ?? 'La mascota solicitada no fue encontrada');
        } else if ($codigo === 400) {
            // Para errores de validación o formato incorrecto
            if (isset($data['errors'])) {
                $errores_detalles = [];
                foreach ($data['errors'] as $campo => $mensajes) {
                    $errores_detalles[] = "$campo: " . implode(", ", $mensajes);
                }
                $error = 'Los datos recibidos no son formato JSON o son incorrectos (400): ' . implode(". ", $errores_detalles);
            } else {
                $error = 'Los datos recibidos no son formato JSON o son incorrectos (400): ' . ($data['message'] ?? 'Error en los datos enviados');
            }
        } else {
            // Otros códigos de error
            $error = "Error inesperado ({$codigo}): " . ($data['message'] ?? 'Error desconocido');
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
    <title>Modificar Mascota - API REST Cliente</title>
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

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        select {
            padding: 8px;
            width: 100%;
        }

        input[type="radio"] {
            margin-right: 10px;
        }

        .radio-group label {
            display: inline-block;
            margin-right: 15px;
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
    </style>
</head>

<body>
    <h1>Modificar Mascota</h1>

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
            <?php if ($codigo): ?>
                <p>Código de estado HTTP: <?php echo htmlspecialchars($codigo); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($mascota): ?>
        <form method="POST" action="cambiarmascota.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id_mascota); ?>">

            <div class="form-group">
                <label for="id">ID de la mascota:</label>
                <input type="text" id="id" value="<?php echo htmlspecialchars($id_mascota); ?>" disabled>
            </div>

            <?php if (isset($mascota['nombre'])): ?>
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" value="<?php echo htmlspecialchars($mascota['nombre']); ?>" disabled>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="4"
                    required><?php echo htmlspecialchars($mascota['descripcion'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Pública:</label>
                <div class="radio-group">
                    <input type="radio" id="publica-si" name="publica" value="Si" <?php echo (isset($mascota['publica']) && $mascota['publica']) ? 'checked' : ''; ?> required>
                    <label for="publica-si">Sí</label>

                    <input type="radio" id="publica-no" name="publica" value="No" <?php echo (isset($mascota['publica']) && !$mascota['publica']) ? 'checked' : ''; ?>>
                    <label for="publica-no">No</label>
                </div>
            </div>

            <div class="form-group">
                <input type="submit" value="Guardar Cambios">
            </div>
        </form>
    <?php endif; ?>

    <div class="links">
        <a href="mascotas.php">Volver a la lista de mascotas</a>
    </div>
</body>

</html>