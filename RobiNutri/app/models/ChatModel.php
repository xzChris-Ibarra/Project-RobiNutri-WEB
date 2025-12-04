<?php
require_once __DIR__ . '/database.php';

class ChatModel {
    private $db;
    private $tableChats = 'chats';
    private $tableMensajes = 'mensajes';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    private function getUsuarioId() {
        if (isset($_SESSION['usuario_id'])) {
            return $_SESSION['usuario_id'];
        }
        return 0; //Usuario invitado
    }

    public function getChatsByProfile($perfilId) {
        try {
            $usuarioId = $this->getUsuarioId();            
            $query = "SELECT * FROM " . $this->tableChats . " 
                    WHERE perfil_id = :perfil_id AND usuario_id = :usuario_id
                    ORDER BY fecha_ultimo_mensaje DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':perfil_id' => $perfilId,
                ':usuario_id' => $usuarioId
            ]);
            
            $result = $stmt->fetchAll();
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error getting chats: " . $e->getMessage());
            return [];
        }
    }

    public function createChat($perfilId, $nombreChat = null) {
        try {
            $usuarioId = $this->getUsuarioId();
            
            if (!$nombreChat) {
                //Obtener nombre del perfil
                $queryPerfil = "SELECT nombre FROM perfiles WHERE id = :perfil_id AND usuario_id = :usuario_id";
                $stmtPerfil = $this->db->prepare($queryPerfil);
                $stmtPerfil->execute([
                    ':perfil_id' => $perfilId,
                    ':usuario_id' => $usuarioId
                ]);
                $perfil = $stmtPerfil->fetch();
                
                //CONTAR CHATS EXISTENTES PARA ESTE PERFIL
                $queryCount = "SELECT COUNT(*) as total FROM " . $this->tableChats . " 
                            WHERE perfil_id = :perfil_id AND usuario_id = :usuario_id";
                $stmtCount = $this->db->prepare($queryCount);
                $stmtCount->execute([
                    ':perfil_id' => $perfilId,
                    ':usuario_id' => $usuarioId
                ]);
                $count = $stmtCount->fetch();
                
                $numeroChat = $count['total'] + 1;
                $nombreChat = $perfil ? 'Chat ' . $numeroChat . ' - ' . $perfil['nombre'] : 'Chat ' . $numeroChat;
            }
            
            $query = "INSERT INTO " . $this->tableChats . " 
                    (usuario_id, perfil_id, nombre_chat) 
                    VALUES (:usuario_id, :perfil_id, :nombre_chat)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':perfil_id' => $perfilId,
                ':nombre_chat' => $nombreChat
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Error creating chat: " . $e->getMessage());
            return false;
        }
    }

    public function getMessages($chatId) {
        try {
            $usuarioId = $this->getUsuarioId();
            
            $query = "SELECT m.* FROM " . $this->tableMensajes . " m
                     JOIN " . $this->tableChats . " c ON m.chat_id = c.id
                     WHERE m.chat_id = :chat_id AND c.usuario_id = :usuario_id
                     ORDER BY m.fecha_creacion ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':chat_id' => $chatId,
                ':usuario_id' => $usuarioId
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting messages: " . $e->getMessage());
            return [];
        }
    }

    public function saveMessage($chatId, $mensaje, $tipo) {
        try {
            $usuarioId = $this->getUsuarioId();
            
            //Verificar que el chat pertenece al usuario
            $queryVerify = "SELECT id FROM " . $this->tableChats . " 
            WHERE id = :chat_id AND usuario_id = :usuario_id";
            $stmtVerify = $this->db->prepare($queryVerify);
            $stmtVerify->execute([
                ':chat_id' => $chatId,
                ':usuario_id' => $usuarioId
            ]);
            
            if (!$stmtVerify->fetch()) {
                return false; //Chat no pertenece al usuario
            }
            
            //Guardar mensaje
            $query = "INSERT INTO " . $this->tableMensajes . " 
                     (chat_id, mensaje, tipo) 
                     VALUES (:chat_id, :mensaje, :tipo)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':chat_id' => $chatId,
                ':mensaje' => $mensaje,
                ':tipo' => $tipo
            ]);

            //Actualizar fecha del chat
            $updateQuery = "UPDATE " . $this->tableChats . " 
                           SET fecha_ultimo_mensaje = CURRENT_TIMESTAMP 
                           WHERE id = :chat_id";
            $stmt = $this->db->prepare($updateQuery);
            $stmt->execute([':chat_id' => $chatId]);

            return true;
        } catch (PDOException $e) {
            error_log("Error saving message: " . $e->getMessage());
            return false;
        }
    }

    public function getChatById($chatId) {
        try {
            $usuarioId = $this->getUsuarioId();
            
            $query = "SELECT c.*, p.nombre, p.apellido 
                     FROM " . $this->tableChats . " c
                     JOIN perfiles p ON c.perfil_id = p.id
                     WHERE c.id = :chat_id AND c.usuario_id = :usuario_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':chat_id' => $chatId,
                ':usuario_id' => $usuarioId
            ]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting chat: " . $e->getMessage());
            return null;
        }
    }
    public function deleteChat($chatId) {
        try {
            $usuarioId = $this->getUsuarioId();
            
            //Verificar que el chat pertenece al usuario
            $queryVerify = "SELECT id FROM " . $this->tableChats . " 
                        WHERE id = :chat_id AND usuario_id = :usuario_id";
            $stmtVerify = $this->db->prepare($queryVerify);
            $stmtVerify->execute([
                ':chat_id' => $chatId,
                ':usuario_id' => $usuarioId
            ]);
            
            if (!$stmtVerify->fetch()) {
                return false; //Chat no pertenece al usuario
            }
            
            //eliminar chat
            $query = "DELETE FROM " . $this->tableChats . " 
                    WHERE id = :chat_id AND usuario_id = :usuario_id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':chat_id' => $chatId,
                ':usuario_id' => $usuarioId
            ]);
            
        } catch (PDOException $e) {
            error_log("Error deleting chat: " . $e->getMessage());
            return false;
        }
    }
    public function getChatHistory($chatId, $limit = 10) {
        try {
            $usuarioId = $this->getUsuarioId();
            
            //Verificar que el chat pertenece al usuario
            $queryVerify = "SELECT id FROM " . $this->tableChats . " 
                        WHERE id = :chat_id AND usuario_id = :usuario_id";
            $stmtVerify = $this->db->prepare($queryVerify);
            $stmtVerify->execute([':chat_id' => $chatId, ':usuario_id' => $usuarioId]);
            
            if (!$stmtVerify->fetch()) {
                return [];
            }
            
            //Obtener historial de mensajes
            $query = "SELECT mensaje, tipo, fecha_creacion 
                    FROM " . $this->tableMensajes . " 
                    WHERE chat_id = :chat_id 
                    ORDER BY fecha_creacion ASC 
                    LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':chat_id', $chatId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error getting chat history: " . $e->getMessage());
            return [];
        }
    }
}
?>