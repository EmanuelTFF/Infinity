<?php

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

// Filtros e pesquisa
$search = $_GET['search'] ?? '';
$price = $_GET['price'] ?? 10000;
$type = $_GET['type'] ?? '';

// Consulta SQL para pegar os produtos filtrados
$query = "SELECT * FROM products WHERE price <= :price";
$params = [':price' => $price];

if ($search) {
    $query .= " AND name LIKE :search";
    $params[':search'] = "%$search%";
}

if ($type) {
    $query .= " AND type_id = :type";
    $params[':type'] = $type;
}





$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Definir os tipos de produtos
$types = [
    1 => 'Headset',
    2 => 'Microfone',
    3 => 'Monitor',
    4 => 'Mousepad',
    5 => 'Mouse',
];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #bad1e5;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            margin: auto;
            padding: 20px;
        }

        .breadcrumbs {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .breadcrumbs a {
            text-decoration: none;
            color: #276a81;
        }

        h1 {
            text-align: center;
            color: #276a81;
        }

        /* Search bar and filters */
        .search-bar,
        .filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-bar input[type="text"],
        .filters select,
        .filters input[type="range"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .search-bar button,
        .filters button {
            background-color: #276a81;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        .search-bar button:hover,
        .filters button:hover {
            background-color: #276a81;
        }

        /* Products grid */
        .products {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .product {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .product img {
            max-width: 100%;
            border-radius: 5px;
        }

        .product h3 {
            font-size: 18px;
            color: #333;
            margin-top: 10px;
        }

        .product p {
            color: #276a81;
            font-size: 16px;
        }

        .product a {
            text-decoration: none;
        }

        .product .buy-now {
            background-color: #276a81;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            margin-top: 10px;
            display: block;
        }

        .product .buy-now:hover {
            background-color: #276a81;
        }

        /* Responsive styles */
        @media (max-width: 1200px) {
            .products {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .products {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .products {
                grid-template-columns: 1fr;
            }

            .search-bar,
            .filters {
                flex-direction: column;
                align-items: flex-start;
            }

            .filters input[type="range"],
            .filters select {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style><!-- Lembre-se de linkar o CSS -->
</head>

<body>

    <div class="container">
        <div class="breadcrumbs">
            <a href="index.php">Início</a> > <a href="#">Produtos</a>
        </div>

        <h1>Nossos Produtos</h1>

        <!-- Barra de Pesquisa -->
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Pesquisar produtos..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Pesquisar</button>
            </form>
        </div>

        <!-- Filtros -->
        <div class="filters">
            <form method="GET" action="">
                <div>
                    <label for="price">Filtrar por preço até: R$ <span
                            id="priceValue"><?php echo htmlspecialchars($price); ?></span></label>
                    <input type="range" name="price" id="price" min="0" max="2000"
                        value="<?php echo htmlspecialchars($price); ?>" step="100">
                </div>

                <div>
                    <label for="type">Categoria:</label>
                    <select name="type" id="type">
                        <option value="">Todos</option>
                        <?php foreach ($types as $id => $label): ?>
                            <option value="<?php echo $id; ?>" <?php echo ($id == $type) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit">Filtrar</button>
            </form>
        </div>

        <!-- Produtos -->
        <div class="products">
            <?php if ($products): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product">
                        <a href="produto.php?id=<?php echo $product['id']; ?>">
                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                            <h3><?php echo $product['name']; ?></h3>
                            <p>R$<?php echo number_format($product['price'], 2, ',', '.'); ?></p>
                            <span class="buy-now">Comprar</span>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum produto encontrado.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const priceSlider = document.getElementById('price');
        const priceValue = document.getElementById('priceValue');

        priceSlider.addEventListener('input', function () {
            priceValue.textContent = priceSlider.value;
        });
    </script>
</body>

</html>