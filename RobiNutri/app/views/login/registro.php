<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RobiNutri - Crear Cuenta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;700&family=Quicksand:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/robinutri/public/css/registro_style.css">
</head>
<body>

    <main class="register-container">
        
        <header class="header-logo">
            <img src="https://raw.githubusercontent.com/xzChris-Ibarra/Project-RobiNutri-WEB/refs/heads/main/Project%20RobiNutri/img/Logotipo%20Profesional%20RobiNutri.png" alt="RobiNutri Logo" class="logo-image">
        </header>

        <div class="form-container">
            <form id="registerForm" method="POST" action="/robinutri/index.php/login/auth">
                
                <div class="form-row">
                    <div class="form-group half-width">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" placeholder="Tu nombre" name="nombre" required>
                    </div>
                    <div class="form-group half-width">
                        <label for="apellido">Apellido</label>
                        <input type="text" id="apellido" placeholder="Tu apellido" name="apellido" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Fecha de nacimiento</label>
                    <span class="helper-text">+18 obligatorio.</span>
                    <div class="form-row date-fields">
                        
                        <select id="dob-day" name="dia" required>
                            <option value="">Día</option>
                            </select>
                        
                        <select id="dob-month" name="mes" required>
                            <option value="">Mes</option>
                        </select>
                        
                        <select id="dob-year" name="anio" required>
                            <option value="">Año</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="genero">Género</label>
                    <select id="genero" name="genero" required>
                        <option value="">Selecciona tu género</option>
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                        <option value="otro">Otro</option>
                        <option value="no-especificar">Prefiero no decirlo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Correo</label>
                    <input type="email" id="email" placeholder="ejemplo@correo.com" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" placeholder="Crea una contraseña segura" name="password" required>
                </div>
                
                <input type="hidden" name="accion" value="register">
                
                <button type="submit">Registrarte</button>
            </form>
            
            <p class="login-link">
                <a href="/robinutri/login">¡Ya tengo una cuenta!</a>
            </p>
        </div>

    </main>

    <footer>
        <p>💡 RobiNutri es el chatbot que impulsa hábitos saludables.</p>
    </footer>

    <script src="/robinutri/public/js/registro_script.js"></script>

</body>
</html>