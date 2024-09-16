<?php
// search.php

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Obtém a consulta de pesquisa
$query = isset($_GET['query']) ? $_GET['query'] : '';

$sql = "SELECT * FROM products WHERE name LIKE ?";
$stmt = $conn->prepare($sql);
$search = "%$query%";
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Resultados da Pesquisa</title>
    <style>
        .container {
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .breadcrumbs {
            display: inline-block;
            margin-bottom: 1rem;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
        }

        .card {
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 10px;
            width: 250px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .card img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .card h3 {
            font-size: 18px;
            margin: 15px 0;
        }

        .card p {
            font-size: 16px;
            color: #333;
        }

        .price {
            font-size: 20px;
            font-weight: bold;
            color: #007BFF;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        .search-results h2 {
            text-align: center;
            font-size: 24px;
            margin: 20px 0;
            font-weight: bold;
        }

        .action-buttons {
            margin-top: 10px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .rating {
            margin-top: 10px;
            color: #ffc107;
        }

        .filter-container {
            margin-bottom: 20px;
        }

        .filter-container select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>
    <header>
        <nav class="navigation">
            <a href="#" class="logo">Infi<span>ni</span>ty<span>tech</span></a>
            <div class="nav">
                <a href="cart.php" style="color:black;" onclick="toggleCart()"><i class='bx bx-cart-alt'> Carrinho</i></a>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php">Inicio</a></li>
                <li class="nav-item"><a href="#">Produtos</a></li>
                <li class="nav-item"><a href="#">Ajuda</a></li>
                <li class="nav-item">
                    <?php if (isset($_SESSION['administrador']) && $_SESSION['administrador'] == 'yes'): ?>
                        <a href="admin_dashboard.php">Admin Dashboard</a>
                    <?php else: ?>
                        <a href="perfil.php">Perfil</a>
                    <?php endif; ?>
                </li>
            </ul>
            <div class="menu" onclick="toggleMenu()">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
    </header>
    <br><br><br><br><br>
    <div class="container">
        <div class="breadcrumbs mb-4">
            <a href="index.php" class="text-blue-500">Inicio</a> >
            <a href="index.php" class="text-blue-500">Produtos</a> >
            <span class="text-gray-500">Pesquisa</span>
        </div>

        <main>
            <section class="search-results">
                <h2>Resultados por "<?php echo htmlspecialchars($query); ?>"</h2>
                <div class="card-container">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>"
                                    alt="<?php echo htmlspecialchars($row['name']); ?>">
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                <div class="rating">
                                    ★★★★☆
                                </div>
                                <p class="price">R$ <?php echo number_format($row['price'], 2, ',', '.'); ?></p>
                                <div class="action-buttons">
                                    <a href="cart.php?add=<?php echo htmlspecialchars($row['id']); ?>" class="btn">Adicionar ao
                                        Carrinho</a>
                                    <a href="product.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn">Ver Detalhes</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Nenhum produto encontrado.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>

</html>

<?php
$conn->close();
?>
