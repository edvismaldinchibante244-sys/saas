/**
 * ============================================
 * JAVASCRIPT DO LOGIN (Bootstrap 5)
 * ============================================
 */

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const alertDiv = document.getElementById('alert');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const email = document.getElementById('email').value;
        const senha = document.getElementById('senha').value;

        if(!email || !senha) {
            showAlert('Preencha todos os campos!', 'danger');
            return;
        }

        const formData = new FormData();
        formData.append('email', email);
        formData.append('senha', senha);

        fetch('login_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            showAlert('Erro ao processar login. Tente novamente.', 'danger');
            console.error('Erro:', error);
        });
    });

    function showAlert(message, type) {
        alertDiv.textContent = message;
        alertDiv.className = 'alert alert-' + type;
        alertDiv.style.display = 'block';
        setTimeout(() => {
            alertDiv.style.display = 'none';
        }, 5000);
    }
});
