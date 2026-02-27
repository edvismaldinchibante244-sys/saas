<?php
// Teste simples dos planos
session_start();
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Teste Planos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="p-5">
    <h2>Teste - Sistema de Planos</h2>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <h5>Básico</h5>
                <p class="text-muted">Grátis/mês</p>
                <button class="btn btn-secondary" onclick="testarFuncao('BASICO')">Testar</button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <h5>Profissional</h5>
                <p class="text-primary">1.500 MZN/mês</p>
                <button class="btn btn-primary" onclick="testarFuncao('PROFISSIONAL', 1500)">Testar</button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <h5>Enterprise</h5>
                <p class="text-warning">3.000 MZN/mês</p>
                <button class="btn btn-warning" onclick="testarFuncao('ENTERPRISE', 3000)">Testar</button>
            </div>
        </div>
    </div>

    <div id="resultado" class="alert mt-4" style="display:none;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var planoSelecionado = '';
        var valorPlano = 0;

        function testarFuncao(plano, valor) {
            planoSelecionado = plano;
            valorPlano = valor || 0;

            var resultado = document.getElementById('resultado');
            resultado.className = 'alert alert-info';
            resultado.innerHTML = '<strong>Função chamada!</strong><br>Plano: ' + plano + '<br>Valor: ' + valor + ' MZN<br><br>Chamando API...';
            resultado.style.display = 'block';

            console.log('Plano selecionado:', plano);
            console.log('Valor:', valor);

            // Testar API
            var formData = new FormData();
            formData.append('plano', plano);
            formData.append('metodo', 'DINHEIRO');

            fetch('api/plano_comprar.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(r => r.json())
                .then(data => {
                    console.log('Resposta da API:', data);
                    if (data.success) {
                        resultado.className = 'alert alert-success';
                        resultado.innerHTML = '<strong>Sucesso!</strong><br>' + data.message;
                    } else {
                        resultado.className = 'alert alert-danger';
                        resultado.innerHTML = '<strong>Erro:</strong><br>' + data.message;
                    }
                })
                .catch(err => {
                    console.error('Erro:', err);
                    resultado.className = 'alert alert-danger';
                    resultado.innerHTML = '<strong>Erro JavaScript:</strong><br>' + err.message;
                });
        }
    </script>
</body>

</html>