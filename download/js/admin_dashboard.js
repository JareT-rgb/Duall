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
            const target = e.target;
            if (target.classList.contains('btn-edit')) {
                const empresaId = target.getAttribute('data-id');
                handleEditEmpresa(empresaId);
            } else if (target.classList.contains('btn-delete')) {
                const empresaId = target.getAttribute('data-id');
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
        if (confirm('¿Estás seguro de que quieres eliminar esta empresa?')) {
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
            .catch(error => console.error('Error:', error));
        }
    }
    // -- FIN: Lógica para botones de Empresa --


    // -- INICIO: Lógica para Aceptar/Denegar Vinculación --
    const vinculacionesTable = document.querySelector('#gestion-vinculaciones table');
    const vinculacionModal = document.getElementById('vinculacion-modal');
    const vinculacionForm = document.getElementById('vinculacion-form');
    const closeVinculacionModal = document.querySelector('#vinculacion-modal .close-button');

    if (vinculacionesTable) {
        vinculacionesTable.addEventListener('click', function(e) {
            const target = e.target;
            if (target.classList.contains('btn-accept')) {
                const postulacionId = target.getAttribute('data-id');
                document.getElementById('id_postulacion').value = postulacionId;
                vinculacionModal.style.display = 'block';
            } else if (target.classList.contains('btn-deny')) {
                const postulacionId = target.getAttribute('data-id');
                if (confirm('¿Estás seguro de que quieres denegar esta solicitud?')) {
                    // Aquí iría la lógica para denegar, por ejemplo, una llamada a fetch
                    console.log('Denegar postulación ID:', postulacionId);
                }
            }
        });
    }

    if (closeVinculacionModal) {
        closeVinculacionModal.addEventListener('click', () => {
            vinculacionModal.style.display = 'none';
        });
    }

    if (vinculacionForm) {
        vinculacionForm.addEventListener('submit', function(e) {
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
                    vinculacionModal.style.display = 'none';
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target == vinculacionModal) {
            vinculacionModal.style.display = 'none';
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

    // Cambiar estatus de postulación
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const id_registro = this.getAttribute('data-id');
            const estatus = this.value;

            fetch('php/admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_status',
                    id_registro: id_registro,
                    estatus: estatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error al cambiar el estatus: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
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
});
