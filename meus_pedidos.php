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

$user_id = $_SESSION['user_id'];  // Variável da sessão

$sql = "SELECT orders.id, orders.total, orders.order_date, GROUP_CONCAT(products.name SEPARATOR ', ') as products
        FROM orders
        INNER JOIN orders_items ON orders.id = orders_items.order_id
        INNER JOIN products ON orders_items.products_id = products.id
        WHERE orders.users_id = ?
        GROUP BY orders.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #bad1e5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .page-title {
            text-align: center;
            font-size: 36px;
            color: #333;
            margin-bottom: 20px;
            animation: fadeInDown 1s ease-in-out;
        }

        .orders {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .order-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .order-card:hover {
            transform: scale(1.03);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .order-info {
            margin-bottom: 15px;
        }

        .order-info h2 {
            font-size: 24px;
            color: #555;
            margin-bottom: 10px;
        }

        .order-info p {
            font-size: 18px;
            color: #666;
            line-height: 1.5;
        }

        .view-details {
            background-color: #276a81;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            position: absolute;
            bottom: 20px;
            right: 20px;
        }

        .view-details:hover {
            background-color: #276a81;
        }

        .back-btn {
            display: block;
            margin: 20px auto;
            padding: 15px 30px;
            background-color: #f44336;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #d32f2f;
        }

        /* Animações */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .order-card {
                padding: 15px;
            }

            .order-info h2 {
                font-size: 20px;
            }

            .order-info p {
                font-size: 16px;
            }

            .view-details {
                padding: 8px 16px;
            }

            .back-btn {
                font-size: 16px;
                padding: 10px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="page-title">Meus Pedidos</h1>
        <div class="orders">
            <?php while ($order = $result->fetch_assoc()): ?>
                <div class="order-card" data-aos="fade-up" data-aos-duration="1000">
                    <div class="order-info">
                        <h2>Pedido realizado em: <?= date("d/m/Y", strtotime($order['order_date'])); ?></h2>
                        <p><strong>Produtos:</strong> <?= $order['products']; ?></p>
                        <p><strong>Total:</strong> R$ <?= number_format($order['total'], 2, ',', '.'); ?></p>
                    </div>
                    <button class="view-details"
                        onclick="window.location.href='detalhes_pedido.php?id=<?= $order['id']; ?>'">Ver detalhes</button>
                </div>
            <?php endwhile; ?>
        </div>
        <button class="back-btn" onclick="window.location.href='index.php'">Voltar à loja</button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>