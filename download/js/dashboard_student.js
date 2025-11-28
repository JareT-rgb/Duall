document.addEventListener('DOMContentLoaded', () => {
    // --- Lógica para Notificaciones (Toast) ---
    const notificationContainer = document.getElementById('notification-container');

    const showToast = (message, type = 'success') => {
        if (!notificationContainer) return;

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;

        notificationContainer.appendChild(toast);

        // Eliminar el toast del DOM después de que la animación de salida termine
        setTimeout(() => {
            toast.remove();
        }, 5000); // 5000ms = 4.5s de visibilidad + 0.5s de animación de salida
    };

    // --- Lógica para Control de Botones (Spinner) ---
    const setLoading = (button, isLoading) => {
        if (button) {
            if (isLoading) {
                button.classList.add('loading');
                button.disabled = true;
            } else {
                button.classList.remove('loading');
                button.disabled = false;
            }
        }
    };

    // --- Lógica para Modales ---
    const profileModal = document.getElementById('profile-modal');
    const confirmModal = document.getElementById('confirm-modal');

    const closeProfileModal = () => {
        if (profileModal) {
            profileModal.style.display = 'none';
            const feedbackDiv = document.getElementById('form-feedback');
            if (feedbackDiv) {
                feedbackDiv.style.display = 'none';
                feedbackDiv.textContent = '';
                feedbackDiv.className = 'form-feedback';
            }
        }
    };
    
    let empresaIdParaUnirse = null;
    const closeConfirmModal = () => {
        if (confirmModal) {
            confirmModal.style.display = 'none';
            empresaIdParaUnirse = null;
        }
    };

    window.addEventListener('click', (event) => {
        if (event.target === profileModal) {
            closeProfileModal();
        }
        if (event.target === confirmModal) {
            closeConfirmModal();
        }
    });

    // --- Lógica para Solicitud de Empresa ---
    if (confirmModal) {
        const cancelSolicitudBtn = document.getElementById('cancel-solicitud-btn');
        const confirmSolicitudBtn = document.getElementById('confirm-solicitud-btn');
        const empresaNombreSpan = document.getElementById('empresa-a-unirse');
        const solicitarButtons = document.querySelectorAll('.btn-solicitar');
        
        const openConfirmModal = () => confirmModal.style.display = 'block';

        solicitarButtons.forEach(button => {
            button.addEventListener('click', () => {
                empresaIdParaUnirse = button.dataset.idEmpresa;
                empresaNombreSpan.textContent = button.dataset.nombreEmpresa;
                openConfirmModal();
            });
        });

        if (cancelSolicitudBtn) cancelSolicitudBtn.addEventListener('click', closeConfirmModal);
        
        if (confirmSolicitudBtn) {
            confirmSolicitudBtn.addEventListener('click', async () => {
                if (!empresaIdParaUnirse) return;

                setLoading(confirmSolicitudBtn, true);

                try {
                    const response = await fetch('php/postular.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_empresa: empresaIdParaUnirse })
                    });

                    const result = await response.json();

                    if (result.success) {
                        showToast(result.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast(result.message, 'error');
                    }
                } catch (error) {
                    showToast('Ocurrió un error de red. Por favor, inténtalo de nuevo.', 'error');
                } finally {
                    setLoading(confirmSolicitudBtn, false);
                    closeConfirmModal();
                }
            });
        }
    }

    // --- Lógica para Solicitar Baja ---
    const bajaModal = document.getElementById('confirm-baja-modal');
    if (bajaModal) {
        const bajaBtn = document.querySelector('.btn-baja');
        const cancelBajaBtn = document.getElementById('cancel-baja-btn');
        const confirmBajaBtn = document.getElementById('confirm-baja-btn');
        let registroIdParaBaja = null;

        const openBajaModal = () => bajaModal.style.display = 'block';
        const closeBajaModal = () => {
            bajaModal.style.display = 'none';
            registroIdParaBaja = null;
        };

        if (bajaBtn) {
            bajaBtn.addEventListener('click', () => {
                registroIdParaBaja = bajaBtn.dataset.idRegistro;
                openBajaModal();
            });
        }

        if (cancelBajaBtn) cancelBajaBtn.addEventListener('click', closeBajaModal);
        window.addEventListener('click', (event) => {
            if (event.target === bajaModal) {
                closeBajaModal();
            }
        });

        if (confirmBajaBtn) {
            confirmBajaBtn.addEventListener('click', async () => {
                if (!registroIdParaBaja) return;

                setLoading(confirmBajaBtn, true);

                try {
                    const response = await fetch('php/solicitar_baja.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_registro: registroIdParaBaja })
                    });
                    const result = await response.json();

                    if (result.success) {
                        showToast(result.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast(result.message, 'error');
                    }
                } catch (error) {
                    showToast('Ocurrió un error de red. Por favor, inténtalo de nuevo.', 'error');
                } finally {
                    setLoading(confirmBajaBtn, false);
                    closeBajaModal();
                }
            });
        }
    }

    // --- Lógica para Editar Perfil ---
    if (profileModal) {
        const editIcon = document.getElementById('edit-profile-icon');
        const closeModalButton = document.getElementById('close-modal-button');
        const cancelBtn = document.getElementById('cancel-edit-btn');
        const form = document.getElementById('profile-edit-form');
        const saveBtn = document.getElementById('save-profile-btn');
        const feedbackDiv = document.getElementById('form-feedback');
        const formInputs = form.querySelectorAll('input');

        const openProfileModal = () => profileModal.style.display = 'block';
        
        if(editIcon) editIcon.addEventListener('click', openProfileModal);
        if(closeModalButton) closeModalButton.addEventListener('click', closeProfileModal);
        if(cancelBtn) cancelBtn.addEventListener('click', closeProfileModal);

        if (form) {
            let initialFormData = new FormData(form);
            const checkFormChanges = () => {
                let currentFormData = new FormData(form);
                let hasChanged = false;
                for (let [key, value] of initialFormData.entries()) {
                    if (currentFormData.get(key) !== value) {
                        hasChanged = true;
                        break;
                    }
                }
                if (form.querySelector('#contrasena').value !== '') {
                    hasChanged = true;
                }
                saveBtn.disabled = !hasChanged;
            };

            formInputs.forEach(input => input.addEventListener('input', checkFormChanges));
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                setLoading(saveBtn, true);

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                if (data.contrasena && data.contrasena.length < 8) {
                    showFeedback('La nueva contraseña debe tener al menos 8 caracteres.', 'error');
                    return;
                }

                try {
                    const response = await fetch('php/update_student_profile.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    if (result.success) {
                        // Usar el nuevo sistema de notificaciones
                        showToast(result.message, 'success');
                        
                        // Actualizar la vista del perfil en la página
                        updateProfileDisplay(data);
                        
                        // Cerrar el modal sin recargar
                        closeProfileModal();

                        // Opcional: Re-habilitar el botón de guardar y resetear el estado de cambios
                        // Esto es útil si el usuario quiere hacer más cambios sin cerrar el modal
                        saveBtn.disabled = true; 
                        initialFormData = new FormData(form);
                        
                    } else {
                        showFeedback(result.message, 'error');
                    }
                } catch (error) {
                    showFeedback('Error de conexión. Inténtalo de nuevo.', 'error');
                } finally {
                    setLoading(saveBtn, false);
                }
            });
        }

        const showFeedback = (message, type) => {
            if (feedbackDiv) {
                feedbackDiv.textContent = message;
                feedbackDiv.className = `form-feedback ${type}`;
                feedbackDiv.style.display = 'block';
            }
        };

        const updateProfileDisplay = (data) => {
            document.getElementById('display-nombre').textContent = `${data.nombre} ${data.ap_paterno} ${data.ap_materno}`;
            document.getElementById('display-correo').textContent = data.correo_electronico;
            document.getElementById('display-telefono').textContent = data.telefono;
            document.getElementById('display-direccion').textContent = data.direccion;
        };
    }
});
