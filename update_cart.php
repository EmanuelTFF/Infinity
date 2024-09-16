<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $productId = $_POST['id'];
    $quantity = $_POST['quantity'];

    if ($action === 'add') {
        // Lógica para adicionar o produto ao carrinho no banco de dados
        // Verifique se o produto já está no carrinho, se estiver, aumente a quantidade
        $stmt = $conn->prepare("SELECT * FROM cart_itens WHERE product_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $productId, $userId); // Supondo que você tenha $userId para identificar o usuário
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Produto já está no carrinho, atualize a quantidade
            $stmt = $conn->prepare("UPDATE cart_itens SET quantity = quantity + ? WHERE product_id = ? AND user_id = ?");
            $stmt->bind_param("iii", $quantity, $productId, $userId);
            $stmt->execute();
        } else {
            // Produto não está no carrinho, adicione um novo registro
            $stmt = $conn->prepare("INSERT INTO cart_itens (product_id, user_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $productId, $userId, $quantity);
            $stmt->execute();
        }
    } elseif ($action === 'update') {
        // Lógica para atualizar a quantidade de um produto no carrinho
        $stmt = $conn->prepare("UPDATE cart_itens SET quantity = ? WHERE product_id = ? AND user_id = ?");
        $stmt->bind_param("iii", $quantity, $productId, $userId);
        $stmt->execute();
    } elseif ($action === 'remove') {
        // Lógica para remover um produto do carrinho
        $stmt = $conn->prepare("DELETE FROM cart_itens WHERE product_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $productId, $userId);
        $stmt->execute();
    }
}
?>
