<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

header('Content-Type: application/json'); // Defina o tipo de conteúdo como JSON

if (!isset($_POST['cart_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID do carrinho não fornecido']);
    exit();
}

$cart_id = $_POST['cart_id'];

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Verifica se o item pertence ao usuário logado
$sql = "DELETE FROM cart WHERE id = ? AND users_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cart_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao remover o item do carrinho']);
}

$stmt->close();
$conn->close();
?>
