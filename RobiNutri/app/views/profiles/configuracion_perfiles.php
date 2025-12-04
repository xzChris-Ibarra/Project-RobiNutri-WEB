
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RobiNutri - Registro de Perfiles</title>
    <link rel="stylesheet" href="/robinutri/public/css/configuracion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="page-container" style="padding-top: 50px;">
        <div class="registro-header">
            <img src="/robinutri/public/imagenes/Logo.png" alt="RobiNutri Logo" class="logo-img-config">
            <h1>Registro de Perfiles</h1>
        </div>

        <div class="registro-perfil-area">
            <div class="registro-inputs">
                <form id="registro-form">
                    <div class="input-row">
                        <input type="text" name="nombre" id="perfil-nombre" class="form-input blue-input small-input" placeholder="Nombre" required>
                        <input type="text" name="apellido" id="perfil-apellido" class="form-input blue-input small-input" placeholder="Apellido" required>
                        <div class="input-group-edad">
                            <label for="perfil-edad">Edad:</label>
                            <input type="number" name="edad" id="perfil-edad" class="form-input blue-input tiny-input" min="1" max="120" required>
                        </div>
                    </div>
                    
                    <div class="form-row-group">
                        <div class="input-group-vertical">
                            <label for="perfil-alergias" class="input-label">Alergias:</label>
                            <textarea name="alergias" id="perfil-alergias" class="form-input blue-input large-input" rows="4" placeholder="Especifique alergias..."></textarea>
                        </div>
                        <div class="input-group-vertical">
                            <label for="perfil-enfermedades" class="input-label">Enfermedades:</label>
                            <textarea name="enfermedades" id="perfil-enfermedades" class="form-input blue-input large-input" rows="4" placeholder="Especifique enfermedades crónicas..."></textarea>
                        </div>
                    </div>
                    
                    <div class="input-group-vertical">
                        <label for="perfil-observaciones" class="input-label">Observaciones:</label>
                        <textarea name="observaciones" id="perfil-observaciones" class="form-input blue-input large-input" rows="4" placeholder="Otras notas importantes..."></textarea>
                    </div>
                    
                    <button type="submit" class="create-profile-btn green-btn">Crear Perfil</button>
                </form>
            </div>
            
            <div class="perfiles-creados-lista" id="perfiles-creados-lista">
                <h3>Perfiles Creados</h3>
                <?php if (!empty($perfiles)): ?>
                    <?php foreach ($perfiles as $perfil): ?>
                        <div class="profile-display-item" data-id="<?= $perfil['id'] ?>">
                            <div class="profile-info">
                                <span class="profile-name">
                                    <strong><?= $perfil['nombre'] ?> <?= $perfil['apellido'] ?></strong>
                                </span>
                                <span class="profile-details">
                                    (Edad: <?= $perfil['edad'] ?> años)
                                </span>
                            </div>
                            <div class="profile-actions">
                                <i class="fas fa-pen edit-profile-icon" title="Editar perfil"></i>
                                <i class="fas fa-trash-alt delete-profile-icon" title="Eliminar perfil"></i>
                                <a href="/robinutri/index.php/chat?perfil_id=<?= $perfil['id'] ?>" 
                                    class="chat-btn" title="Iniciar Chat">
                                    <i class="fas fa-comments"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?><?php endif; ?>
            </div>
        </div>

        <a href="/robinutri/index.php/chat" class="green-btn continue-btn">
            <i class="fas fa-arrow-right"></i> Continuar al Chat
            </a>        
        <p class="footer-text-registro">
            RobiNutri es el chatbot que impulsa hábitos saludables en niños a 
            través de inteligencia artificial y educación divertida.
        </p>
    </div>

    <script src="/robinutri/public/js/script_config.js"></script>
</body>
</html>