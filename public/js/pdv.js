/**
 * ============================================
 * JAVASCRIPT - PDV (Ponto de Venda)
 * ============================================
 */

let carrinho = [];

function adicionarAoCarrinho(produto) {
    const index = carrinho.findIndex(item => item.id === produto.id);
    if(index !== -1) {
        carrinho[index].quantidade++;
    } else {
        carrinho.push({
            id: produto.id,
            nome: produto.nome,
            preco: parseFloat(produto.preco),
            quantidade: 1
        });
    }
    atualizarCarrinho();
}

function atualizarCarrinho() {
    const container = document.getElementById('carrinhoItens');
    
    if(carrinho.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">🛒<br>Carrinho vazio</div>';
        document.getElementById('subtotal').textContent = '0,00 MZN';
        document.getElementById('total').textContent = '0,00 MZN';
        return;
    }
    
    let html = '';
    carrinho.forEach((item, index) => {
        const subtotal = item.preco * item.quantidade;
        html += `
            <div class="carrinho-item">
                <div class="item-info">
                    <div class="item-nome">${item.nome}</div>
                    <div class="item-preco">${formatarMoeda(item.preco)} x ${item.quantidade}</div>
                </div>
                <div class="item-qtd">
                    <button class="btn-qtd" onclick="alterarQuantidade(${index}, -1)">-</button>
                    <span>${item.quantidade}</span>
                    <button class="btn-qtd" onclick="alterarQuantidade(${index}, 1)">+</button>
                </div>
                <div class="item-subtotal">${formatarMoeda(subtotal)}</div>
                <button class="btn-remover" onclick="removerItem(${index})">🗑️</button>
            </div>
        `;
    });
    
    container.innerHTML = html;
    calcularTotal();
}

function alterarQuantidade(index, delta) {
    carrinho[index].quantidade += delta;
    if(carrinho[index].quantidade <= 0) {
        carrinho.splice(index, 1);
    }
    atualizarCarrinho();
}

function removerItem(index) {
    carrinho.splice(index, 1);
    atualizarCarrinho();
}

function calcularTotal() {
    let subtotal = 0;
    carrinho.forEach(item => {
        subtotal += item.preco * item.quantidade;
    });
    const desconto = parseFloat(document.getElementById('desconto').value) || 0;
    const total = subtotal - desconto;
    document.getElementById('subtotal').textContent = formatarMoeda(subtotal);
    document.getElementById('total').textContent = formatarMoeda(total);
}

function limparCarrinho() {
    if(carrinho.length === 0) return;
    if(confirm('Deseja limpar o carrinho?')) {
        carrinho = [];
        document.getElementById('desconto').value = 0;
        atualizarCarrinho();
    }
}

function finalizarVenda() {
    if(carrinho.length === 0) {
        showAlert('Adicione produtos ao carrinho!', 'warning');
        return;
    }
    
    const mesa_id = document.getElementById('mesa_id').value;
    const forma_pagamento = document.getElementById('forma_pagamento').value;
    const desconto = parseFloat(document.getElementById('desconto').value) || 0;
    
    let subtotal = 0;
    carrinho.forEach(item => { subtotal += item.preco * item.quantidade; });
    const total = subtotal - desconto;
    
    const dados = {
        mesa_id: mesa_id || null,
        forma_pagamento: forma_pagamento,
        desconto: desconto,
        total: subtotal,
        total_final: total,
        itens: carrinho
    };
    
    fetch('api/venda_criar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showAlert('Venda realizada com sucesso! #' + data.venda_id, 'success');
            carrinho = [];
            document.getElementById('desconto').value = 0;
            document.getElementById('mesa_id').value = '';
            atualizarCarrinho();
            if(confirm('Deseja imprimir o comprovante?')) {
                window.open('comprovante.php?id=' + data.venda_id, '_blank');
            }
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => showAlert('Erro ao processar venda', 'danger'));
}

document.addEventListener('DOMContentLoaded', function() {
    const inputBuscar = document.getElementById('buscarProduto');
    if(inputBuscar) {
        inputBuscar.addEventListener('keyup', function() {
            const busca = this.value.toLowerCase();
            const cards = document.querySelectorAll('.produto-card');
            cards.forEach(card => {
                const nome = card.querySelector('.produto-nome').textContent.toLowerCase();
                card.style.display = nome.includes(busca) ? 'block' : 'none';
            });
        });
    }
});

function formatarMoeda(valor) {
    return valor.toFixed(2).replace('.', ',') + ' MZN';
}

function showAlert(message, type) {
    const alertDiv = document.getElementById('alertVenda');
    alertDiv.textContent = message;
    alertDiv.className = 'alert alert-' + (type === 'warning' ? 'warning' : (type === 'success' ? 'success' : 'danger'));
    alertDiv.style.display = 'block';
    setTimeout(() => alertDiv.style.display = 'none', 5000);
}
