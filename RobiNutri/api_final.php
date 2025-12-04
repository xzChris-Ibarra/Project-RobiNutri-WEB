<?php
header('Content-Type: application/json');

if (ob_get_level()) ob_clean();

$secretsPath = __DIR__ . '/config/secrets.php';
if (file_exists($secretsPath)) {
    require_once $secretsPath;
} else {
    define('OPENAI_API_KEY', '');
}
$OPENAI_API_KEY = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $mensaje = trim($_POST['mensaje'] ?? '');
    $chatId = $_POST['chat_id'] ?? '1';
    $perfilId = $_POST['perfil_id'] ?? null;
    
    if (empty($mensaje)) {
        throw new Exception('Mensaje vacío');
    }

    $contextoPerfil = "";
    if ($perfilId) {
        $contextoPerfil = obtenerContextoPerfil($perfilId);
    }

    $respuesta = obtenerRespuestaOpenAI($mensaje, $contextoPerfil, $OPENAI_API_KEY);
    
    echo json_encode([
        'success' => true,
        'bot_response' => $respuesta,
        'timestamp' => date('H:i:s'),
        'source' => 'openai'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;

function obtenerContextoPerfil($perfilId) {
    try {
        $configFile = __DIR__ . '/config/database.php';
        
        if (!file_exists($configFile)) {
            return "";
        }
        
        require_once $configFile;
        
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $query = "SELECT * FROM perfiles WHERE id = :id LIMIT 1"; 
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $perfilId]);
        $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($perfil) {
            $info = "INFORMACIÓN DEL PACIENTE: ";
            $info .= "Nombre: " . $perfil['nombre'] . " " . $perfil['apellido'] . ". ";
            $info .= "Edad: " . ($perfil['edad'] ?? '?') . " años. ";
            
            if (!empty($perfil['alergias'])) {
                $info .= "⚠️ ALERTA CRÍTICA: El niño es ALÉRGICO a: " . strtoupper($perfil['alergias']) . ". ";
                $info .= "PROHIBIDO sugerir estos alimentos. Si el usuario los pide, NIEGATE y advierte el riesgo.";
            }
            
            if (!empty($perfil['enfermedades'])) {
                $info .= "Condiciones médicas: " . $perfil['enfermedades'] . ". ";
            }
            
            return $info;
        }
        
    } catch (Exception $e) {
    }
    return "";
}

function obtenerRespuestaOpenAI($mensajeUsuario, $contextoPerfil, $apiKey) {
    if (empty($apiKey)) {
        return generarRespuestaPredefinida($mensajeUsuario);
    }
    
    $personalidad = "Eres RobiNutri, un nutricionista experto en pediatría.
    
    $contextoPerfil
    
    REGLAS DE SEGURIDAD:
    1. Tu prioridad absoluta es la salud del niño.
    2. REVISA SIEMPRE LAS ALERGIAS en la información del paciente antes de responder.
    3. Si el usuario pide un ingrediente al que el niño es alérgico, DEBES NEGARTE AMABLEMENTE, explicar el riesgo de reacción alérgica y sugerir un sustituto seguro.
    4. Sé amable, claro y usa emojis.";

    try {
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $personalidad],
                ['role' => 'user', 'content' => $mensajeUsuario]
            ],
            'max_tokens' => 500,
            'temperature' => 0.5 
        ];
        
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
        
        if (curl_errno($ch)) throw new Exception(curl_error($ch));
        
        curl_close($ch);
        
        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'] ?? "Error procesando respuesta.";
        
    } catch (Exception $e) {
        return generarRespuestaPredefinida($mensajeUsuario);
    }
}

function generarRespuestaPredefinida($mensajeUsuario) {
    $mensaje = strtolower(trim($mensajeUsuario));
    
    if (strpos($mensaje, 'hola') !== false) {
        return "¡Hola! 👋 Soy RobiNutri. ¿En qué puedo ayudarte con la alimentación de hoy?";
    }

     if (strpos($mensaje, 'fruta') !== false) {
        return "🍎 **FRUTAS ESENCIALES** 🍌\n\n• **Plátanos**: Potasio para músculos\n• **Manzanas**: Fibra para digestión  \n• **Naranjas**: Vitamina C para defensas\n• **Fresas**: Antioxidantes\n\n📊 **Porciones recomendadas:**\n- 8-12 años: 3-4 porciones/día";
    }
    
    if (strpos($mensaje, 'verdura') !== false) {
        return "🥦 **VERDURAS NUTRIENTES** 🥕\n\n• **Zanahorias**: Vitamina A (visión)\n• **Brócoli**: Hierro y calcio\n• **Espinacas**: Ácido fólico\n• **Calabazas**: Fibra y vitaminas\n\n💡 **Tip**: Sirve las verduras en formas divertidas.";
    }
    
    if (strpos($mensaje, 'alergia') !== false) {
        return "⚠️ **ALERGIAS ALIMENTARIAS COMUNES**\n\n• Leche • Huevos • Maní • Mariscos • Trigo\n\n🔍 Si sospechas de alergia, consulta con pediatra.";
    }
    
    if (strpos($mensaje, 'receta') !== false) {
        return "👩‍🍳 **RECETA FÁCIL**\n\n**🍌 Panqueques de Plátano**\n• 1 plátano\n• 1 huevo  \n• 2 cucharadas de avena\n\nMachacar, mezclar y cocinar. ¡Listo en 10 minutos!";
    }
    
    return "🤖 Estoy teniendo problemas para conectarme a mi cerebro central, pero puedo darte consejos básicos de nutrición. ¿Qué necesitas?";
}
?>