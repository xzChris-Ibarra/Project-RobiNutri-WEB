<?php
require_once __DIR__ . '/Database.php';

class ProfileModel {
    private $db;
    private $table = 'perfiles';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    //MÉTODO PRIVADO SIN session_start()
    private function getUsuarioId() {
        //Solo verificar si la sesión está activa y tiene usuario_id
        if (isset($_SESSION['usuario_id'])) {
            return $_SESSION['usuario_id'];
        }
        return 0; //Usuario invitado
    }

    public function getAll() {
        try {
            $usuarioId = $this->getUsuarioId();
            
            $query = "SELECT * FROM " . $this->table . " 
                     WHERE usuario_id = :usuario_id 
                     ORDER BY fecha_creacion DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':usuario_id' => $usuarioId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting profiles: " . $e->getMessage());
            return [];
        }
    }

    public function create($data) {
        try {
            $usuarioId = $this->getUsuarioId();
            
            $query = "INSERT INTO " . $this->table . " 
                     (usuario_id, nombre, apellido, edad, alergias, enfermedades, observaciones) 
                     VALUES (:usuario_id, :nombre, :apellido, :edad, :alergias, :enfermedades, :observaciones)";
            
            $stmt = $this->db->prepare($query);
            
            $result = $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':nombre' => $data['nombre'],
                ':apellido' => $data['apellido'],
                ':edad' => $data['edad'],
                ':alergias' => $data['alergias'] ?? '',
                ':enfermedades' => $data['enfermedades'] ?? '',
                ':observaciones' => $data['observaciones'] ?? ''
            ]);

            return $result;
        } catch (PDOException $e) {
            error_log("Error creating profile: " . $e->getMessage());
            return false;
        }
    }

    public function getById($id) {
        try {
            $usuarioId = $this->getUsuarioId();
            
            $query = "SELECT * FROM " . $this->table . " 
                     WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id' => $id,
                ':usuario_id' => $usuarioId
            ]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting profile by ID: " . $e->getMessage());
            return null;
        }
    }

    public function delete($id) {
        try {
            $usuarioId = $this->getUsuarioId();
            
            $query = "DELETE FROM " . $this->table . " 
                     WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':id' => $id,
                ':usuario_id' => $usuarioId
            ]);
        } catch (PDOException $e) {
            error_log("Error deleting profile: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $usuarioId = $this->getUsuarioId();
            
            $query = "UPDATE " . $this->table . " 
                    SET nombre = :nombre, apellido = :apellido, edad = :edad, 
                        alergias = :alergias, enfermedades = :enfermedades, 
                        observaciones = :observaciones 
                    WHERE id = :id AND usuario_id = :usuario_id";
            
            $stmt = $this->db->prepare($query);
            
            $result = $stmt->execute([
                ':nombre' => $data['nombre'],
                ':apellido' => $data['apellido'],
                ':edad' => $data['edad'],
                ':alergias' => $data['alergias'] ?? '',
                ':enfermedades' => $data['enfermedades'] ?? '',
                ':observaciones' => $data['observaciones'] ?? '',
                ':id' => $id,
                ':usuario_id' => $usuarioId
            ]);

            return $result;
        } catch (PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            return false;
        }
    }

    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }
}
?>