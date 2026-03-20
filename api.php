<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Conexion a BD
$host = 'localhost';
$db   = 'urlshortener';
$user = 'root';
$pass = 'dsw123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexion']);
    exit;
}

require_once  __DIR__ . '/controlador/UrlController.php';

$controller = new UrlController($pdo);
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Accion POST /api.php?action=acortar
// Recibe una URL original, la acorta y devuelve la URL corta generada.
if ($method === 'POST' && $action === 'acortar') {
    $body = json_decode(file_get_contents('php://input'), true);
    $urlOriginal = $body['urlOriginal'] ?? '';
    if (empty($urlOriginal)) {
        http_response_code(400);
        echo json_encode(['error' => 'URL no proporcionada']);
        exit;
    }
    $resultado = $controller->acortar($urlOriginal, $_SERVER['HTTP_HOST']);
    echo json_encode($resultado);

// Accion GET /api.php?action=estadisticas&codigo=...
// Devuelve el resumen estadístico asociado a un codigo corto.
} elseif ($method === 'GET' && $action === 'estadisticas') {
    $codigo = $_GET['codigo'] ?? '';
    if (empty($codigo)) {
        http_response_code(400);
        echo json_encode(['error' => 'Codigo no proporcionado']);
        exit;
    }
    $resultado = $controller->estadisticas($codigo);
    if (!$resultado) {
        http_response_code(404);
        echo json_encode(['error' => 'URL no encontrada']);
        exit;
    }
    echo json_encode($resultado);

// Accion GET /api.php?action=redirigir&codigo=...
// Busca la URL original, registra el acceso y redirige al destino final.
} elseif ($method === 'GET' && $action === 'redirigir') {
    $codigo = $_GET['codigo'] ?? '';
    if (empty($codigo)) {
        http_response_code(400);
        echo json_encode(['error' => 'Codigo no proporcionado']);
        exit;
    }
    $ip = $_SERVER['REMOTE_ADDR'];
    $urlOriginal = $controller->redirigir($codigo, $ip);
    if (!$urlOriginal) {
    http_response_code(404);
    echo json_encode(['error' => 'URL no encontrada']);
    exit;
    }
    echo json_encode(['urlOriginal' => $urlOriginal]);
    exit;

// Maneja cualquier combinacion de método o accion no soportada.
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Accion no valida']);
}
?>
