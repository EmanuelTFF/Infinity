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

// Consultar avaliações para o produto
$sql_reviews = 'SELECT pr.rating, pr.comment, 
                IF(pr.anonymous = 1, CONCAT("anônimo#", pr.id), IFNULL(pr.username, u.full_name)) AS display_name 
                FROM product_reviews pr 
                INNER JOIN users u ON pr.users_id = u.id 
                WHERE pr.products_id = :product_id 
                ORDER BY pr.id DESC';



$stmt_reviews = $pdo->prepare($sql_reviews);
$stmt_reviews->execute([':product_id' => $product_id]);
$reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <style>
        /* Estilo CSS */
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
            align-items: flex-start;
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
            background-color: #215f69;
            transform: translateY(-5px);
        }

        .review-section {
            margin-top: 40px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.1);
        }

        .review-section h2 {
            margin-bottom: 20px;
            color: #276a81;
        }

        .review {
            margin-bottom: 15px;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }

        .review .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .review .review-header h4 {
            margin: 0;
            color: #555;
        }

        .review .review-header .stars {
            color: gold;
        }

        .review .review-content {
            margin-top: 10px;
        }

        .review .review-content p {
            margin: 0;
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
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </div>

    <div class="container">
        <!-- Galeria de Imagens -->
        <div class="image-gallery">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>

        <!-- Detalhes do Produto -->
        <div class="product-details">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="original-price">De: R$ 2.067,59</p>
            <p class="price">Por: R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></p>
            <p class="parcelamento">Em até 12x de R$78,42 sem juros no cartão</p>
            <p class="stock-info">Pronta entrega 🚚</p>

            <form action="cart.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="btn-add-cart">Adicionar ao Carrinho</button>
            </form>
        </div>
    </div>

    <br>
    <br>
    <br>
    <br>
    <br>

    <!-- Seção de Avaliações -->
    <div class="review-section">
        <h2>Avaliações</h2>
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div class="review-header">
                        <h4><?php echo htmlspecialchars($review['display_name']); ?></h4>
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span><?php echo $i <= $review['rating'] ? '★' : '☆'; ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="review-content">
                        <p><?php echo htmlspecialchars($review['comment']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Não há avaliações para este produto.</p>
        <?php endif; ?>
    </div>

</body>

</html>