document.addEventListener("DOMContentLoaded", function() {

    // --- Rellena los campos de Fecha de Nacimiento ---
    const daySelect = document.getElementById("dob-day");
    const monthSelect = document.getElementById("dob-month");
    const yearSelect = document.getElementById("dob-year");

    const months = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];

    // Rellenar Días (1-31)
    for (let i = 1; i <= 31; i++) {
        const option = document.createElement("option");
        option.value = i;
        option.textContent = i;
        daySelect.appendChild(option);
    }

    // Rellenar Meses
    months.forEach((month, index) => {
        const option = document.createElement("option");
        option.value = index + 1; // 1-12
        option.textContent = month;
        monthSelect.appendChild(option);
    });

    // Rellenar Años (Ej: últimos 100 años)
    const currentYear = new Date().getFullYear();
    const startYear = currentYear - 100;
    const endYear = currentYear - 18; // Para el requisito de +18

    for (let i = endYear; i >= startYear; i--) {
        const option = document.createElement("option");
        option.value = i;
        option.textContent = i;
        yearSelect.appendChild(option);
    }
    // "placeholder" al año por si el usuario es más joven
    // y requiere ayuda parental
    const yearPlaceholder = document.createElement("option");
    yearPlaceholder.value = "";
    yearPlaceholder.textContent = "Año";
    yearSelect.prepend(yearPlaceholder);
    yearSelect.value = ""; // Seleccionar el placeholder


    // --- Manejo del Formulario ---
    const registerForm = document.getElementById("registerForm");

    if (registerForm) {
        registerForm.addEventListener("submit", function(event) {

            // ------------------- Previene el envío real del formulario por ahora ---------------------------
            event.preventDefault(); 
            
            // ----------------- Aquí irá la lógica para enviar los datos a PHP ------------------
            console.log("Formulario de registro enviado (simulación)");
        });
    }

});