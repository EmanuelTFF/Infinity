<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Conexão com o banco de dados
$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];  // Variável da sessão correta
$customer_name = $_POST['customer_name'];
$customer_email = $_POST['customer_email'];
$customer_phone = $_POST['customer_phone'];
$customer_address = $_POST['customer_address'];
$payment_method = $_POST['payment_method'];
$cart_items = json_decode($_POST['cart_items'], true);  // Decodificar os itens do carrinho
$total = 0;

// Calcular o total do pedido
foreach ($cart_items as $item) {
    $total += $item['product_price'] * $item['quantity'];
}

// Iniciar transação
$conn->begin_transaction();

try {
    // Inserir o pedido na tabela "orders"
    $sql_order = "INSERT INTO orders (users_id, customer_name, customer_email, customer_phone, customer_address, payment_method, total, order_date) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("isssssd", $user_id, $customer_name, $customer_email, $customer_phone, $customer_address, $payment_method, $total);
    $stmt_order->execute();

    // Obter o ID do pedido recém-criado
    $order_id = $stmt_order->insert_id;

    // Preparar a consulta para inserir itens na tabela "orders_items"
    $sql_item = "INSERT INTO orders_items (order_id, products_id, quantity) VALUES (?, ?, ?)";
    $stmt_item = $conn->prepare($sql_item);

    foreach ($cart_items as $item) {
        // Consultar o 'products_id' na tabela 'products' usando o nome do produto
        $sql_product = "SELECT id FROM products WHERE name = ?";
        $stmt_product = $conn->prepare($sql_product);
        $stmt_product->bind_param("s", $item['product_name']);
        $stmt_product->execute();
        $stmt_product->bind_result($products_id);
        $stmt_product->fetch();
        $stmt_product->close();

        // Se o 'products_id' foi encontrado, insere o item na tabela 'orders_items'
        if ($products_id) {
            $stmt_item->bind_param("iii", $order_id, $products_id, $item['quantity']);  // Ajuste: removido o 'price'
            $stmt_item->execute();
        } else {
            throw new Exception("Erro: Produto não encontrado no banco de dados.");
        }
    }

    // Limpar o carrinho do usuário
    $sql_clear_cart = "DELETE FROM cart WHERE users_id = ?";
    $stmt_clear_cart = $conn->prepare($sql_clear_cart);
    $stmt_clear_cart->bind_param("i", $user_id);
    $stmt_clear_cart->execute();

    // Commit da transação
    $conn->commit();

    // Redirecionar para a página de "Meus Pedidos"
    header("Location: meus_pedidos.php");
    exit();

} catch (Exception $e) {
    // Rollback da transação em caso de erro
    $conn->rollback();
    echo "Erro ao processar o pedido: " . $e->getMessage();
}

$stmt_order->close();
$stmt_item->close();
$conn->close();
?>
