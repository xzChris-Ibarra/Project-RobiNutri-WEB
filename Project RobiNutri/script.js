document.addEventListener("DOMContentLoaded", function() {
    
    const loginForm = document.getElementById("loginForm");

    if (loginForm) {
        loginForm.addEventListener("submit", function(event) {
            // Previene el envío real del formulario
            event.preventDefault(); 
            
            // aquí irá la lógica para enviar los datos
            // a PHP usando fetch() o para validaciones.
            
            console.log("Formulario enviado (simulación)");
            
            // Simulación de éxito: redirigir a la página principal
            // window.location.href = "chat.html"; 
        });
    }

});