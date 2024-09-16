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
        body {
            font-family: 'Arial', sans-serif;
            background-color: #bad1e5;
            color: #333;
            padding: 20px;
            margin: 0;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .breadcrumbs {
            margin-bottom: 20px;
            font-size: 19px;
            text-align: center;
        }

        .breadcrumbs a {
            color: #276a81;
            text-decoration: none;
        }

        .breadcrumbs a:hover {
            text-decoration: underline;
        }

        .breadcrumbs span {
            color: #555;
        }

        .image-gallery {
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: auto;
        }

        .image-gallery img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .product-details {
            width: 100%;
            max-width: 600px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin: auto;
        }

        .product-details h1 {
            font-size: 24px;
            color: #276a81;
        }

        .price {
            font-size: 28px;
            color: #276a81;
            margin-top: 15px;
        }

        .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 18px;
        }

        .parcelamento {
            font-size: 14px;
            color: #555;
            margin-top: 10px;
        }

        .stock-info {
            margin-top: 10px;
            color: #276a81;
            font-weight: bold;
        }

        .btn-add-cart {
            display: inline-block;
            background-color: #276a81;
            color: white;
            padding: 15px 30px;
            font-size: 18px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s;
            width: 80%;
        }

        .btn-add-cart:hover {
            background-color: #1a4c5c;
        }
    </style>
</head>

<body><br><br><br><br><br>
    <div class="breadcrumbs">
        <a href="index.php">Inicio</a> >
        <a href="index.php">produtos</a> >
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

            <!-- Botão Adicionar ao Carrinho -->
            <form action="cart.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="btn-add-cart">Adicionar ao Carrinho</button>
            </form>
        </div>
    </div>
</body>

</html>
