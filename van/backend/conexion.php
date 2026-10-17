<?php
class BaseDatos {
    private static $conexion = null;

    public static function obtenerConexion() {
        if (self::$conexion === null) {
            $host = 'localhost';
            $dbname = 'svdistribuciones'; // Base de datos aislada para la PWA[cite: 19]
            $username = 'root';           // Usuario configurado[cite: 19]
            $password = '1712666864';     // Contraseña configurada[cite: 19]
            
            try {
                self::$conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$conexion;
    }
}
?>