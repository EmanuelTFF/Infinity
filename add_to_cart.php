<?php
session_start();
require 'vendor/autoload.php';
use Dotenv\Dotenv;

// Carregar as variáveis de ambiente do arquivo .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Conectar ao banco de dados usando as variáveis de ambiente
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

// Recupera os detalhes do produto
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if ($product) {
    // Verifica se o produto já está no carrinho
    $sql = "SELECT * FROM cart WHERE users_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Se o produto já estiver no carrinho, incrementa a quantidade
        $cart_row = $result->fetch_assoc();
        $cart_id = $cart_row['id']; // Captura o cart_id existente
        $sql = "UPDATE cart SET quantity = quantity + 1 WHERE users_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();

        // Atualiza a tabela items_product com o novo quantity
        $sql = "UPDATE items_product SET quantity = quantity + 1 WHERE cart_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cart_id, $product_id);
        $stmt->execute();
    } else {
        // Se o produto não estiver no carrinho, insere um novo registro na tabela 'cart'
        $sql = "INSERT INTO cart (product_id, product_name, product_image, product_price, quantity, users_id) VALUES (?, ?, ?, ?, 1, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdi", $product_id, $product['name'], $product['image_url'], $product['price'], $user_id);
        $stmt->execute();
        
        // Pega o ID do carrinho que acabou de ser inserido
        $cart_id = $conn->insert_id;

        // Insere na tabela items_product
        $sql = "INSERT INTO items_product (cart_id, product_id, quantity, price) VALUES (?, ?, 1, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iid", $cart_id, $product_id, $product['price']);
        $stmt->execute();
    }
}

$stmt->close();
$conn->close();
?>
