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
            padding: 20px;
        }

        .product {
            display: inline-block;
            width: 200px;
            margin: 10px;
            text-align: center;
        }

        .product img {
            width: 100%;
            height: auto;
        }

        .filters {
            margin-bottom: 20px;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #bad1e5;
            color: #333;
            padding: 20px;
            margin: 0;
        }

        h1 {
            text-align: center;
            color: #276a81;
            margin-bottom: 20px;
        }

        .search-bar,
        .filters {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-bar form,
        .filters form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        input[type="text"],
        input[type="number"],
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            max-width: 300px;
        }

        button {
            background-color: #276a81;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #3498db;
        }

        .products {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .product {
            display: flex;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            text-align: left;
            transition: transform 0.2s;
            width: 100%;
            max-width: 600px;
            padding: 15px;
            box-sizing: border-box;
        }

        .product img {
            width: 150px;
            height: auto;
            margin-right: 20px;
        }

        .product-details {
            flex: 1;
        }

        .product h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #276a81;
        }

        .product p {
            font-size: 16px;
            color: #276a81;
            margin: 5px 0;
        }

        .product:hover {
            transform: scale(1.02);
        }

        /* Para telas pequenas, mantém o layout lado a lado */
        @media (max-width: 768px) {
            h1 {
                font-size: 24px;
            }

            .product {
                width: 100%;
                flex-direction: row;
                /* Mantém imagem e conteúdo lado a lado */
            }

            .product img {
                width: 120px;
                /* Ajuste o tamanho da imagem para telas pequenas */
            }

            input[type="text"],
            input[type="number"],
            select,
            button {
                font-size: 14px;
                padding: 8px;
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .product {
                width: 100%;
                flex-direction: row;
                /* Garante que o layout continue lado a lado */
            }

            .product img {
                width: 100px;
                /* Diminui a imagem ainda mais para caber em telas menores */
            }

            input[type="text"],
            input[type="number"],
            select,
            button {
                font-size: 12px;
                padding: 6px;
            }
        }

        @media (min-width: 1024px) {

            .search-bar,
            .filters {
                flex-direction: column;
            }

            .search-bar form,
            .filters form {
                flex-direction: column;
                align-items: center;
            }

        }

        a {
            text-decoration: none;
        }

        .breadcrumbs {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            margin-bottom: 20px;
            /* espaço entre o breadcrumbs e o conteúdo */
        }

        .breadcrumbs a {
            color: #276a81;
            text-decoration: none;
            margin: 0 5px;
            /* espaço entre os links */
        }

        .breadcrumbs a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="breadcrumbs">
        <a href="index.php">Inicio</a> >
        <a href="#">produtos</a>

    </div>

    <br>
    <br>
    <br>
    <h1>Nossos produtos</h1>

    <!-- Barra de Pesquisa -->
    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Pesquisar produtos..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Pesquisar</button>
        </form>
    </div>

    <!-- Filtros de Preço e Tipo -->
    <div class="filters">
        <form method="GET" action="">
            <label for="price">Filtrar por preço até: R$</label>
            <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($price); ?>">
            <label for="type">Categoria:</label>
            <select name="type" id="type">
                <option value="">Todos</option>
                <?php foreach ($types as $id => $label): ?>
                    <option value="<?php echo $id; ?>" <?php echo ($id == $type) ? 'selected' : ''; ?>><?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Filtrar</button>
        </form>
    </div>

    <!-- Exibição dos Produtos -->
    <div class="products">
        <?php if ($products): ?>
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <a href="produto.php?id=<?php echo $product['id']; ?>">
                        <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                        <h3><?php echo $product['name']; ?></h3>
                        <p>R$<?php echo number_format($product['price'], 2, ',', '.'); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhum produto encontrado.</p>
        <?php endif; ?>
    </div>


</body>

</html>