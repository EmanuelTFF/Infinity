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

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validar o ID do pedido
if (!isset($_GET['id'])) {
    header("Location: meus_pedidos.php");
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$success_message = "";

// Consulta o pedido específico para verificar se pertence ao usuário logado
$sql_order = "SELECT * FROM orders WHERE id = ? AND users_id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("ii", $order_id, $user_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc();

if (!$order) {
    echo "Pedido não encontrado.";
    exit();
}

// Consulta os itens do pedido
$sql_items = "SELECT products.id, products.name, orders_items.quantity, products.price 
              FROM orders_items 
              INNER JOIN products ON orders_items.products_id = products.id 
              WHERE orders_items.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

// Processar o formulário de avaliação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = $conn->real_escape_string($_POST['comment']);
    $is_anonymous = isset($_POST['anonymous']) && $_POST['anonymous'] === '1'; // Verifica se foi marcado anônimo

    // Validar o rating
    if ($rating >= 1 && $rating <= 5) {
        if ($is_anonymous) {
            // Gerar um nome anônimo
            $sql_last_anonymous = "SELECT COUNT(*) as total FROM product_reviews WHERE full_name LIKE 'anônimo#%'";
            $result_anonymous = $conn->query($sql_last_anonymous);
            $row_anonymous = $result_anonymous->fetch_assoc();
            $anonymous_count = $row_anonymous['total'] + 1;
            $username = "anônimo#" . $anonymous_count; // Nome anônimo gerado
        } else {
            // Pegar o full_name da tabela users com base no user_id
            $sql_full_name = "SELECT full_name FROM users WHERE id = ?";
            $stmt_full_name = $conn->prepare($sql_full_name);
            $stmt_full_name->bind_param("i", $user_id);
            $stmt_full_name->execute();
            $result_full_name = $stmt_full_name->get_result();
            $user = $result_full_name->fetch_assoc();

            if ($user) {
                $username = $user['full_name']; // Nome real do usuário
            } else {
                $username = "Usuário desconhecido"; // Valor de fallback
            }

            $stmt_full_name->close();
        }

        // Inserir a avaliação no banco de dados
        $sql_review = "INSERT INTO product_reviews (full_name, users_id, products_id, orders_id, rating, comment, anonymous) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_review = $conn->prepare($sql_review);
        $stmt_review->bind_param("siiiisi", $username, $user_id, $product_id, $order_id, $rating, $comment, $is_anonymous);

        if ($stmt_review->execute()) {
            $success_message = "Avaliação postada com sucesso!";
        } else {
            $success_message = "Erro ao postar a avaliação. Por favor, tente novamente.";
        }

        $stmt_review->close();
    } else {
        $success_message = "A avaliação deve estar entre 1 e 5 estrelas.";
    }
}
?>


?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido</title>
    <style>
        /* CSS para estilizar a página */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: #bad1e5;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .page-title {
            text-align: center;
            font-size: 3rem;
            color: #333;
            margin-bottom: 40px;
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
            letter-spacing: 1px;
        }

        .order-details {
            background: #f7f7f7;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .order-details p {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: #555;
        }

        .order-items li {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            transition: background 0.3s ease;
        }

        .order-items li:hover {
            background: #f0f0f0;
        }

        .review-form {
            background: #ececec;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            transition: transform 0.3s ease;
        }

        .review-form:hover {
            transform: translateY(-5px);
        }

        .review-form h3 {
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: #333;
        }

        .rating-stars {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .rating-stars input[type="radio"] {
            display: none;
        }

        .rating-stars label {
            font-size: 2.5rem;
            color: #ccc;
            transition: color 0.2s ease;
            cursor: pointer;
        }

        .rating-stars input[type="radio"]:checked~label {
            color: gold;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 1rem;
            color: #555;
        }

        textarea:focus {
            border-color: #276a81;
            outline: none;
        }

        button[type="submit"],
        .back-btn {
            background: #276a81;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button[type="submit"]:hover,
        .back-btn:hover {
            background: #276a81;
        }

        .success-message {
            color: green;
            font-size: 1.2rem;
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="page-title">Detalhes do Pedido</h1>

        <div class="order-details">
            <p><strong>Data do Pedido:</strong> <?= date("d/m/Y", strtotime($order['order_date'])); ?></p>
            <p><strong>Total Pago:</strong> R$ <?= number_format($order['total'], 2, ',', '.'); ?></p>
            <p><strong>Endereço de Entrega:</strong> <?= $order['customer_address']; ?></p>
            <p><strong>Método de Pagamento:</strong> <?= $order['payment_method']; ?></p>

            <h2>Itens do Pedido</h2>
            <ul class="order-items">
                <?php while ($item = $result_items->fetch_assoc()) : ?>
                    <li>
                        <p><strong><?= htmlspecialchars($item['name']); ?></strong></p>
                        <p><strong>Quantidade:</strong> <?= $item['quantity']; ?></p>
                        <p><strong>Preço Unitário:</strong> R$ <?= number_format($item['price'], 2, ',', '.'); ?></p>
                    </li>

                    <!-- Formulário de Avaliação -->
                    <form class="review-form" action="detalhes_pedido.php?id=<?= $order_id; ?>" method="post">
                        <input type="hidden" name="product_id" value="<?= $item['id']; ?>">
                        <h3>Avaliar Produto</h3>

                        <!-- Estrelas de Avaliação -->
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                <input type="radio" id="star<?= $i; ?>-<?= $item['id']; ?>" name="rating" value="<?= $i; ?>">
                                <label for="star<?= $i; ?>-<?= $item['id']; ?>">&#9733;</label>
                            <?php endfor; ?>
                        </div>

                        <textarea name="comment" rows="4" placeholder="Escreva um comentário sobre o produto..."></textarea>

                        <label>
                        <input type="checkbox" name="anonymous" value="1"> Avaliar anonimamente
                        </label>

                        <br>
                        <button type="submit">Enviar Avaliação</button>
                    </form>
                <?php endwhile; ?>
            </ul>
        </div>

        <a href="meus_pedidos.php" class="back-btn">Voltar aos Meus Pedidos</a>

        <?php if (!empty($success_message)) : ?>
            <div class="success-message"><?= $success_message; ?></div>
        <?php endif; ?>
    </div>
</body>

</html>
