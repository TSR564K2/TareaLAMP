<?php

require_once(__DIR__ . '/../modelo/Url.php');

// Controla los casos de uso principales del acortador:
// crear URLs cortas, redirigir y consultar estadísticas.
class UrlController {
    private $modelo;

    // Inicializa el controlador con el modelo de URLs.
    public function __construct($pdo) {
        $this->modelo = new Url($pdo);
    }

    // Accion acortar:
    // crea una URL corta o devuelve la existente si ya fue registrada.
    public function acortar($urlOriginal, $host) {

        $existente = $this->modelo->buscarPorOriginal($urlOriginal);
        if ($existente) {
            return [
                'urlOriginal' => $urlOriginal,
                'shortUrl' => "http://138.2.235.169/" . $existente['url_corta'],
                'mensaje' => 'URL ya existia'
            ];
        }

        // Generar codigo unico
        $codigo = $this->generarCodigo();

        // Guardar
        $this->modelo->crear($urlOriginal, $codigo);

        return [
            'urlOriginal' => $urlOriginal,
            'shortUrl' => "http://138.2.235.169/" . $codigo,
            'mensaje' => 'URL acortada correctamente'
        ];
    }

    // Accion redirigir:
    // resuelve el codigo corto, registra el acceso y devuelve la URL original.
    public function redirigir($codigo, $ip) {
        $url = $this->modelo->buscarPorCorta($codigo);
        if (!$url) return null;

        $pais = $this->obtenerPais($ip);
        $this->modelo->registrarAcceso($url['id'], $ip, $pais);

        return $url['url_original'];
    }

    // Accion estadisticas:
    // construye el resumen estadístico de una URL corta a partir de sus logs.
    public function estadisticas($codigo) {
        $url = $this->modelo->buscarPorCorta($codigo);
        if (!$url) return null;

        $logs = $this->modelo->obtenerLogs($url['id']);
        $totalAccesos = count($logs);
        $paises = array_unique(array_column($logs, 'pais'));

        $accesosPorDia = [];
        foreach ($logs as $log) {
            $fecha = substr($log['fecha'], 0, 10);
            $accesosPorDia[$fecha] = ($accesosPorDia[$fecha] ?? 0) + 1;
        }

        return [
            'urlOriginal' => $url['url_original'],
            'urlCorta' => $url['url_corta'],
            'fecha' => $url['fecha'],
            'totalAccesos' => $totalAccesos,
            'paises' => array_values($paises),
            'accesosPorDia' => $accesosPorDia
        ];
    }

    // Genera un codigo aleatorio de 6 caracteres y valida que no exista.
    private function generarCodigo() {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $codigo = '';
            for ($i = 0; $i < 6; $i++) {
                $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
            }
            $existente = $this->modelo->buscarPorCorta($codigo);
        } while ($existente);
        return $codigo;
    }


    // Consulta ipinfo para traducir la IP a un pais en español.
    private function obtenerPais($ip) {
    try {
        $token = getenv('IPINFO_TOKEN');
        $url = "https://ipinfo.io/$ip/json" . ($token ? "?token=$token" : "");

        $respuesta = file_get_contents($url);
        $data = json_decode($respuesta, true);

        $codigo = $data['country'] ?? null;

        return \Locale::getDisplayRegion('-' . $codigo, 'es');

    } catch (Exception $e) {
        return 'ERROR';
    }
}
}
?>
