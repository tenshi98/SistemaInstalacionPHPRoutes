/**
 * JavaScript para el instalador
 * Validaciones del lado del cliente y mejoras de UX
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Confirmación antes de ejecutar la instalación
    const executeButton = document.getElementById('execute-install');
    if (executeButton) {
        executeButton.addEventListener('click', function(e) {
            const confirmed = confirm('¿Estás seguro de que deseas ejecutar la instalación? Esta acción creará la base de datos y ejecutará el archivo SQL.');
            if (!confirmed) {
                e.preventDefault();
            }
        });
    }
    
    // Prevenir doble submit en formularios
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            // Solo aplicar loading si el botón no está deshabilitado
            if (submitButton && !submitButton.disabled && !submitButton.classList.contains('is-loading')) {
                submitButton.classList.add('is-loading');
                submitButton.disabled = true;
                
                // Rehabilitar después de 5 segundos por si hay error
                setTimeout(function() {
                    submitButton.classList.remove('is-loading');
                    submitButton.disabled = false;
                }, 5000);
            }
        });
    });
    
    // Validación de nombre de base de datos en tiempo real
    const dbNameInput = document.getElementById('db_name');
    if (dbNameInput) {
        dbNameInput.addEventListener('input', function() {
            const value = this.value;
            const helpText = document.getElementById('db-name-help');
            const pattern = /^[a-zA-Z0-9_]+$/;
            
            if (value.length === 0) {
                helpText.textContent = 'Solo letras, números y guiones bajos';
                helpText.className = 'help';
            } else if (!pattern.test(value)) {
                helpText.textContent = 'Caracteres inválidos detectados';
                helpText.className = 'help is-danger';
            } else if (value.length < 3) {
                helpText.textContent = 'Mínimo 3 caracteres';
                helpText.className = 'help is-warning';
            } else {
                helpText.textContent = 'Nombre válido';
                helpText.className = 'help is-success';
            }
        });
    }
    
    // Auto-cerrar notificaciones después de 5 segundos
    const notifications = document.querySelectorAll('.notification .delete');
    notifications.forEach(function(deleteButton) {
        deleteButton.addEventListener('click', function() {
            const notification = this.parentElement;
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(function() {
                notification.remove();
            }, 300);
        });
        
        // Auto-cerrar después de 5 segundos
        setTimeout(function() {
            deleteButton.click();
        }, 5000);
    });
    
    // Mostrar/ocultar contraseña
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = '🙈';
            } else {
                input.type = 'password';
                this.textContent = '👁️';
            }
        });
    });
});

// Animación de salida para notificaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
`;
document.head.appendChild(style);
