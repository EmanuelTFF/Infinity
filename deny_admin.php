<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if (isset($_GET['code']) && isset($_GET['email'])) {
    $code = $_GET['code'];
    $user_email = $_GET['email'];

    // Delete the user from the database
    $stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
    $stmt->bind_param("s", $user_email);

    if ($stmt->execute()) {
        echo "Usuário deletado com sucesso.";
    } else {
        echo "Erro ao deletar o usuário.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Parâmetros inválidos.";
}
?>
