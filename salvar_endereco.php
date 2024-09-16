<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$cep = $_POST['cep'];
$logradouro = $_POST['logradouro'];
$bairro = $_POST['bairro'];
$cidade = $_POST['cidade'];
$estado = $_POST['estado'];
$numero = $_POST['numero'];

// Salvar endereço no banco de dados (adaptar para sua tabela de endereços)
$sql = "UPDATE users SET cep = ?, logradouro = ?, bairro = ?, cidade = ?, estado = ?, numero = ?, endereco_salvo = TRUE WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssi", $cep, $logradouro, $bairro, $cidade, $estado, $numero, $user_id);
$stmt->execute();

// Redirecionar com sucesso
header("Location: cart.php?success=true");
exit();
?>
