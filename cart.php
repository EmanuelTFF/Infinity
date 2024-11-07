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
$sql_endereco = "SELECT u.endereco_salvo, u.cep, u.logradouro, u.bairro, u.numero, 
                        c.nome AS cidade, e.sigla AS estado
                 FROM users u
                 LEFT JOIN cidade c ON u.cidade_id = c.id
                 LEFT JOIN estado e ON u.estado_id = e.id
                 WHERE u.id = ?";
$stmt = $conn->prepare($sql_endereco);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_endereco = $stmt->get_result();
$user_endereco = $result_endereco->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cep'])) {
    // Supondo que você já tenha validado os dados
    $cep = $_POST['cep'];
    $logradouro = $_POST['logradouro'];
    $bairro = $_POST['bairro'];
    $cidade_nome = $_POST['cidade'];
    $estado_sigla = $_POST['estado'];
    $numero = $_POST['numero'];

    // Buscar o ID da cidade com base no nome
    $sql_cidade = "SELECT id FROM cidade WHERE nome = ?";
    $stmt_cidade = $conn->prepare($sql_cidade);
    $stmt_cidade->bind_param("s", $cidade_nome);
    $stmt_cidade->execute();
    $result_cidade = $stmt_cidade->get_result();
    $cidade = $result_cidade->fetch_assoc();

    if (!$cidade) {
        die("Cidade não encontrada");
    }
    $cidade_id = $cidade['id'];

    // Buscar o ID do estado com base na sigla
    $sql_estado = "SELECT id FROM estado WHERE sigla = ?";
    $stmt_estado = $conn->prepare($sql_estado);
    $stmt_estado->bind_param("s", $estado_sigla);
    $stmt_estado->execute();
    $result_estado = $stmt_estado->get_result();
    $estado = $result_estado->fetch_assoc();

    if (!$estado) {
        die("Estado não encontrado");
    }
    $estado_id = $estado['id'];

    // Atualizar o endereço do usuário com os IDs de cidade e estado
    $sql_save_address = "UPDATE users SET cep = ?, logradouro = ?, bairro = ?, cidade_id = ?, estado_id = ?, numero = ?, endereco_salvo = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql_save_address);
    $stmt->bind_param("sssiisi", $cep, $logradouro, $bairro, $cidade_id, $estado_id, $numero, $user_id);

    if ($stmt->execute()) {
        header("Location: ?success=true");
        exit();
    } else {
        die("Erro ao salvar endereço.");
    }
}



// Se não houver endereço salvo (campo 'endereco_salvo' for 0), mostrar o formulário
$mostrar_formulario_endereco = ($user_endereco['endereco_salvo'] == 0);

// Verificar se um produto foi adicionado ao carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Verificar se o produto já está no carrinho
    $check_cart = "SELECT * FROM cart WHERE users_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_cart);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Se o produto já estiver no carrinho, atualiza a quantidade
        $update_cart = "UPDATE cart SET quantity = quantity + 1 WHERE users_id = ? AND product_id = ?";
        $stmt = $conn->prepare($update_cart);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    } else {
        // Se o produto não estiver no carrinho, insere um novo item
        $add_to_cart = "INSERT INTO cart (users_id, product_id, quantity) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($add_to_cart);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    }
    
    // Redirecionar para a página de visualização do carrinho ou exibir uma mensagem de sucesso
    header('Location: cart.php');
    exit();
}

