//Clase para gestionar la conexión con la base de datos 
<?php
class BaseDeDatos{
    //Definición de las credenciales del servidor 
    private $host = "localhost";
    private $db_name = "urlshortener";
    private $username = "root";
    private $password = "dsw123";
    public $conn; 


    //Método para conectar con el servidor
     
    public function conectar() {
        $this->conn = null; //Se reinicia la conexión
        try {

            //Se instancia la nueva conexión
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}", 
                $this->username, 
                $this->password
            );

            //Define cómo visualizar los errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch(PDOException $ex) {
            echo "Error de conexión: " . $ex->getMessage();
        }

        return $this->conn;
    }
}
?>