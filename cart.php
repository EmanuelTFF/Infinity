<?php
session_start();

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verificar se o usuário já tem endereço salvo na tabela 'users'
$sql_endereco = "SELECT endereco_salvo, cep, logradouro, bairro, cidade, estado, numero 
                 FROM users 
                 WHERE id = ?";
$stmt = $conn->prepare($sql_endereco);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_endereco = $stmt->get_result();
$user_endereco = $result_endereco->fetch_assoc();

// Se não houver endereço salvo (campo 'endereco_salvo' for 0), mostrar o formulário
$mostrar_formulario_endereco = ($user_endereco['endereco_salvo'] == 0);

// Buscar itens do carrinho com detalhes dos produtos
$sql = "SELECT cart.id, cart.quantity, products.name AS product_name, products.price AS product_price, products.image_url AS product_image 
        FROM cart 
        INNER JOIN products ON cart.product_id = products.id 
        WHERE cart.users_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Verificar se um produto foi adicionado ao carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Verificar se o produto já está no carrinho
    $check_cart = "SELECT * FROM cart WHERE users_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_cart);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>



    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Carrinho de Compras</title>
        <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">
    </head>

    <body>
        <!-- Mensagem de Sucesso -->
        <div id="success-message"
            class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-4 py-2 rounded shadow-lg transition-opacity duration-500 opacity-0">
            Endereço salvo com sucesso!
        </div>

        <header>
            <nav class="navigation bg-gray-800 text-white p-4">
                <a href="#" class="logo text-2xl font-bold">Infi<span class="text-blue-500">ni</span>ty<span
                        class="text-blue-500">te</span>ch</a>
                <div class="nav ml-auto">
                    <a href="cart.php" class="flex items-center"><i class='bx bx-cart-alt text-2xl mr-2'></i> Carrinho</a>
                </div>
                <ul class="nav-menu flex space-x-4 ml-auto">
                    <li class="nav-item"><a href="index.php" class="hover:underline">Inicio</a></li>
                    <li class="nav-item"><a href="products.php" class="hover:underline">Produtos</a></li>
                    <li class="nav-item"><a href="help.html" class="hover:underline">Ajuda</a></li>
                    <li class="nav-item"><a href="perfil.php" class="hover:underline">Perfil</a></li>
                </ul>
                <div class="menu ml-4 cursor-pointer" onclick="toggleMenu()">
                    <span class="bar block w-6 h-1 bg-white mb-1"></span>
                    <span class="bar block w-6 h-1 bg-white mb-1"></span>
                    <span class="bar block w-6 h-1 bg-white"></span>
                </div>
            </nav>
        </header>

        <main class="container mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-4">Carrinho de Compras</h1>

            <!-- Formulário de Endereço -->
            <?php if ($mostrar_formulario_endereco): ?>
                <div class="space-y-4">
                    <form action="salvar_endereco.php" method="post" id="form-endereco">
                        <h2 class="text-xl font-bold mb-2">Informações de Entrega</h2>
                        <input type="text" id="cep" name="cep" placeholder="Digite o CEP" required pattern="\d{5}-?\d{3}"
                            onblur="buscarCEP()" class="border border-gray-300 rounded w-full px-2 py-1 mb-2">
                        <input type="text" id="logradouro" name="logradouro" placeholder="Logradouro" required readonly
                            class="border border-gray-300 rounded w-full px-2 py-1 mb-2">
                        <input type="text" id="bairro" name="bairro" placeholder="Bairro" required readonly
                            class="border border-gray-300 rounded w-full px-2 py-1 mb-2">
                        <input type="text" id="cidade" name="cidade" placeholder="Cidade" required readonly
                            class="border border-gray-300 rounded w-full px-2 py-1 mb-2">
                        <input type="text" id="estado" name="estado" placeholder="Estado" required readonly
                            class="border border-gray-300 rounded w-full px-2 py-1 mb-2">
                        <input type="text" id="numero" name="numero" placeholder="Número" required
                            class="border border-gray-300 rounded w-full px-2 py-1 mb-2">
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Salvar
                            Endereço</button>
                    </form>
                </div>
            <?php else: ?>
                <p class="text-green-500">Endereço já salvo.</p>
            <?php endif; ?>

            <div class="space-y-4 mt-8">
                <!-- Exibir Itens do Carrinho -->
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='cart-item flex items-center bg-gray-100 p-4 rounded-lg shadow-md' data-id='" . $row['id'] . "'>";
                        echo "<img src='" . $row['product_image'] . "' alt='" . $row['product_name'] . "' class='thumbnail w-20 h-20 object-cover rounded-lg mr-4'>";
                        echo "<div class='cart-item-info flex-grow'>";
                        echo "<h4 class='font-semibold text-lg'>" . $row['product_name'] . "</h4>";
                        echo "<span class='cart-item-price text-blue-500 font-bold'>R$ " . number_format($row['product_price'], 2, ',', '.') . "</span>";
                        echo "<input type='number' class='cart-item-quantity border border-gray-300 rounded w-16 ml-4' value='" . $row['quantity'] . "' min='1' onchange='updateCartTotal()'>";
                        echo "<button class='ml-4 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 remove-item' data-cart-id='" . $row['id'] . "'>Remover</button>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='text-gray-500'>Carrinho vazio!</p>";
                }
                $stmt->close();
                $conn->close();
                ?>
            </div>

            <!-- Cálculo de Frete -->
            <div class="flex justify-between items-center mt-4">
                <div>
                    <label for="frete" class="block text-gray-700">Valor do Frete</label>
                    <input type="number" id="frete" class="border border-gray-300 rounded w-24 px-2 py-1" value="0.00"
                        min="0" step="0.01" onchange="updateCartTotal()">
                </div>
                <div class="cart-total text-right text-xl font-bold">Total: R$ 0,00</div>
            </div>

            <!-- Formulário para Calcular Frete -->
            <div class="mt-4">
                <label for="cep-frete" class="block text-gray-700">Calcular Frete</label>
                <input type="text" id="cep-frete" class="border border-gray-300 rounded w-48 px-2 py-1"
                    placeholder="Digite seu CEP">
                <button class="ml-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    onclick="calcularFrete()">Calcular</button>
            </div>
            <?php if ($result->num_rows > 0): ?>
                <div class="mt-8 text-right">
                    <form action="checkout.php" method="GET">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Finalizar Compra
                        </button>
                    </form>
                </div>
            <?php endif; ?>


            <div id="frete-info" class="mt-4"></div>
        </main>

        <script>
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
                    const cartId = this.getAttribute('data-cart-id');
                    fetch(`remover_item.php?id=${cartId}`)
                        .then(response => response.text())
                        .then(data => {
                            if (data === 'success') {
                                this.closest('.cart-item').remove();
                                updateCartTotal();
                            } else {
                                alert('Erro ao remover item do carrinho.');
                            }
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
            <?php endif; ?>
        </script>
    </body>
    <script src="js/script.js"></script>
    </html>