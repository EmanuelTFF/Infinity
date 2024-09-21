function preencherFormulario(data) {
    document.getElementById('logradouro').value = data.logradouro;
    document.getElementById('bairro').value = data.bairro;
    document.getElementById('cidade').value = data.localidade;
    document.getElementById('estado').value = data.uf;
}

function buscarCEP() {
    const cep = document.getElementById('cep').value.replace(/\D/g, '');
    if (cep.length === 8) {
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    preencherFormulario(data);
                } else {
                    alert('CEP não encontrado!');
                }
            });
    }
}

function updateCartTotal() {
    const items = document.querySelectorAll('.cart-item');
    let total = 0;
    items.forEach(item => {
        const price = parseFloat(item.querySelector('.cart-item-price').textContent.replace('R$', '').replace(',', '.'));
        const quantity = parseInt(item.querySelector('.cart-item-quantity').value);
        total += price * quantity;
    });
    const frete = parseFloat(document.getElementById('frete').value);
    total += frete;
    document.querySelector('.cart-total').textContent = `Total: R$ ${total.toFixed(2).replace('.', ',')}`;
}

function calcularFrete() {
    const cep = document.getElementById('cep-frete').value;
    if (cep.length === 8) {
        // Simulação de cálculo de frete (pode ser substituído por lógica real)
        const valorFrete = Math.random() * 20 + 5; // Gera um valor entre 5 e 25
        document.getElementById('frete').value = valorFrete.toFixed(2);
        document.getElementById('frete-info').textContent = `Valor do frete: R$ ${valorFrete.toFixed(2).replace('.', ',')}`;
        updateCartTotal();
    } else {
        alert('Por favor, insira um CEP válido para calcular o frete.');
    }
}

document.querySelectorAll('.remove-item').forEach(button => {
    button.addEventListener('click', function () {
        const cartId = this.getAttribute('data-cart-id'); // Assumindo que você tem um botão com o atributo 'data-cart-id'

        fetch('remove_item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}`,
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.closest('.cart-item').remove(); // Remove o item visualmente
                   
                } else {
                    alert(data.message || 'Erro ao remover o item do carrinho');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a requisição.');
            });
    });
});

// Exibir mensagem de sucesso se o endereço foi salvo
<?php if (isset($_GET['success']) && $_GET['success'] == 'true'): ?>
    const successMessage = document.getElementById('success-message');
    successMessage.classList.remove('hidden');
    setTimeout(() => {
        successMessage.classList.add('opacity-100');
    }, 100);

    setTimeout(() => {
        successMessage.classList.remove('opacity-100');
        setTimeout(() => {
            successMessage.classList.add('hidden');
        }, 500);
    }, 3000);
