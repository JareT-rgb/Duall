document.addEventListener('DOMContentLoaded', () => {
    const loginToggle = document.getElementById('login-toggle');
    const registerToggle = document.getElementById('register-toggle');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const loginMessage = document.getElementById('login-message');
    const registerMessage = document.getElementById('register-message');

    loginToggle.addEventListener('click', () => {
        loginToggle.classList.add('toggle-active');
        registerToggle.classList.remove('toggle-active');
        loginForm.classList.add('form-active');
        registerForm.classList.remove('form-active');
        registerForm.style.display = 'none';
        loginForm.style.display = 'flex';
    });

    registerToggle.addEventListener('click', () => {
        registerToggle.classList.add('toggle-active');
        loginToggle.classList.remove('toggle-active');
        registerForm.classList.add('form-active');
        loginForm.classList.remove('form-active');
        loginForm.style.display = 'none';
        registerForm.style.display = 'flex';
    });

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('php/login_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                loginMessage.textContent = result.message;
                loginMessage.className = 'message success';
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1500);
            } else {
                loginMessage.textContent = result.message;
                loginMessage.className = 'message error';
            }
        } catch (error) {
            loginMessage.textContent = 'Ocurrió un error. Inténtalo de nuevo.';
            loginMessage.className = 'message error';
        }
    });

    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(registerForm);
        const data = Object.fromEntries(formData.entries());

        if (data.contrasena.length < 8) {
            registerMessage.textContent = 'La contraseña debe tener al menos 8 caracteres.';
            registerMessage.className = 'message error';
            return;
        }

        try {
            const response = await fetch('php/register_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                registerMessage.textContent = result.message;
                registerMessage.className = 'message success';
                setTimeout(() => {
                    loginToggle.click();
                    registerForm.reset();
                    registerMessage.textContent = '';
                }, 2000);
            } else {
                registerMessage.textContent = result.message;
                registerMessage.className = 'message error';
            }
        } catch (error) {
            registerMessage.textContent = 'Ocurrió un error. Inténtalo de nuevo.';
            registerMessage.className = 'message error';
        }
    });
});
