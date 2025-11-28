document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    const modal = document.getElementById('empresa-modal');
    const addEmpresaBtn = document.getElementById('add-empresa-btn');
    const closeButton = document.querySelector('.close-button');
    const empresaForm = document.getElementById('empresa-form');

    // -- INICIO: Lógica para Pestañas --
    tabLinks.forEach(link => {
        link.addEventListener('click', () => {
            const targetId = link.getAttribute('data-target');
            
            tabContents.forEach(content => content.classList.remove('active'));
            tabLinks.forEach(btn => btn.classList.remove('active'));

            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.add('active');
            }
            link.classList.add('active');
        });
    });
    // -- FIN: Lógica para Pestañas --


    // -- INICIO: Lógica del Modal para Agregar Empresa --
    if (addEmpresaBtn) {
        addEmpresaBtn.addEventListener('click', () => {
            empresaForm.reset();
            document.getElementById('id_empresa').value = ''; 
            document.getElementById('modal-title').textContent = 'Agregar Nueva Empresa';
            modal.style.display = 'block';
        });
    }

    if (closeButton) {
        closeButton.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });

    if (empresaForm) {
        empresaForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // -- INICIO: Validación del Formulario --
            const rfcInput = document.getElementById('rfc');
            const rfcValue = rfcInput.value.trim().toUpperCase();
            
            // Expresión regular para validar RFC (simplificada)
            const rfcRegex = /^[A-Z&Ñ]{3,4}\d{6}[A-Z\d]{3}$/;

            if (rfcValue && !rfcRegex.test(rfcValue)) {
                alert('El formato del RFC no es válido. Debe ser, por ejemplo, "XAXX010101000".');
                rfcInput.focus();
                return;
            }

            // Validar que los campos requeridos no estén vacíos
            const requiredFields = ['nombre_empresa', 'descripcion', 'carrera_afin', 'direccion', 'telefono_empresa', 'correo_empresa'];
            for (const fieldId of requiredFields) {
                const input = document.getElementById(fieldId);
                if (!input.value.trim()) {
                    alert(`El campo "${input.previousElementSibling.textContent}" es obligatorio.`);
                    input.focus();
                    return;
                }
            }
            // -- FIN: Validación del Formulario --

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            data.action = data.id_empresa ? 'edit_empresa' : 'add_empresa';

            fetch('php/admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                if (result.success) {
                    modal.style.display = 'none';
                    location.reload(); 
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
    // -- FIN: Lógica del Modal --

    // -- INICIO: Lógica para botones de Editar y Eliminar Empresa --
    const empresasTable = document.querySelector('#gestion-empresas table');
    if (empresasTable) {
        empresasTable.addEventListener('click', function(e) {
            const button = e.target.closest('.btn-action');
            if (!button) return;

            const empresaId = button.getAttribute('data-id');

            if (button.classList.contains('btn-edit')) {
                handleEditEmpresa(empresaId);
            } else if (button.classList.contains('btn-delete')) {
                handleDeleteEmpresa(empresaId);
            }
        });
    }

    function handleEditEmpresa(id) {
        fetch(`php/admin_actions.php?action=get_empresa&id=${id}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const empresa = result.data;
                    document.getElementById('id_empresa').value = empresa.id_empresa;
                    document.getElementById('nombre_empresa').value = empresa.nombre_empresa;
                    document.getElementById('razon_social').value = empresa.razon_social;
                    document.getElementById('rfc').value = empresa.rfc;
                    document.getElementById('giro').value = empresa.giro;
                    document.getElementById('descripcion').value = empresa.descripcion;
                    document.getElementById('carrera_afin').value = empresa.carrera_afin;
                    document.getElementById('perfil_alumno').value = empresa.perfil_alumno;
                    document.getElementById('direccion').value = empresa.direccion;
                    document.getElementById('telefono_empresa').value = empresa.telefono_empresa;
                    document.getElementById('correo_empresa').value = empresa.correo_empresa;
                    document.getElementById('nombre_contacto').value = empresa.nombre_contacto;
                    document.getElementById('telefono_contacto').value = empresa.telefono_contacto;

                    document.getElementById('modal-title').textContent = 'Editar Empresa';
                    modal.style.display = 'block';
                } else {
                    alert(result.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function handleDeleteEmpresa(id) {
        const deleteModal = document.getElementById('confirm-delete-modal');
        const confirmBtn = document.getElementById('confirm-delete-btn');
        const cancelBtn = document.getElementById('cancel-delete-btn');
        const closeModal = deleteModal.querySelector('.close-button');

        deleteModal.style.display = 'block';

        // Usamos .onclick para sobrescribir eventos previos y evitar múltiples llamadas
        confirmBtn.onclick = () => {
            fetch('php/admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_empresa', id_empresa: id })
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                if (result.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                deleteModal.style.display = 'none';
            });
        };

        cancelBtn.onclick = () => {
            deleteModal.style.display = 'none';
        };
        
        closeModal.onclick = () => {
            deleteModal.style.display = 'none';
        };

        window.addEventListener('click', (event) => {
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    }
    // -- FIN: Lógica para botones de Empresa --


    // -- INICIO: Lógica para botones de Editar y Eliminar Usuario --
    const usuariosTable = document.querySelector('#usuarios-table');
    if (usuariosTable) {
        usuariosTable.addEventListener('click', function(e) {
            const button = e.target.closest('.btn-action');
            if (!button) return;

            const nControl = button.getAttribute('data-id');

            if (button.classList.contains('btn-edit')) {
                handleEditUser(nControl);
            } else if (button.classList.contains('btn-delete')) {
                handleDeleteUser(nControl);
            }
        });
    }

    const userModal = document.getElementById('user-modal');
    const userForm = document.getElementById('user-form');
    const userModalCloseButton = userModal.querySelector('.close-button');

    function handleEditUser(nControl) {
        fetch(`php/admin_actions.php?action=get_user&id=${nControl}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const user = result.data;
                    document.getElementById('n_control').value = user.n_control;
                    document.getElementById('nombre').value = user.nombre;
                    document.getElementById('ap_paterno').value = user.ap_paterno;
                    document.getElementById('ap_materno').value = user.ap_materno;
                    document.getElementById('correo_electronico').value = user.correo_electronico;
                    document.getElementById('telefono').value = user.telefono;
                    document.getElementById('direccion').value = user.direccion;
                    document.getElementById('carrera').value = user.carrera;
                    document.getElementById('semestre').value = user.semestre;
                    document.getElementById('grupo').value = user.grupo;
                    document.getElementById('turno').value = user.turno;

                    document.getElementById('user-modal-title').textContent = 'Editar Usuario';
                    userModal.style.display = 'block';
                } else {
                    alert(result.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    if (userForm) {
        userForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const requiredFields = ['nombre', 'ap_paterno', 'ap_materno', 'correo_electronico', 'telefono', 'direccion', 'carrera', 'semestre', 'grupo', 'turno'];
            for (const fieldId of requiredFields) {
                const input = document.getElementById(fieldId);
                if (!input.value.trim()) {
                    alert(`El campo "${input.previousElementSibling.textContent}" es obligatorio.`);
                    input.focus();
                    return;
                }
            }

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            data.action = 'edit_user';

            fetch('php/admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                if (result.success) {
                    userModal.style.display = 'none';
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    if (userModalCloseButton) {
        userModalCloseButton.addEventListener('click', () => {
            userModal.style.display = 'none';
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target == userModal) {
            userModal.style.display = 'none';
        }
    });

    function handleDeleteUser(nControl) {
        const deleteModal = document.getElementById('confirm-delete-modal');
        const confirmBtn = document.getElementById('confirm-delete-btn');
        const cancelBtn = document.getElementById('cancel-delete-btn');
        const closeModalBtn = deleteModal.querySelector('.close-button');
        
        document.getElementById('delete-modal-message').textContent = `¿Estás seguro de que quieres eliminar a este usuario? Esta acción no se puede deshacer.`;
        deleteModal.style.display = 'block';

        confirmBtn.onclick = () => {
            fetch('php/admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_user', n_control: nControl })
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                if (result.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                deleteModal.style.display = 'none';
            });
        };

        cancelBtn.onclick = () => deleteModal.style.display = 'none';
        closeModalBtn.onclick = () => deleteModal.style.display = 'none';
    }
    // -- FIN: Lógica para botones de Usuario --


    // -- INICIO: Lógica para Aceptar/Denegar Vinculación --
    const vinculacionesTable = document.querySelector('#gestion-vinculaciones table');
    const aceptarVinculacionModal = document.getElementById('aceptar-vinculacion-modal');
    const aceptarVinculacionForm = document.getElementById('aceptar-vinculacion-form');
    const closeAceptarModalBtn = document.querySelector('#aceptar-vinculacion-modal .close-button');

    if (vinculacionesTable) {
        vinculacionesTable.addEventListener('click', function(e) {
            const button = e.target.closest('.btn-action');
            if (!button) return;

            const idRegistro = button.getAttribute('data-id');

            if (button.classList.contains('btn-aceptar')) {
                document.getElementById('id_registro_vinculacion').value = idRegistro;
                aceptarVinculacionModal.style.display = 'block';

            } else if (button.classList.contains('btn-denegar')) {
                handleDenyVinculacion(idRegistro);
            } else if (button.classList.contains('btn-baja-aceptar')) {
                 if (confirm('¿Estás seguro de que quieres aceptar esta solicitud de baja?')) {
                    updateVinculacionStatus(idRegistro, 'Baja Aceptada');
                }
            } else if (button.classList.contains('btn-baja-rechazar')) {
                if (confirm('¿Estás seguro de que quieres rechazar esta solicitud de baja?')) {
                    updateVinculacionStatus(idRegistro, 'Baja Rechazada'); // Regresa al estado anterior
                }
            }
        });
    }

    if (closeAceptarModalBtn) {
        closeAceptarModalBtn.addEventListener('click', () => {
            aceptarVinculacionModal.style.display = 'none';
        });
    }

    if (aceptarVinculacionForm) {
        aceptarVinculacionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            data.action = 'accept_vinculacion';

            fetch('php/admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                if (result.success) {
                    aceptarVinculacionModal.style.display = 'none';
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    function handleDenyVinculacion(id) {
        const deleteModal = document.getElementById('confirm-delete-modal');
        const confirmBtn = document.getElementById('confirm-delete-btn');
        const cancelBtn = document.getElementById('cancel-delete-btn');
        const closeModalBtn = deleteModal.querySelector('.close-button');
        
        document.getElementById('delete-modal-title').textContent = 'Confirmar Rechazo';
        document.getElementById('delete-modal-message').textContent = '¿Estás seguro de que quieres rechazar esta solicitud?';
        deleteModal.style.display = 'block';

        confirmBtn.onclick = () => {
            updateVinculacionStatus(id, 'Rechazado');
            deleteModal.style.display = 'none';
        };

        cancelBtn.onclick = () => deleteModal.style.display = 'none';
        closeModalBtn.onclick = () => deleteModal.style.display = 'none';
    }

    function updateVinculacionStatus(id, newStatus) {
        fetch('php/admin_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_status', id_registro: id, estatus: newStatus })
        })
        .then(response => response.json())
        .then(result => {
            alert(result.message);
            if (result.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    window.addEventListener('click', (event) => {
        if (event.target == aceptarVinculacionModal) {
            aceptarVinculacionModal.style.display = 'none';
        }
    });
    // -- FIN: Lógica para Aceptar/Denegar Vinculación --


    // -- INICIO: Lógica para campos de la tabla --
    
    // Lógica para celdas de fecha con icono de lápiz (versión mejorada con delegación de eventos)
    const alumnosTbody = document.querySelector('#gestion-alumnos table tbody');
    if (alumnosTbody) {
        alumnosTbody.addEventListener('click', function(event) {
            const dateDisplaySpan = event.target.closest('.date-display');
            
            if (dateDisplaySpan) {
                const container = dateDisplaySpan.parentElement;
                const input = container.querySelector('.date-input');

                if (input && !input.disabled) {
                    dateDisplaySpan.style.display = 'none';
                    input.style.display = 'block';
                    input.focus();
                }
            }
        });
    }

    document.querySelectorAll('.date-input').forEach(input => {
        // Evento 'change' es para cuando se selecciona una fecha
        input.addEventListener('change', function() {
            const container = this.parentElement;
            const span = container.querySelector('.date-display');

            // Actualizar el texto a mostrar
            if (this.value) {
                const date = new Date(this.value);
                // Ajustar por la zona horaria para evitar problemas de un día menos
                const adjustedDate = new Date(date.getTime() + date.getTimezoneOffset() * 60000);
                span.innerHTML = adjustedDate.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
            } else {
                span.innerHTML = '<span class="edit-icon">&#9998;</span>';
            }
            
            this.style.display = 'none';
            span.style.display = 'inline';

            // Llamada a la función de guardado
            saveFieldChange(this);
        });

        // Evento 'blur' es para cuando el input pierde el foco
        input.addEventListener('blur', function() {
            // Ocultar el input y mostrar el span si no se hizo ningún cambio
            const container = this.parentElement;
            const span = container.querySelector('.date-display');
            this.style.display = 'none';
            span.style.display = 'inline';
        });
    });

    // Actualizar campos de texto normales
    document.querySelectorAll('.editable-field:not(.date-input)').forEach(input => {
        input.addEventListener('change', function() {
            saveFieldChange(this);
        });
    });
    
    // Función centralizada para guardar cambios de campos
    function saveFieldChange(element) {
        const id_registro = element.getAttribute('data-id');
        const field = element.getAttribute('data-field');
        const value = element.value;

        if (!id_registro) return;

        fetch('php/admin_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_alumno_report',
                id_registro: id_registro,
                field: field,
                value: value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Error al actualizar: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // -- FIN: Lógica para campos de la tabla --

    // -- INICIO: Lógica para Generar Reportes PDF --
    const reportButtons = document.querySelectorAll('.btn-report');

    reportButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tableId = button.getAttribute('data-table');
            const table = document.getElementById(tableId);
            const title = button.parentElement.querySelector('h2').textContent;
            generatePDF(table, title);
        });
    });

    function generatePDF(table, title) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.text(title, 14, 16);
        doc.autoTable({
            html: table,
            startY: 20,
            theme: 'grid',
            headStyles: { fillColor: [22, 160, 133] },
            styles: { fontSize: 8 },
        });

        doc.save(`${title.replace(/ /g, '_')}.pdf`);
    }
    // -- FIN: Lógica para Generar Reportes PDF --


    // -- INICIO: Lógica para el botón de Editar Perfil del Admin --
    const editAdminBtn = document.getElementById('edit-admin-profile');
    if (editAdminBtn) {
        editAdminBtn.addEventListener('click', () => {
            alert('Funcionalidad para editar el perfil del administrador pendiente de implementación.');
            // Aquí se podría abrir un modal similar al de empresas para editar los datos del admin
        });
    }
    // -- FIN: Lógica para el botón de Editar Perfil del Admin --
});
