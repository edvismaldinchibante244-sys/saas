/**
 * ============================================
 * JAVASCRIPT - GESTÃO DE MESAS (Bootstrap 5)
 * ============================================
 */

let modalNovaMesaInstance = null;

document.addEventListener('DOMContentLoaded', function () {
    // Inicializar modal Bootstrap
    const modalEl = document.getElementById('modalNovaMesa');
    if (modalEl) {
        modalNovaMesaInstance = new bootstrap.Modal(modalEl);
    }
    
    // Form nova mesa
    const form = document.getElementById('formNovaMesa');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(form);

            fetch('api/mesa_cadastrar.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => showAlert('Erro ao cadastrar', 'danger'));
        });
    }
});

function atualizarMesa(id, novoStatus) {
    const labels = { 'LIVRE': 'liberar', 'OCUPADA': 'ocupar', 'RESERVADA': 'reservar' };
    if (!confirm('Deseja ' + (labels[novoStatus] || novoStatus) + ' esta mesa?')) return;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', novoStatus);

    fetch('api/mesa_atualizar.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => showAlert('Erro ao atualizar', 'danger'));
}

function abrirModalNovaMesa() {
    document.getElementById('formNovaMesa').reset();
    const alertDiv = document.getElementById('alertMesa');
    if (alertDiv) {
        alertDiv.style.display = 'none';
    }
    if (modalNovaMesaInstance) {
        modalNovaMesaInstance.show();
    } else {
        const modal = document.getElementById('modalNovaMesa');
        if (modal) modal.style.display = 'block';
    }
}

function showAlert(message, type) {
    const alertDiv = document.getElementById('alertMesa');
    if (alertDiv) {
        alertDiv.textContent = message;
        alertDiv.className = 'alert alert-' + type;
        alertDiv.style.display = 'block';
        setTimeout(() => alertDiv.style.display = 'none', 4000);
    }
}
