document.addEventListener("DOMContentLoaded", function() {

    //Rellena los campos de Fecha de Nacimiento
    const daySelect = document.getElementById("dob-day");
    const monthSelect = document.getElementById("dob-month");
    const yearSelect = document.getElementById("dob-year");

    const months = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];

    //Rellenar Días (1-31)
    if (daySelect) {
        for (let i = 1; i <= 31; i++) {
            const option = document.createElement("option");
            option.value = i;
            option.textContent = i;
            daySelect.appendChild(option);
        }
    }

    //Rellenar Meses
    if (monthSelect) {
        months.forEach((month, index) => {
            const option = document.createElement("option");
            option.value = index + 1; // 1-12
            option.textContent = month;
            monthSelect.appendChild(option);
        });
    }

    //Rellenar Años
    if (yearSelect) {
        const currentYear = new Date().getFullYear();
        const startYear = currentYear - 100;
        const endYear = currentYear - 18; 

        for (let i = endYear; i >= startYear; i--) {
            const option = document.createElement("option");
            option.value = i;
            option.textContent = i;
            yearSelect.appendChild(option);
        }
        
        const yearPlaceholder = document.createElement("option");
        yearPlaceholder.value = "";
        yearPlaceholder.textContent = "Año";
        yearSelect.prepend(yearPlaceholder);
        yearSelect.value = ""; 
    }
});