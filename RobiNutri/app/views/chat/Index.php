<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RobiNutri - Chat Principal</title>
    <link rel="stylesheet" href="/robinutri/public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div id="test-element" style="display: none;">
    chats-list existe: <?php echo isset($chatsList) ? 'SÍ' : 'NO'; ?>
</div>
    <!-- Sidebar de Historial -->
    <div id="history-sidebar" class="sidebar">
        <div class="sidebar-header">
            <div class="menu-toggle-icon">
                <i class="fas fa-bars"></i>
            </div>
            <h1>Historial de chat</h1>
        </div>
        
        <?php if (isset($_SESSION['usuario_id']) && !empty($perfiles)): ?>
            <a href="#" class="new-chat" id="new-chat-btn" data-perfil-id="<?php echo $perfilActivo['id'] ?? ''; ?>">
                + Nuevo chat
            </a>
        <?php endif; ?>

        <div class="sidebar-section recents">
            <h2>Conversaciones</h2>
            <div id="chats-list">
                <div class="loading-chats">Cargando conversaciones...</div>
            </div>
        </div>
        <!-- PLANTILLA PARA CHATS - OCULTA -->
            <template id="chat-template">
                <div class="chat-history-item">
                    <div class="chat-content">
                        <div class="chat-title"></div>
                        <div class="chat-profile"></div>
                        <div class="chat-date"></div>
                    </div>
                    <div class="chat-actions">
                        <button class="rename-chat-btn" data-chat-id="" title="Renombrar chat">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="delete-chat-btn" data-chat-id="" title="Eliminar chat">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </template>

            <!-- MODAL PARA RENOMBRAR CHAT -->
            <div id="rename-modal" class="rename-modal">
                <div class="rename-modal-content">
                    <h3>Renombrar Chat</h3>
                    <input type="text" id="rename-input" class="rename-input" placeholder="Nuevo nombre del chat...">
                    <div class="rename-modal-buttons">
                        <button id="rename-cancel-btn" class="rename-cancel-btn">Cancelar</button>
                        <button id="rename-save-btn" class="rename-save-btn">Guardar</button>
                    </div>
                </div>
            </div>

        <div class="sidebar-footer">
            <a href="#" class="footer-item" id="help-link">
                <i class="fas fa-question-circle"></i>
                <span class="item-text">Ayuda</span>
            </a>
            <a href="/robinutri/index.php/profiles" class="footer-item">
                <i class="fas fa-cog"></i>
                <span class="item-text">Configurar Perfiles</span>
            </a>
        </div>
    </div>

    <div class="page-container">
        
        <!-- Botón perfiles -->
        <a href="#" class="top-right-button icon-circle" id="open-profiles-sidebar">
            <img src="/robinutri/public/img/Menu niños.png" alt="Abrir Perfiles">
        </a>
        
        <!-- Sidebar de perfiles -->
        <div id="profile-sidebar" class="profile-sidebar-container">
            <div class="profile-sidebar-content">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                <!-- USUARIO REGISTRADO -->
                <div class="login-section" style="padding: 20px; border-bottom: 2px solid #A390D3; margin-bottom: 20px; background: #f8f6ff; border-radius: 10px;">
                    <h3 style="margin-bottom: 15px; color: #333; text-align: center; font-family: 'Fredoka', sans-serif;">
                        <i class="fas fa-user" style="color: #8BC53F; margin-right: 8px;"></i>
                        ¡Hola, <?php echo $_SESSION['usuario_nombre']; ?>!
                    </h3>
                    <p style="text-align: center; color: #666;">Usuario Registrado</p>
                    
                    <a href="/robinutri/index.php/logout" 
                       style="display: block; text-align: center; padding: 10px; background: #e74c3c; color: white; 
                              text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 15px;
                              transition: background 0.3s; font-size: 0.9em;">
                       <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> 
                       Cerrar Sesión    
                    </a>
                </div>

                <div class="menu-header">
                    <a href="/robinutri/index.php/profiles" class="config-link" style="text-decoration: none;">
                        <div class="config-text">
                            <i class="fas fa-cog" style="color: #A390D3;"></i> 
                            <div>
                                <strong style="color: #333;">Configurar</strong>
                                <p style="color: #666; margin: 0;">Perfiles Infantiles</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="profile-list">
                    <h4 style="color: #333; margin-bottom: 15px; text-align: center;">Perfiles Activos</h4>
                    
                    <?php if (!empty($perfiles)): ?>
                        <?php foreach ($perfiles as $perfil): ?>
                            <div class="profile-item <?php echo $perfilActivo['id'] == $perfil['id'] ? 'active-profile' : ''; ?>" 
                                data-profile-id="<?php echo $perfil['id']; ?>"
                                onclick="cambiarPerfil(<?php echo $perfil['id']; ?>)"
                                style="padding: 12px; margin: 8px 0; background: <?php echo $perfilActivo['id'] == $perfil['id'] ? '#e8f5e8' : '#f0edff'; ?>; 
                                        border: 2px solid <?php echo $perfilActivo['id'] == $perfil['id'] ? '#8BC53F' : 'transparent'; ?>; 
                                        border-radius: 8px; cursor: pointer; transition: all 0.3s;">
                                <strong style="color: #333;"><?php echo $perfil['nombre'] . ' ' . $perfil['apellido']; ?></strong>
                                <br>
                                <small style="color: #666;"><?php echo $perfil['edad']; ?> años</small>
                                <?php if ($perfilActivo['id'] == $perfil['id']): ?>
                                    <br><small style="color: #8BC53F; font-weight: bold;">✓ Activo</small>
                                <?php endif; ?>
                                
                                <!-- CONTADOR DE CHATS -->
                                <?php
                                $chatsPerfil = array_filter($chats ?? [], function($chat) use ($perfil) {
                                    return $chat['perfil_id'] == $perfil['id'];
                                });
                                $totalChats = count($chatsPerfil);
                                ?>
                                <br><small style="color: #A390D3; font-size: 0.8em;">
                                    💬 <?php echo $totalChats; ?> chat<?php echo $totalChats != 1 ? 's' : ''; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #666; padding: 10px;">Aún no tienes perfiles</p>
                    <?php endif; ?>
                    
                    <a href="/robinutri/index.php/profiles" class="add_profile-button" 
                    style="display: block; text-align: center; padding: 12px; background: transparent; 
                            border: 2px dashed #A390D3; color: #333; text-decoration: none; 
                            border-radius: 10px; font-weight: bold; margin-top: 15px;">
                    <i class="fas fa-plus" style="margin-right: 8px; color: #A390D3;"></i> 
                    Agregar Perfil Infantil
                    </a>
                </div>

                <?php else: ?>
                <!-- USUARIO INVITADO -->
                <div class="login-section" style="padding: 20px; border-bottom: 2px solid #A390D3; margin-bottom: 20px; background: #fffacd; border-radius: 10px;">
                    <h3 style="margin-bottom: 15px; color: #333; text-align: center; font-family: 'Fredoka', sans-serif;">
                        <i class="fas fa-user-clock" style="color: #ff9800; margin-right: 8px;"></i>
                        Modo Invitado
                    </h3>
                    <p style="text-align: center; color: #666; font-size: 0.9em;">
                        ⚡ Chat básico disponible<br>
                        🔒 Funciones limitadas
                    </p>
                    
                    <a href="/robinutri/index.php/login" 
                       style="display: block; text-align: center; padding: 12px; background: #8BC53F; color: white; 
                              text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 10px;">
                       <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> 
                       Regístrate para más funciones
                    </a>
                </div>

                <div class="profile-list">
                    <div class="profile-item active-profile" 
                         style="padding: 12px; margin: 10px 0; background: #e8f5e8; border: 2px solid #8BC53F; border-radius: 8px; text-align: center;">
                        <strong>Chat de Prueba</strong><br>
                        <small style="color: #666;">Usuario Invitado</small>
                        <br><small style="color: #8BC53F; font-weight: bold;">✓ Activo</small>
                    </div>
                    
                    <div style="display: block; text-align: center; padding: 12px; background: #f5f5f5; 
                            border: 2px dashed #ccc; color: #999; border-radius: 10px; font-weight: bold; margin-top: 15px; cursor: not-allowed;">
                       <i class="fas fa-lock" style="margin-right: 8px; color: #ccc;"></i> 
                       Agregar Perfil (Solo usuarios registrados)
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="header-logo">
            <img src="/robinutri/public/img/Image (1).png" alt="RobiNutri Logo" class="logo-img">
        </div>
        
        <div class="chat-main-container">
            <div class="chat-header" style="text-align: center; margin-bottom: 20px; padding: 15px; background: #f8f6ff; border-radius: 10px;">
                <h2 style="color: #333; margin: 0;">
                    <i class="fas fa-robot" style="color: #A390D3;"></i>
                    
                    <?php if ($usuarioLogueado && empty($perfiles)): ?>
                        ¡Hola, <?php echo $_SESSION['usuario_nombre']; ?>! 👋
                    <?php elseif (isset($perfilActivo) && $perfilActivo['nombre'] !== 'Invitado'): ?>
                        Chat con <?php echo $perfilActivo['nombre']; ?>
                        <?php if (!empty($perfilActivo['edad'])): ?>
                            (<?php echo $perfilActivo['edad']; ?> años)
                        <?php endif; ?>
                    <?php else: ?>
                        Chat con RobiNutri (Invitado)
                    <?php endif; ?>

                </h2>
                
                <?php if ($usuarioLogueado && empty($perfiles)): ?>
                    <p style="color: #e67e22; margin: 10px 0 0 0; font-weight: bold;">
                        <i class="fas fa-arrow-left"></i> Crea el primer perfil de tu hijo/a en el menú lateral para comenzar.
                    </p>
                <?php elseif (isset($_SESSION['usuario_id']) && count($perfiles) > 1): ?>
                    <p style="color: #666; margin: 5px 0 0 0; font-size: 0.9em;">
                        <i class="fas fa-info-circle"></i> Usa el menú de perfiles para cambiar de niño
                    </p>
                <?php endif; ?>
            </div>

            <div class="chat-area" id="chat-area">
                <?php if (!empty($mensajes)): ?>
                    <?php foreach ($mensajes as $mensaje): ?>
                        <div class="chat-message <?php echo $mensaje['tipo'] === 'user' ? 'user-message' : 'bot-message'; ?>">
                            <p><?php echo nl2br(htmlspecialchars($mensaje['mensaje'])); ?></p>
                            <small style="opacity: 0.7; font-size: 0.8em;">
                                <?php echo date('H:i', strtotime($mensaje['fecha_creacion'])); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>

                <?php elseif ($usuarioLogueado && empty($perfiles)): ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="side-palette">
            <div class="palette-icon">
                <img src="/robinutri/public/img/manzana.png" alt="Icono Manzana">
            </div>
            <div class="palette-arrow">
                <img src="/robinutri/public/img/flechas.png" alt="Flechas de Retroceso">
            </div>
        </div>
    </div>

    <!-- BARRA DE INPUT -->
    <div class="chat-input-container-fixed">
        <div class="chat-input-container">
            <div class="chat-robot-icon">
                <img src="/robinutri/public/img/Image.png" alt="Robot Chat Icon">
            </div>
            <input type="text" class="chat-input" id="chat-input" 
                placeholder="Escribe tu pregunta sobre nutrición infantil..." 
                data-chat-id="<?php echo $chatActivo['id'] ?? '0'; ?>">
       
            <!-- DEBUG INFO -->
            <div style="display: none;">
                Chat ID: <?php echo $chatActivo['id'] ?? 'NO_CREADO'; ?><br>
                Perfil: <?php echo $perfilActivo['nombre'] ?? 'Invitado'; ?><br>
                Mensajes: <?php echo count($mensajes ?? []); ?>
            </div>
            <button class="send-button" id="send-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <script src="/robinutri/public/js/main_script.js"></script>
    <script src="/robinutri/public/js/chat.js"></script>
</body>
</html>