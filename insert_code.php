<?php
session_start();
require 'vendor/autoload.php';
use Dotenv\Dotenv;

// Carregar as variáveis de ambiente do arquivo .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configurar a conexão com o banco de dados
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

// Criar a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $access_code = $_POST['access_code'];

    $stmt = $conn->prepare("SELECT access_code FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($stored_code);
    $stmt->fetch();

    if ($stored_code === $access_code) {
        $stmt->close();

        $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($full_name);
        $stmt->fetch();
        $_SESSION['full_name'] = $full_name;
        $stmt->close();

        $_SESSION['status'] = 'approved';
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Código de acesso inválido.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Acesso</title>
    <link rel="stylesheet" href="css/codigo.css">
</head>
<body>
    <div class="container">
        <h1>Verificação de Acesso</h1>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="insert_code.php" method="POST">
            <label for="access_code">Código de Acesso:</label>
            <input type="text" id="access_code" name="access_code" required>
            <button type="submit">Verificar</button>
        </form>
    </div>
</body>
</html>
