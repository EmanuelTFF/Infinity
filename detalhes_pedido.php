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

if (!isset($_GET['id'])) {
    header("Location: meus_pedidos.php");
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Consulta o pedido específico
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
$sql_items = "SELECT products.name, orders_items.quantity, products.price 
              FROM orders_items 
              INNER JOIN products ON orders_items.products_id = products.id 
              WHERE orders_items.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido</title>
    <style>
        /* Reset de margin e padding */
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
            margin: auto;
        }

        .page-title {
            text-align: center;
            font-size: 2.8rem;
            color: #333;
            margin-bottom: 30px;
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
        }

        .page-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: #276a81;
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            border-radius: 5px;
        }

        .order-details {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 1s ease-out;
            border: 1px solid #e0e0e0;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border 0.3s ease;
        }

        .order-details:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            
        }

        .order-details p {
            font-size: 1.2rem;
            margin-bottom: 15px;
            position: relative;
            padding-left: 30px;
        }

        .order-details p::before {
            content: '✔️';
            position: absolute;
            left: -5px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
            color: #007bff;
        }

        .order-items {
            list-style: none;
            padding: 0;
        }

        .order-items li {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            animation: fadeIn 0.8s ease-out;
            transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        }

        .order-items li:nth-child(even) {
            background: #ececec;
        }

        .order-items li:hover {
            background: #e0e0e0;
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .back-btn {
            display: block;
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            color: #fff;
            background:  #276a81;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            margin-top: 30px;
        }

        .back-btn:hover {
            background: #276a81;
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        /* Animações */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 10px;
            }

            .page-title {
                font-size: 2.2rem;
            }

            .order-details p {
                font-size: 1rem;
            }

            .back-btn {
                font-size: 1rem;
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.8rem;
            }

            .order-details p {
                font-size: 0.9rem;
            }

            .back-btn {
                font-size: 0.9rem;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="page-title">Detalhes do Pedido</h1>
        <div class="order-details" data-aos="fade-up">

            <p><strong>Data do Pedido:</strong> <?= date("d/m/Y", strtotime($order['order_date'])); ?></p>
            <p><strong>Total Pago:</strong> R$ <?= number_format($order['total'], 2, ',', '.'); ?></p>
            <p><strong>Endereço de Entrega:</strong> <?= $order['customer_address']; ?></p>
            <p><strong>Método de Pagamento:</strong> <?= $order['payment_method']; ?></p>

            <h2>Itens do Pedido:</h2>
            <ul class="order-items">
                <?php while ($item = $result_items->fetch_assoc()): ?>
                    <li>
                        <?= $item['name']; ?> - Quantidade: <?= $item['quantity']; ?> -
                        Preço: R$ <?= number_format($item['price'], 2, ',', '.'); ?>
                    </li>
                <?php endwhile; ?>
            </ul>

            <button class="back-btn" onclick="window.location.href='meus_pedidos.php'">Voltar para Meus Pedidos</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>

</html>

<?php
$stmt_order->close();
$stmt_items->close();
$conn->close();
?>