<?php
// Modelo encargado de consultar y persistir los accesos registrados en logs.
class Log {
    private $pdo;

    // Recibe la conexión PDO usada por las operaciones sobre logs.
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Inserta un nuevo registro de acceso para una URL.
    public function crear($urlId, $ip, $pais = null) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO logs (url_id, ip, pais) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$urlId, $ip, $pais]);
    }

    // Devuelve todos los accesos de una URL ordenados del más reciente al más antiguo.
    public function obtenerPorUrl($urlId) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM logs WHERE url_id = ? ORDER BY fecha DESC"
        );
        $stmt->execute([$urlId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cuenta cuántos accesos tiene una URL.
    public function contarAccesos($urlId) {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as total FROM logs WHERE url_id = ?"
        );
        $stmt->execute([$urlId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Obtiene los accesos más recientes del sistema según el límite indicado.
    public function obtenerRecientes($limite = 10) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM logs ORDER BY fecha DESC LIMIT ?"
        );
        $stmt->bindValue(1, (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agrupa los accesos de una URL por país y los ordena por frecuencia.
    public function accesosPorPais($urlId) {
        $stmt = $this->pdo->prepare(
            "SELECT pais, COUNT(*) as total 
             FROM logs 
             WHERE url_id = ? 
             GROUP BY pais 
             ORDER BY total DESC"
        );
        $stmt->execute([$urlId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
