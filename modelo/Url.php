<?php
// Modelo encargado de consultar y persistir URLs acortadas y sus accesos.
class Url {
    
    private $pdo;

    // Recibe la conexión PDO usada por el resto de consultas del modelo.
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Inserta una nueva URL original junto con su codigo corto.
    public function crear($urlOriginal, $urlCorta) {
        $stmt = $this->pdo->prepare("INSERT INTO urls (url_original, url_corta) VALUES (?, ?)");
        return $stmt->execute([$urlOriginal, $urlCorta]);
    }

    // Busca una URL registrada a partir de su valor original.
    public function buscarPorOriginal($urlOriginal) {
        $stmt = $this->pdo->prepare("SELECT * FROM urls WHERE url_original = ?");
        $stmt->execute([$urlOriginal]);
        return $stmt->fetch();
    }

    // Busca una URL registrada a partir de su codigo corto.
    public function buscarPorCorta($urlCorta) {
        $stmt = $this->pdo->prepare("SELECT * FROM urls WHERE url_corta = ?");
        $stmt->execute([$urlCorta]);
        return $stmt->fetch();
    }

    // Devuelve todas las URLs ordenadas por fecha de creación descendente.
    public function obtenerTodas() {
        $stmt = $this->pdo->query("SELECT * FROM urls ORDER BY fecha DESC");
        return $stmt->fetchAll();
    }

    // Registra un acceso en la tabla de logs para una URL determinada.
    public function registrarAcceso($urlId, $ip, $pais) {
        $stmt = $this->pdo->prepare("INSERT INTO logs (url_id, ip, pais) VALUES (?, ?, ?)");
        return $stmt->execute([$urlId, $ip, $pais]);
    }

    // Obtiene el historial de accesos asociado a una URL.
    public function obtenerLogs($urlId) {
        $stmt = $this->pdo->prepare("SELECT * FROM logs WHERE url_id = ? ORDER BY fecha ASC");
        $stmt->execute([$urlId]);
        return $stmt->fetchAll();
    }
}
?>