// Buscar itens do carrinho com detalhes dos produtos
$sql = "SELECT cart.id, cart.quantity, products.name AS product_name, products.price AS product_price, products.image_url AS product_image 
        FROM cart 
        INNER JOIN products ON cart.product_id = products.id 
        WHERE cart.users_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
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
    <style>
                /* Estilo geral */
                body {
            font-family: 'Poppins', sans-serif;
            background: #bad1e5;
            color: white;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Animação de fade-in e efeito hover */
        .cart-item {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeInUp 0.8s ease-in-out;
            color: #276a81;
        }

        .cart-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }

        .thumbnail {
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .thumbnail:hover {
            transform: scale(1.1);
        }

        /* Animação de fade-in */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Botões */
        button {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            transform: scale(1.05);
            background-color: #007BFF;
        }

        .remove-item {
            transition: background-color 0.3s ease;
        }

        .remove-item:hover {
            background-color: #e60023;
        }

        /* Estilo Geral do Formulário */
        form {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            margin: auto;
            animation: fadeInForm 0.8s ease-in-out;
            transition: all 0.3s ease;
        }

        /* Input e Textarea */
        input,
        textarea {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #276a81;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.15);
            color: #276a81;
            font-size: 1rem;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        input::placeholder,
        textarea::placeholder {
            color: #7da6b1;
        }

        input:focus,
        textarea:focus {
            background-color: rgba(255, 255, 255, 0.25);
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            outline: none;
        }

        /* Responsividade */
        @media (max-width: 1024px) {
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                text-align: center;
            }

            .cart-item-info {
                margin-top: 1rem;
            }
        }

        @media (max-width: 768px) {
            .cart-item {
                padding: 0.5rem;
            }

            .thumbnail {
                width: 80px;
                height: 80px;
            }
        }

        /* Mensagem de sucesso */
        #success-message {
            background-color: #28a745;
            color: white;
            position: fixed;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.5s ease-in-out;
        }

        #success-message.show {
            top: 20px;
        }

        @keyframes fadeInForm {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsividade */
        @media (max-width: 768px) {
            form {
                padding: 1.5rem;
            }

            input,
            textarea {
                font-size: 0.9rem;
                padding: 0.5rem;
            }

            button {
                font-size: 1rem;
                padding: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            form {
                padding: 1rem;
            }

            input,
            textarea {
                font-size: 0.85rem;
            }

            button {
                font-size: 0.95rem;
            }
        }
        .text-total {
    color: #276a81;
}
#frete-info{
    
    color: #276a81;
}
.frete-label {
            color: #276a81; /* Define a cor do rótulo do valor do frete */
            font-weight: bold; /* Negrito */

}

    </style>
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
        <?php if ($mostrar_formulario_endereco): ?>
    <div class="space-y-4">
        <form method="POST" action="">
            <input type="text" id="cep" name="cep" placeholder="CEP" required onblur="buscarCep(this.value)" />
            <input type="text" id="logradouro" name="logradouro" placeholder="Logradouro" required />
            <input type="text" id="bairro" name="bairro" placeholder="Bairro" required />
            <input type="text" id="cidade" name="cidade" placeholder="Cidade" required />
            <input type="text" id="estado" name="estado" placeholder="Estado" required />
            <input type="text" id="numero" name="numero" placeholder="Número" required />
            <button type="submit">Salvar Endereço</button>
        </form>
    </div>
<?php else: ?>
    <p class="text-green-500">Endereço já salvo.</p>
<?php endif; ?>

<script>
    function buscarCep(cep) {
        // Remove qualquer caractere que não seja número
        cep = cep.replace(/\D/g, '');

        if (cep.length === 8) { // Verifica se o CEP tem 8 dígitos
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        // Preenche os campos com os dados recebidos
                        document.getElementById('logradouro').value = data.logradouro;
                        document.getElementById('bairro').value = data.bairro;
                        document.getElementById('cidade').value = data.localidade;
                        document.getElementById('estado').value = data.uf;
                    } else {
                        alert('CEP não encontrado.');
                    }
                })
                .catch(error => {
                    alert('Erro ao buscar o CEP. Tente novamente.');
                    console.error(error);
                });
        } else {
            alert('CEP inválido. Digite um CEP com 8 dígitos.');
        }
    }
</script>

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

            ?>
        </div>

        <!-- Cálculo de Frete -->
        <div class="flex justify-between items-center mt-4">
            <div>
            <label for="frete" class="frete-label">Valor do Frete</label>
                <input type="number" id="frete" class="border border-gray-300 rounded w-24 px-2 py-1" value="0.00"
                    min="0" step="0.01" onchange="updateCartTotal()">
            </div>
            <div class="cart-total text-right text-xl font-bold text-total">Total: R$ 0,00</div>
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


                <a href="checkout.php"><button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Finalizar Compra
                    </button>
                </a>
                <a href="products.php"><button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Adicionar mais produtos
                    </button>
                </a>

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
        <?php endif; ?>
    </script>
</body>
<script src="js/script.js"></script>

</html>