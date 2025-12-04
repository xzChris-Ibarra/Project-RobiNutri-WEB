<?php
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/ChatModel.php';
require_once __DIR__ . '/../models/ProfileModel.php';

class ChatController {
    private $chatModel;
    private $profileModel;
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->chatModel = new ChatModel();
        $this->profileModel = new ProfileModel();
    }
    public function index() {
        //VERIFICAR SI SE SOLICITA NUEVO CHAT
        $nuevoChat = $_GET['nuevo_chat'] ?? false;
        $perfilIdParam = $_GET['perfil_id'] ?? null;
        
        if ($nuevoChat && $perfilIdParam && isset($_SESSION['usuario_id'])) {
            //Crear nuevo chat forzadamente
            $chatId = $this->chatModel->createChat($perfilIdParam);
            if ($chatId) {
                //Redirigir al nuevo chat
                header('Location: /robinutri/index.php/chat?perfil_id=' . $perfilIdParam);
                exit;
            }
        }
        
        //VERIFICAR SI VIENE UN PERFIL ESPECÍFICO
        if ($perfilIdParam && isset($_SESSION['usuario_id'])) {
            //Usar directamente el perfil de la URL
            $perfil = $this->profileModel->getById($perfilIdParam);
            if ($perfil) {
                $this->mostrarChatPrincipal($perfilIdParam);
                return;
            }
        }
        
        //Si no, continuar normal
        $this->mostrarChatPrincipal();
    }
    private function mostrarChatPrincipal($perfilIdForzado = null) {
        $usuarioLogueado = isset($_SESSION['usuario_id']);
        
        //Obtener perfiles
        $perfiles = $usuarioLogueado ? $this->profileModel->getAll() : [];
        
        $perfilActivo = null;
        $chatActivo = null;
        $mensajes = [];

        //CASO 1: Usuario logueado CON perfiles
        if ($usuarioLogueado && !empty($perfiles)) {
            //Determinar perfil activo
            if ($perfilIdForzado) {
                foreach ($perfiles as $perfil) {
                    if ($perfil['id'] == $perfilIdForzado) {
                        $perfilActivo = $perfil;
                        break;
                    }
                }
            }
            
            if (!$perfilActivo) {
                $perfilActivo = $perfiles[0];
            }
            
            //GARANTIZAR QUE EXISTE UN CHAT PARA ESTE PERFIL
            $chats = $this->chatModel->getChatsByProfile($perfilActivo['id']);
            
            if (empty($chats)) {
                //CREAR CHAT EN LA BASE DE DATOS
                $chatId = $this->chatModel->createChat($perfilActivo['id']);
                
                if ($chatId) {
                    $chatActivo = $this->chatModel->getChatById($chatId);
                    //Mensaje de bienvenida
                    $mensajeBienvenida = "¡Hola! Soy RobiNutri. Estoy aquí para ayudarte con la nutrición de " . $perfilActivo['nombre'] . ".";
                    $this->chatModel->saveMessage($chatId, $mensajeBienvenida, 'bot');
                }
            } else {
                $chatActivo = $chats[0];
            }
            
            if ($chatActivo) {
                $mensajes = $this->chatModel->getMessages($chatActivo['id']);
            }
            
        } 
        // CASO 2: Usuario logueado SIN perfiles (Nuevo Usuario)
        elseif ($usuarioLogueado && empty($perfiles)) {
            // No asignar perfil activo, pero NO es invitado
            $perfilActivo = null; 
            // Aquí se puede mostrar un mensaje introductorio vacío
        }
        // CASO 3: Invitado (No logueado)
        else {
            $perfilActivo = ['nombre' => 'Invitado', 'edad' => '', 'id' => 0];
        }

        $datosVista = [
            'usuarioLogueado' => $usuarioLogueado,
            'perfilActivo' => $perfilActivo,
            'chatActivo' => $chatActivo,
            'mensajes' => $mensajes,
            'perfiles' => $perfiles
        ];
        
        require_once __DIR__ . '/../views/chat/index.php';
    }
    
    public function sendMessage() {
        // DEBUG TEMPORAL
        error_log("=== DEBUG CHATCONTROLLER ===");
        error_log("Método: " . $_SERVER['REQUEST_METHOD']);
        error_log("Chat ID: " . ($_POST['chat_id'] ?? 'NO'));
        error_log("Mensaje: " . ($_POST['mensaje'] ?? 'NO'));
        error_log("Perfil ID: " . ($_POST['perfil_id'] ?? 'NO'));
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $chatId = $_POST['chat_id'] ?? null;
            $mensaje = trim($_POST['mensaje'] ?? '');
            $perfilId = $_POST['perfil_id'] ?? null;
            
            if ($chatId && !empty($mensaje)) {
                //Guardar mensaje del usuario
                $this->chatModel->saveMessage($chatId, $mensaje, 'user');
                
                //Obtener información del perfil para contexto
                $perfilInfo = null;
                if ($perfilId) {
                    $perfilInfo = $this->profileModel->getById($perfilId);
                }
                
                //Generar respuesta de IA
                $respuestaBot = $this->generarRespuestaInteligente($mensaje, $perfilInfo);
                
                //Guardar respuesta del bot
                $this->chatModel->saveMessage($chatId, $respuestaBot, 'bot');
                
                echo json_encode([
                    'success' => true,
                    'bot_response' => $respuestaBot
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Mensaje vacío o chat no especificado'
                ]);
            }
        }
    }
    public function loadMessages() {
        header('Content-Type: application/json');
        
        $chatId = $_GET['chat_id'] ?? null;
        
        if ($chatId) {
            $mensajes = $this->chatModel->getMessages($chatId);
            
            echo json_encode([
                'success' => true,
                'mensajes' => $mensajes
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Chat no especificado'
            ]);
        }
    }
    public function verChatsPorPerfil() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'No logueado']);
            return;
        }
        
        $perfilId = $_GET['perfil_id'] ?? null;
        
        if ($perfilId) {
            $chats = $this->chatModel->getChatsByProfile($perfilId);
            echo json_encode([
                'success' => true,
                'chats' => $chats,
                'total' => count($chats)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Perfil no especificado']);
        }
    }
    public function getChatsUsuario() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'No logueado']);
            exit;
        }
        //OBTENER EL PERFIL ESPECÍFICO SI SE SOLICITA
        $perfilIdFiltro = $_GET['perfil_id'] ?? null;

        //SIN DEBUG, SIN COMENTARIOS
        $perfiles = $this->profileModel->getAll();
        $todosLosChats = [];
        
        foreach ($perfiles as $perfil) {
            $chatsDelPerfil = $this->chatModel->getChatsByProfile($perfil['id']);
            foreach ($chatsDelPerfil as $chat) {
                $chat['perfil_nombre'] = $perfil['nombre'];
                $chat['perfil_id'] = $perfil['id']; //AGREGAR ID DEL PERFIL

                $todosLosChats[] = $chat;
            }
        }
        
        usort($todosLosChats, function($a, $b) {
            return strtotime($b['fecha_ultimo_mensaje']) - strtotime($a['fecha_ultimo_mensaje']);
        });
        
        //SOLO JSON, NADA MÁS
        echo json_encode([
            'success' => true, 
            'chats' => $todosLosChats
        ]);
        exit;
    }
    public function createChat() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
            return;
        }
        
        $perfilId = $_POST['perfil_id'] ?? null;
        
        if ($perfilId) {
            $chatId = $this->chatModel->createChat($perfilId);
            
            if ($chatId) {
                echo json_encode([
                    'success' => true,
                    'chat_id' => $chatId,
                    'message' => 'Nuevo chat creado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear el chat en la base de datos'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se especificó el perfil'
            ]);
        }
    }
    public function deleteChat() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'No logueado']);
            return;
        }
        
        $chatId = $_POST['chat_id'] ?? null;
        
        if ($chatId) {
            try {
                //INICIALIZAR LA CONEXIÓN A LA BASE DE DATOS
                if (!$this->db) {
                    $database = new Database();
                    $this->db = $database->getConnection();
                }
                
                //Primero eliminar los mensajes del chat (por la foreign key)
                $queryMensajes = "DELETE FROM mensajes WHERE chat_id = :chat_id";
                $stmtMensajes = $this->db->prepare($queryMensajes);
                $stmtMensajes->execute([':chat_id' => $chatId]);
                
                //Luego eliminar el chat
                $queryChat = "DELETE FROM chats WHERE id = :chat_id AND usuario_id = :usuario_id";
                $stmtChat = $this->db->prepare($queryChat);
                $result = $stmtChat->execute([
                    ':chat_id' => $chatId,
                    ':usuario_id' => $_SESSION['usuario_id']
                ]);
                
                if ($result && $stmtChat->rowCount() > 0) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Chat eliminado exitosamente'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Chat no encontrado o no tienes permisos'
                    ]);
                }
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al eliminar el chat: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'ID de chat no especificado'
            ]);
        }
    }
    private function eliminarMensajesChat($chatId) {
        try {
            $query = "DELETE FROM mensajes WHERE chat_id = :chat_id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':chat_id' => $chatId]);
        } catch (PDOException $e) {
            error_log("Error eliminando mensajes: " . $e->getMessage());
            return false;
        }
    }
    public function renameChat() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'No logueado']);
            return;
        }
        
        $chatId = $_POST['chat_id'] ?? null;
        $nuevoNombre = trim($_POST['nuevo_nombre'] ?? '');
        
        if ($chatId && $nuevoNombre) {
            try {
                //INICIALIZAR LA CONEXIÓN A LA BASE DE DATOS
                if (!$this->db) {
                    $database = new Database();
                    $this->db = $database->getConnection();
                }
                
                //Verificar que el chat pertenece al usuario
                $queryVerify = "SELECT id FROM chats WHERE id = :chat_id AND usuario_id = :usuario_id";
                $stmtVerify = $this->db->prepare($queryVerify);
                $stmtVerify->execute([
                    ':chat_id' => $chatId,
                    ':usuario_id' => $_SESSION['usuario_id']
                ]);
                
                if ($stmtVerify->fetch()) {
                    //Actualizar nombre del chat
                    $query = "UPDATE chats SET nombre_chat = :nombre_chat WHERE id = :chat_id";
                    $stmt = $this->db->prepare($query);
                    $result = $stmt->execute([
                        ':nombre_chat' => $nuevoNombre,
                        ':chat_id' => $chatId
                    ]);
                    
                    if ($result) {
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Chat renombrado exitosamente'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false, 
                            'message' => 'Error al renombrar el chat'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Chat no encontrado o no tienes permisos'
                    ]);
                }
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al renombrar el chat: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Datos incompletos'
            ]);
        }
    }   
    private function generarRespuestaInteligente($mensajeUsuario, $perfilInfo = null) {
         //DEBUG
        error_log("=== GENERANDO RESPUESTA IA ===");
        error_log("Mensaje usuario: " . $mensajeUsuario);
        
        //Preparar el contexto del perfil
        $contextoPerfil = "";
        if ($perfilInfo) {
            $contextoPerfil = "Información del niño: " . $perfilInfo['nombre'] . " " . $perfilInfo['apellido'] . 
                            ", " . $perfilInfo['edad'] . " años.";
            if (!empty($perfilInfo['alergias'])) {
                $contextoPerfil .= " Alergias: " . $perfilInfo['alergias'] . ".";
            }
            if (!empty($perfilInfo['enfermedades'])) {
                $contextoPerfil .= " Enfermedades: " . $perfilInfo['enfermedades'] . ".";
            }
        }
        
        //Preparar el prompt para la IA
        $prompt = "Eres RobiNutri, un nutricionista infantil especializado. 
        {$contextoPerfil}
        
        Responde de manera amigable, profesional y apropiada para padres de familia.
        Sé conciso pero informativo. Usa emojis relevantes ocasionalmente.
        
        Pregunta del usuario: {$mensajeUsuario}";
        
        //Llamar a OpenAI
        return $this->llamarOpenAI($prompt, $apiKey);
    }
    public function getChatHistory($chatId, $limit = 50) {
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
            return []; //Chat no pertenece al usuario
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
    private function llamarOpenAI($prompt, $apiKey) {
        try {
            error_log("=== LLAMANDO OPENAI ===");
            
            //VERIFICACIÓN CRÍTICA DE API KEY
            if ($apiKey === '' || empty($apiKey) || strlen($apiKey) < 20) {
                error_log("❌ API KEY NO CONFIGURADA CORRECTAMENTE");
                error_log("Key recibida: " . substr($apiKey ?: 'VACÍA', 0, 10) . "...");
                return $this->respuestaPredefinida($prompt);
            }
            
            error_log("✅ API Key parece válida, longitud: " . strlen($apiKey));
            
            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system', 
                        'content' => 'Eres RobiNutri, un asistente de nutrición infantil. Responde de manera amigable, profesional y útil para padres.'
                    ],
                    [
                        'role' => 'user', 
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7
            ];
            
            error_log("Enviando request a OpenAI...");
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            error_log("OpenAI Response - HTTP Code: " . $httpCode);
            
            if ($curlError) {
                error_log("❌ cURL Error: " . $curlError);
                return $this->respuestaPredefinida($prompt);
            }
            
            if ($httpCode !== 200) {
                error_log("❌ HTTP Error: " . $httpCode);
                error_log("Response: " . $response);
                return $this->respuestaPredefinida($prompt);
            }
            
            $result = json_decode($response, true);
            
            if (isset($result['choices'][0]['message']['content'])) {
                error_log("✅ Respuesta IA obtenida exitosamente");
                return $result['choices'][0]['message']['content'];
            } else {
                error_log("❌ No se pudo obtener respuesta de IA");
                error_log("Response structure: " . print_r($result, true));
                return $this->respuestaPredefinida($prompt);
            }
            
        } catch (Exception $e) {
            error_log("❌ Exception llamando a OpenAI: " . $e->getMessage());
            return $this->respuestaPredefinida($prompt);
        }
    }
    public function test() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'ChatController test funciona',
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
        exit;
    }
}
?>