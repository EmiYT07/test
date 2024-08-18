document.addEventListener('DOMContentLoaded', function() {
    // Configuración del selector de fecha
    const dateInput = document.querySelector('#task_date');
    if (dateInput) {
        dateInput.addEventListener('focus', function() {
            // Añadir el datepicker aquí si usas jQuery UI o similar
        });
    }

    // Ejemplo de animación para una página suave
    const elements = document.querySelectorAll('.animate');
    elements.forEach(element => {
        element.classList.add('fade-in');
    });
});

// Ejemplo de animación CSS
document.write(`
<style>
    .fade-in {
        opacity: 0;
        animation: fadeIn ease 2s;
        animation-fill-mode: forwards;
    }

    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }
</style>
`);
