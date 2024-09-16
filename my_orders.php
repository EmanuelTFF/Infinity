<?php
session_start();
require __DIR__ . '/vendor/autoload.php'; 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$sql = "SELECT o.id, o.order_date, oi.products_id, oi.quantity, oi.price
        FROM orders o
        JOIN orders_items oi ON o.id = oi.orders_id
        WHERE o.user_id = $userId";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos</title>
</head>
<body>
    <h2>Meus Pedidos</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID Pedido</th>
                <th>Data</th>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Preço</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['order_date']; ?></td>
                <td><?php echo $row['products_id']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td><?php echo $row['price']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Você ainda não fez nenhum pedido.</p>
    <?php endif; ?>
</body>
</html>
