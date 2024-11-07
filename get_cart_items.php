<?php

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$sql = "SELECT * FROM cart";
$result = $conn->query($sql);

$cart_items = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
    }
}

$conn->close();

echo json_encode($cart_items);
?>
