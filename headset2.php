<?php
// Conexão com o banco de dados
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Verificar o conteúdo de $_GET

// Verificar se 'product_id' está presente na URL
if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']); // Usar intval para garantir que o ID seja um número inteiro
} else {
    echo "ID do produto não fornecido.";
    exit();
}

// Consulta SQL para pegar os detalhes do produto usando prepared statements
$stmt = $conn->prepare("SELECT name, price, image_url FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Pegar os dados do produto
    $row = $result->fetch_assoc();
    $product_name = $row['name'];
    $product_price = $row['price'];
    $product_image = $row['image_url'];
} else {
    echo "Produto não encontrado.";
    exit();
}

$stmt->close();
$conn->close();
?>



<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <title>Infinity Tech - Produtos</title>
    <style>
        .thumbnail {
            width: 70px;
            height: 70px;
            cursor: pointer;
            border-radius: 10px;
            box-shadow: 0 6px 7px rgba(23, 118, 227, 0.376);
        }

        .main-image {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 6px 7px rgba(23, 118, 227, 0.376);
        }

        .price-container {
            border-radius: 10px;
            padding: 20px;
            background-color: #f5f5f5;
            display: inline-block;
            width: 100%;
            max-width: 400px;
        }

        .buy-button,
        .cart-button {
            background-color: #163844;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }

        .cart-button {
            background-color: #276a81;
        }

        .nav {
            color: #333;
        }

        .breadcrumbs a {
            text-decoration: none;
        }

        .breadcrumbs a:hover {
            text-decoration: underline;
        }

        .product-rating span {
            font-size: 1.5rem;
        }
    </style>
</head>

<body>
    <header>
        <nav class="navigation">
            <a href="#" class="logo">Infi<span>ni</span>ty<span>te</span>ch</a>
            <ul class="nav-menu">
                <div class="nav">
                    <a href="cart.php" onclick="toggleCart()"><i class='bx bx-cart-alt'> Carrinho</i></a>
                </div>
                <li class="nav-item"><a href="index.php">Inicio</a></li>
                <li class="nav-item"><a href="#">Produtos</a></li>
                <li class="nav-item"><a href="#">Ajuda</a></li>
                <li class="nav-item"><a href="login.php">Perfil</a></li>
            </ul>
            <div class="menu" onclick="toggleMenu()">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
    </header>

    <main>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <div class="container mx-auto px-4 py-8">
            <div class="breadcrumbs mb-4">
                <a href="index.php" class="text-blue-500">Inicio</a> >
                <a href="index.php" class="text-blue-500">Produtos</a> >
                <span class="text-gray-500"><?php echo $product_name; ?></span>
            </div>
            <div class="flex flex-col lg:flex-row">
                <div class="flex-1">
                    <img id="mainImage" src="<?php echo $product_image; ?>" alt="Produto Principal"
                        class="mb-4 main-image">
                    <div class="flex space-x-4">
                        <img src="<?php echo $product_image; ?>" alt="Thumbnail 1" class="thumbnail"
                            onclick="changeImage(this)">
                        <!-- Adicione mais thumbnails se necessário -->
                    </div>
                </div>
                <div class="flex-1 lg:ml-8">
                    <h1 class="text-2xl font-bold mb-4"><?php echo $product_name; ?></h1>
                    <div class="mb-4 product-rating">
                        <span class="text-yellow-500"><i class='bx bxs-star'></i> <i class='bx bxs-star'></i> <i
                                class='bx bxs-star'></i> <i class='bx bxs-star'></i> <i
                                class='bx bxs-star-half'></i></span>
                        <span>(30)</span>
                    </div>
                    <div class="price-container">
                        <span class="text-blue-500 font-bold text-xl">R$
                            <?php echo number_format($product_price, 2, ',', '.'); ?></span>
                        <p class="text-sm text-gray-600">À vista no PIX com até 10% OFF</p>
                    </div>

                    <!-- Campo oculto para armazenar o ID do produto -->
                    <input type="hidden" class="product-id" value="<?php echo $product_id; ?>">

                    <div class="mt-4">
                        <button class="buy-button" onclick="redirectToSalePage()">Comprar</button>
                        <button class="cart-button" onclick="addToCart()">Adicionar ao Carrinho</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function changeImage(element) {
            var mainImage = document.getElementById("mainImage");
            mainImage.src = element.src;
        }

        function redirectToSalePage() {
            const productId = document.querySelector('.product-id').value;
            window.location.href = `checkout.php?product_id=${productId}`;
        }

        function addToCart() {
            var productId = document.querySelector('.product-id').value;
            console.log("ID do Produto: " + productId);

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "add_to_cart.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    console.log("Status da Requisição: " + xhr.status);
                    if (xhr.status === 200) {
                        alert("Produto adicionado ao carrinho com sucesso!");
                    } else {
                        alert("Erro ao adicionar o produto ao carrinho.");
                    }
                }
            };
            xhr.send("product_id=" + productId);
        }
    </script>

    <script src="js/script.js"></script>
</body>

</html>