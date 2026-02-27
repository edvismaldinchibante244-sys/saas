/**
 * ============================================
 * JAVASCRIPT - GESTÃO DE PRODUTOS (Bootstrap 5)
 * ============================================
 */

let modalProdutoInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar modal Bootstrap
    const modalEl = document.getElementById('modalProduto');
    if (modalEl) {
        modalProdutoInstance = new bootstrap.Modal(modalEl);
    }
    
    // Permitir criar categoria ao pressionar Enter
    const novaCategoria = document.getElementById('novaCategoria');
    if(novaCategoria) {
        novaCategoria.addEventListener('keypress', function(e) {
            if(e.key === 'Enter') {
                e.preventDefault();
                adicionarNovaCategoria();
            }
        });
    }
    
    // Formulário de produto
    const form = document.getElementById('formProduto');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const produto_id = document.getElementById('produto_id').value;
            const url = produto_id ? 'api/produto_editar.php' : 'api/produto_cadastrar.php';
            
            fetch(url, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showAlertModal(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlertModal(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlertModal('Erro ao processar', 'danger');
                console.error('Erro:', error);
            });
        });
    }
    
    // Busca em tempo real
    const inputBuscar = document.getElementById('buscar');
    const filtroCategoria = document.getElementById('filtroCategoria');
    
    if(inputBuscar) inputBuscar.addEventListener('keyup', filtrarProdutos);
    if(filtroCategoria) filtroCategoria.addEventListener('change', filtrarProdutos);

    // preview de imagem do produto
    const inputImagem = document.getElementById('imagem');
    if (inputImagem) {
        inputImagem.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagemPreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    preview.src = ev.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        });
    }
});

function abrirModal() {
    document.getElementById('tituloModal').textContent = 'Novo Produto';
    document.getElementById('formProduto').reset();
    document.getElementById('produto_id').value = '';
    document.getElementById('ativo').checked = true;
    // limpar preview/imagem existente
    const imgPrev = document.getElementById('imagemPreview');
    const imgField = document.getElementById('imagem_existing');
    if (imgPrev) { imgPrev.src = ''; imgPrev.style.display = 'none'; }
    if (imgField) { imgField.value = ''; }
    if (modalProdutoInstance) {
        modalProdutoInstance.show();
    } else {
        document.getElementById('modalProduto').style.display = 'block';
    }
}

function fecharModal() {
    if (modalProdutoInstance) {
        modalProdutoInstance.hide();
    } else {
        document.getElementById('modalProduto').style.display = 'none';
    }
    document.getElementById('alertModal').style.display = 'none';
}

function editarProduto(id) {
    fetch('api/produto_buscar.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            const produto = data.data;
            document.getElementById('tituloModal').textContent = 'Editar Produto';
            document.getElementById('produto_id').value = produto.id;
            document.getElementById('nome').value = produto.nome;
            document.getElementById('categoria_id').value = produto.categoria_id || '';
            document.getElementById('descricao').value = produto.descricao || '';
            document.getElementById('preco').value = produto.preco;
            document.getElementById('custo').value = produto.custo || '';
            document.getElementById('estoque').value = produto.estoque;
            document.getElementById('estoque_minimo').value = produto.estoque_minimo;
            document.getElementById('ativo').checked = produto.ativo == 1;

            // imagem existente
            const imgPrev = document.getElementById('imagemPreview');
            const imgField = document.getElementById('imagem_existing');
            if (produto.imagem) {
                imgPrev.src = BASE_URL + produto.imagem;
                imgPrev.style.display = 'block';
                imgField.value = produto.imagem;
            } else {
                imgPrev.src = '';
                imgPrev.style.display = 'none';
                imgField.value = '';
            }

            if (modalProdutoInstance) {
                modalProdutoInstance.show();
            } else {
                document.getElementById('modalProduto').style.display = 'block';
            }
        }
    });
}

function deletarProduto(id) {
    if(!confirm('Tem certeza que deseja deletar este produto?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    fetch('api/produto_deletar.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if(data.success) { location.reload(); }
        else { alert(data.message); }
    });
}

function atualizarEstoque(id) {
    const quantidade = prompt('Digite a quantidade (use - para saída):');
    if(quantidade === null) return;
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('quantidade', quantidade);
    
    fetch('api/produto_estoque.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if(data.success) { location.reload(); }
        else { alert(data.message); }
    });
}

function showAlertModal(message, type) {
    const alertDiv = document.getElementById('alertModal');
    alertDiv.textContent = message;
    alertDiv.className = 'alert alert-' + type;
    alertDiv.style.display = 'block';
    setTimeout(() => alertDiv.style.display = 'none', 5000);
}

function filtrarProdutos() {
    const busca = document.getElementById('buscar').value.toLowerCase();
    const categoriaFiltro = document.getElementById('filtroCategoria').value;
    const linhas = document.querySelectorAll('#tabelaProdutos tr');
    
    linhas.forEach(linha => {
        const nome = linha.cells[1]?.textContent.toLowerCase() || '';
        const categoria = linha.cells[2]?.textContent || '';
        let mostrar = true;
        if(busca && !nome.includes(busca)) mostrar = false;
        if(categoriaFiltro && !categoria.includes(categoriaFiltro)) mostrar = false;
        linha.style.display = mostrar ? '' : 'none';
    });
}

function adicionarNovaCategoria() {
    const nomeCategoria = document.getElementById('novaCategoria').value.trim();
    const botao = event?.target || document.querySelector('button[onclick="adicionarNovaCategoria()"]');
    
    // Validações
    if(!nomeCategoria) {
        showAlertModal('Digite o nome da categoria', 'warning');
        document.getElementById('novaCategoria').focus();
        return;
    }
    
    if(nomeCategoria.length < 2) {
        showAlertModal('O nome deve ter pelo menos 2 caracteres', 'warning');
        return;
    }
    
    // Desabilitar botão durante o envio
    if(botao) botao.disabled = true;
    
    const formData = new FormData();
    formData.append('nome', nomeCategoria);
    
    fetch('api/categoria_cadastrar.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Adicionar a nova categoria ao select
            const select = document.getElementById('categoria_id');
            
            // Verificar se a categoria já existe no select
            const jaExiste = Array.from(select.options).some(opt => opt.value === String(data.categoria.id));
            
            if(!jaExiste) {
                const option = document.createElement('option');
                option.value = data.categoria.id;
                option.textContent = data.categoria.nome;
                select.appendChild(option);
            }
            
            // Selecionar a nova categoria
            select.value = data.categoria.id;
            
            // Limpar o input
            document.getElementById('novaCategoria').value = '';
            document.getElementById('novaCategoria').focus();
            
            // Mostrar mensagem de sucesso
            showAlertModal('✓ ' + data.message, 'success');
        } else {
            showAlertModal('❌ ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showAlertModal('Erro ao criar categoria', 'danger');
        console.error('Erro:', error);
    })
    .finally(() => {
        // Reabilitar botão
        if(botao) botao.disabled = false;
    });
}

