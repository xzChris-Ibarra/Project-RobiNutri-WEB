<?php
class UserModel {
    private $conn;
    private $table = "cuenta";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function existeCorreo($correo) {
        try {
            $query = "SELECT id_cuenta FROM " . $this->table . " WHERE email = :correo LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":correo", $correo);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en existeCorreo: " . $e->getMessage());
            return false;
        }
    }
    
    public function crear($nombre, $apellido, $correo, $contraseña, $fecha_nacimiento, $genero) {
        try {
            //Verificar si el correo existe
            if ($this->existeCorreo($correo)) {
                error_log("Correo ya existe: " . $correo);
                return false;
            }
            
            $query = "INSERT INTO " . $this->table . "
                      (nombre_cuenta, apellido, email, contrasena, fecha_nacimiento, genero) 
                      VALUES (:nombre, :apellido, :correo, :contrasena, :fecha_nacimiento, :genero)";
            
            $stmt = $this->conn->prepare($query);
            
            //Vincular los parámetros
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":apellido", $apellido);
            $stmt->bindParam(":correo", $correo);
            $stmt->bindParam(":contrasena", $contraseña);
            $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
            $stmt->bindParam(":genero", $genero);
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                error_log("Usuario creado exitosamente: " . $correo);
                return true;
            } else {
                error_log("Error al ejecutar INSERT para: " . $correo);
                return false;
            }
            
        } catch (PDOException $e) {
            //Capturar error de duplicado
            if ($e->getCode() == 23000) { 
                error_log("Correo duplicado (catch): " . $correo);
                return false;
            }
            error_log("Error en crear usuario: " . $e->getMessage());
            return false;
        }
    }
    
    public function obtenerPasswordPorEmail($email) {
        try {
            $query = "SELECT contrasena FROM " . $this->table . " WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['contrasena'] : false;
            
        } catch (PDOException $e) {
            error_log("Error obteniendo contraseña: " . $e->getMessage());
            return false;
        }
    }
    
    public function obtenerUsuarioPorEmail($email) {
        try {
            $query = "SELECT id_cuenta, nombre_cuenta, apellido, email, fecha_nacimiento, genero FROM " . $this->table . " WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error obteniendo usuario: " . $e->getMessage());
            return false;
        }
    }
    
    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }
}
?>