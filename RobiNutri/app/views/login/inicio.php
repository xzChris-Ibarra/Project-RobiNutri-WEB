<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RobiNutri - Iniciar Sesión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;700&family=Paytone+One&family=Quicksand:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/robinutri/public/css/inicio_style.css">
</head>
<body>

    <main class="login-container">
        
        <section class="info-section">
            
            <img src="https://raw.githubusercontent.com/xzChris-Ibarra/Project-RobiNutri-WEB/refs/heads/main/Project%20RobiNutri/img/Logotipo%20Nombre%20RobiNutri.png" alt="Logotipo nombre RobiNutri" class="logo-image">

            <div class="about-box">
                <h2>🤖 ¿Qué es RobiNutri? 🍎</h2>
                <p>
                    RobiNutri es un chatbot educativo e interactivo diseñado para guiar a niños de 8 a 12 años en el aprendizaje de <strong>hábitos alimenticios saludables</strong> de una forma divertida, amigable y segura.
                </p>
                <p>
                    A través de conversaciones dinámicas, consejos personalizados y contenido adaptado a su edad, RobiNutri enseña sobre nutrición, <strong>alimentos, beneficios y equilibrio alimenticio</strong>, ayudando a crear conciencia sobre la importancia de una buena alimentación desde temprana edad.
                </p>
                <p>
                    Este proyecto nace con el propósito de <strong>mejorar los hábitos nutricionales infantiles en Mexicali</strong>, fomentando la curiosidad y el aprendizaje mediante tecnología e inteligencia artificial. Además, RobiNutri trabaja en conjunto con <strong>especialistas en nutrición infantil</strong> para garantizar información confiable, accesible y actualizada.
                </p>
                <p><strong>💡 Misión:</strong> Promover una alimentación saludable en los niños mediante el uso de la tecnología educativa.</p>
                <p><strong>🌱 Visión:</strong> Convertirse en una herramienta digital de referencia para la educación nutricional infantil en México.</p>
                <p><strong>❤️ Valores:</strong> Educación, salud, inclusión, accesibilidad y bienestar infantil.</p>
            </div>
        </section>
        
        <section class="login-area">
            
            <div class="flex justify-center">
                <img src="https://raw.githubusercontent.com/xzChris-Ibarra/Project-RobiNutri-WEB/refs/heads/main/Project%20RobiNutri/img/Logotipo%20Mascota%20RobiNutri.png" alt="Mascota RobiNutri saludando rodeado de frutas y verduras" class="robot-image">
            </div>

            <div class="login-form-container">
                <form method="POST" action="/robinutri/index.php/login/auth" class="login-form" id="loginForm">
                    <div class="form-group">
                        <label for="email">Correo</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <input type="hidden" name="accion" value="login">
                    <button type="submit">Iniciar Sesión</button>
                </form>
                <p class="register-link">
                    ¿No tienes una cuenta? <a href="/robinutri/index.php/login/registro">Regístrate aquí</a>
                </p>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy; RobiNutri - Todos los derechos reservados, 2025.</p>
    </footer>

    <script src="/robinutri/public/js/script.js"></script>
</body>
</html>