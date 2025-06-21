document.addEventListener('DOMContentLoaded', function () {
    // ---- INICIO LÓGICA DE VALIDACIÓN DE TABS ----
    const tabButtons = document.querySelectorAll('#validacionTabs .nav-link');
    const tabPanes = document.querySelectorAll('#validacionTabsContent .tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();

            tabButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            });
            tabPanes.forEach(pane => {
                pane.classList.remove('show', 'active');
            });

            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            const targetPaneId = this.getAttribute('data-bs-target');
            const targetPane = document.querySelector(targetPaneId);

            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
        });
    });

    // ---- INICIO LÓGICA DE MUNICIPIOS Y MONEDA ----
    const estadoSelect = document.getElementById('estado_id');
    const municipioSelect = document.getElementById('municipio_id');
    const estadoOriginal = "<?php echo htmlspecialchars(strtolower(trim($propiedadRevision['estado'] ?? ''))); ?>";
    const municipioOriginal = "<?php echo htmlspecialchars(strtolower(trim($propiedadRevision['municipio'] ?? ''))); ?>";
    let municipiosCargadosParaEstadoActual = [];

    async function loadMunicipiosByEstado(estadoId, selectedMunicipioNombreOriginal = null) {
        municipioSelect.innerHTML = '<option value="">Cargando municipios...</option>';
        municipioSelect.disabled = true;

        if (!estadoId) {
            municipioSelect.innerHTML = '<option value="">Seleccione un Estado primero...</option>';
            return;
        }
        try {
            const response = await fetch(`/api/catalogos/municipios?estado_id=${estadoId}`);
            const result = await response.json();
            if (result.status === 'success' && result.data) {
                municipioSelect.innerHTML = '<option value="">Seleccione un Municipio...</option>';
                municipiosCargadosParaEstadoActual = result.data;
                result.data.forEach(municipio => {
                    const option = document.createElement('option');
                    option.value = municipio.id;
                    option.textContent = municipio.nombre;
                    option.dataset.nombre = municipio.nombre.toLowerCase().trim();
                    municipioSelect.appendChild(option);
                });
                municipioSelect.disabled = false;
                if (selectedMunicipioNombreOriginal) {
                    const municipioPreseleccionado = municipiosCargadosParaEstadoActual.find(m => m.nombre.toLowerCase().trim() === selectedMunicipioNombreOriginal);
                    if (municipioPreseleccionado) municipioSelect.value = municipioPreseleccionado.id;
                }
            } else {
                municipioSelect.innerHTML = '<option value="">Error al cargar</option>';
            }
        } catch (error) {
            municipioSelect.innerHTML = '<option value="">Error de conexión</option>';
        }
    }
    estadoSelect.addEventListener('change', function () {
        loadMunicipiosByEstado(this.value, municipioOriginal);
    });

    if (estadoOriginal) {
        const estadoPreseleccionado = Array.from(estadoSelect.options).find(opt => opt.dataset.nombre === estadoOriginal);

        if (estadoPreseleccionado) {
            estadoSelect.value = estadoPreseleccionado.value;
            loadMunicipiosByEstado(estadoPreseleccionado.value, municipioOriginal);
        }
    }

    document.querySelectorAll('.input-currency').forEach(input => {
        const formatCurrency = (valueStr) => {
            let value = parseFloat(String(valueStr).replace(/[^\d.-]/g, ''));

            if (!isNaN(value)) {
                return value.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            return '';
        };
        
        input.addEventListener('blur', function (e) {
            e.target.value = formatCurrency(e.target.value);
        });

        if (input.value) {
            input.value = formatCurrency(input.value);
        }
    });
});
