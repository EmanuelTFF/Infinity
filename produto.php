<?php
session_start();
require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Verificar se o ID do produto foi passado na URL
$product_id = $_GET['id'] ?? null;
if ($product_id === null) {
    echo 'ID de produto não fornecido!';
    exit;
}
// Consultar detalhes do produto
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute([':id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o produto não for encontrado, exibe uma mensagem
if (!$product) {
    echo 'Produto não encontrado!';
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?></title>
    <style>
          /* Seu CSS de estilização permanece igual */
          * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #bad1e5;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .breadcrumbs {
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }

        .breadcrumbs a {
            color: #555;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumbs a:hover {
            color: #276a81;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin: 0 auto;
        }

        .image-gallery {
            flex: 0 1 40%;
            max-height: 600px;
            text-align: center;
            padding: 10px;
            position: relative;
            animation: fadeInLeft 1s ease-out;
        }

        .image-gallery img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .product-details {
            flex: 0 1 40%;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.1);
            animation: fadeInRight 1s ease-out;
            max-height: 600px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-details h1 {
            font-size: 2rem;
            color: #276a81;
            margin-bottom: 10px;
        }

        .product-details .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .product-details .price {
            font-size: 1.8rem;
            color: #276a81;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .product-details .parcelamento,
        .product-details .stock-info {
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        .product-details .btn-add-cart {
            display: inline-block;
            padding: 10px 20px;
            background-color: #276a81;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            animation: bounceIn 1.2s ease-out;
        }

        .product-details .btn-add-cart:hover {
            background-color: #276a81;
            transform: translateY(-5px);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }

            .product-details h1 {
                font-size: 1.5rem;
            }

            .product-details .price {
                font-size: 1.5rem;
            }

            .product-details .btn-add-cart {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>

    <div class="breadcrumbs">
        <a href="index.php">Início</a> >
        <a href="products.php">Produtos</a> >
        <span><?php echo $product['name']; ?></span>
    </div>

    <div class="container">
        <!-- Galeria de Imagens -->
        <div class="image-gallery">
            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
        </div>

        <!-- Detalhes do Produto -->
        <div class="product-details">
            <h1><?php echo $product['name']; ?></h1>
            <p class="original-price">De: R$ 2.067,59</p>
            <p class="price">Por: R$<?php echo number_format($product['price'], 2, ',', '.'); ?></p>
            <p class="parcelamento">Em até 12x de R$78,42 sem juros no cartão</p>
            <p class="stock-info">Pronta entrega 🚚</p>

            <form action="cart.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="btn-add-cart">Adicionar ao Carrinho</button>
            </form>
        </div>
    </div>

</body>

</html>