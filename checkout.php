<?php
session_start();

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Conexão com o banco de dados
$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Buscar itens do carrinho com detalhes dos produtos
$sql = "SELECT cart.quantity, products.name AS product_name, products.price AS product_price, products.image_url AS product_image
        FROM cart
        INNER JOIN products ON cart.product_id = products.id
        WHERE cart.users_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$cart_items = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row['product_price'] * $row['quantity'];
    }
} else {
    echo "Carrinho vazio!";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/checkout.css">
</head>

<body>
    <div class="container">
        <h1></h1>

        <!-- Exibir Itens do Carrinho -->
        <div class="cart-items">
            <h2>Finalizar compra</h2>
            <ul>
                <?php foreach ($cart_items as $item): ?>
                    <li style="display: flex; align-items: center;">
                        <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_name']; ?>"
                            style="width: 100px; height: auto; margin-right: 15px;">

                        <div>
                            <strong><?php echo $item['product_name']; ?></strong>
                            <br>
                            Quantidade: <?php echo $item['quantity']; ?> -
                            Preço: R$ <?php echo number_format($item['product_price'], 2, ',', '.'); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Exibir Total -->
        <div class="cart-total">
            <h3>Total: R$ <?php echo number_format($total, 2, ',', '.'); ?></h3>
        </div>

        <!-- Formulário de Pagamento -->
        <div class="form-card">
            <h2>Informações de Pagamento</h2>
            <form action="process_order.php" method="POST">
                <div>
                    <label for="customer_name">Nome Completo:</label>
                    <input type="text" id="customer_name" name="customer_name" required>
                </div>
                <div>
                    <label for="customer_email">Email:</label>
                    <input type="email" id="customer_email" name="customer_email" required>
                </div>
                <div>
                    <label for="customer_phone">Número de Telefone:</label>
                    <input type="tel" id="customer_phone" name="customer_phone"
                        pattern="\+[0-9]{2}\s?[0-9]{2}\s?[0-9]{5}-?[0-9]{4}" placeholder="+55 11 91234-5678" required>
                    <small>Formato esperado: +55 11 91234-5678</small>
                </div>
                <div>
                    <label for="customer_address">Endereço:</label>
                    <input type="text" id="customer_address" name="customer_address" required>
                </div>
                <div>
                    <label for="payment_method">Método de Pagamento:</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="PIX">PIX</option>
                        <option value="CreditCard">Cartão de Crédito</option>
                    </select>
                </div>
                <div id="card_details" style="display:none;">
                    <div>
                        <label for="card_number">Número do Cartão:</label>
                        <input type="text" id="card_number" name="card_number" pattern="[0-9]{16}"
                            placeholder="1234123412341234">
                    </div>
                    <div>
                        <label for="card_expiry">Validade do Cartão (MM/AA):</label>
                        <input type="text" id="card_expiry" name="card_expiry" pattern="\d{2}/\d{2}"
                            placeholder="MM/AA">
                    </div>
                    <div>
                        <label for="card_cvv">CVV:</label>
                        <input type="text" id="card_cvv" name="card_cvv" pattern="[0-9]{3}" placeholder="123">
                    </div>
                </div>

                <!-- Campo Oculto com os Produtos do Carrinho -->
                <input type="hidden" name="cart_items"
                    value="<?php echo htmlspecialchars(json_encode($cart_items)); ?>">

                <button type="submit">Comprar</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('payment_method').addEventListener('change', function () {
            if (this.value === 'CreditCard') {
                document.getElementById('card_details').style.display = 'block';
            } else {
                document.getElementById('card_details').style.display = 'none';
            }
        });
    </script>
</body>

</html>
