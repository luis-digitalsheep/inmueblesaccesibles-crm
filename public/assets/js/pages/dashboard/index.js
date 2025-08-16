import { fetchData } from '../../utils/api.js';
import { showAlert } from '../../utils/alerts.js';

// --- ELEMENTOS DEL DOM ---
const dashboardContainer = document.getElementById('dashboard-container');

// --- FUNCIONES DE RENDERIZADO DE WIDGETS ---

function renderDirectorWidgets(data) {
    let kpiHtml = data.kpis.map(kpi => `
        <div class="widget-card">
            <div class="widget-card-body position-relative">
                <div class="widget-card-title">${kpi.label}</div>
                <div class="widget-card-value">${kpi.value}</div>
                <i class="fas fa-chart-line widget-card-icon"></i>
            </div>
        </div>
    `).join('');

    let queueHtml = data.work_queues.map(queue => `
        <a href="${queue.url}" class="widget-card">
            <div class="widget-card-body position-relative">
                <div class="widget-card-title">${queue.label}</div>
                <div class="widget-card-value">${queue.value}</div>
                <i class="fas fa-inbox widget-card-icon"></i>
            </div>
        </a>
    `).join('');

    dashboardContainer.innerHTML = `<div class="dashboard-grid">${kpiHtml}${queueHtml}</div>`;
}

function renderVendedorWidgets(data) {
    let kpiHtml = data.kpis.map(kpi => `
        <div class="widget-card">
            <div class="widget-card-body position-relative">
                <div class="widget-card-title">${kpi.label}</div>
                <div class="widget-card-value">${kpi.value}</div>
                <i class="fas fa-user-tie widget-card-icon"></i>
            </div>
        </div>
    `).join('');

    // TODO: añadir más widgets aquí, como "Última Actividad"
    dashboardContainer.innerHTML = `<div class="dashboard-grid">${kpiHtml}</div>`;
}

function renderAdminWidgets(data) {
    let queueHtml = data.work_queues.map(queue => `
        <a href="${queue.url}" class="widget-card">
            <div class="widget-card-body position-relative">
                <div class="widget-card-title">${queue.label}</div>
                <div class="widget-card-value">${queue.value}</div>
                <i class="fas fa-tasks widget-card-icon"></i>
            </div>
        </a>
    `).join('');

    dashboardContainer.innerHTML = `<div class="dashboard-grid">${queueHtml}</div>`;
}

// --- LÓGICA PRINCIPAL ---
async function initDashboard() {
    try {
        const {data: dashboardData} = await fetchData('/api/dashboard-data');

        // Decidimos qué tipo de dashboard renderizar según la respuesta de la API
        switch (dashboardData.type) {
            case 'director':
                renderDirectorWidgets(dashboardData);
                break;
            case 'vendedor':
                renderVendedorWidgets(dashboardData);
                break;
            case 'admin':
                renderAdminWidgets(dashboardData);
                break;
            default:
                dashboardContainer.innerHTML = '<p>No hay un dashboard configurado para tu rol.</p>';
        }
    } catch (error) {
        showAlert('No se pudo cargar la información del dashboard. ' + error.message, 'error');
        dashboardContainer.innerHTML = '<div class="alert alert-danger">Error al cargar el dashboard.</div>';
    }
}

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', () => {
    initDashboard();
});