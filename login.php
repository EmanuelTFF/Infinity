<?php
session_start();
require_once 'vendor/autoload.php'; // Certifique-se de que o autoload do Composer está incluído
use Dotenv\Dotenv;

// Carregar variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Conectar ao banco de dados usando variáveis de ambiente
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, full_name, password, status, administrador FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $full_name, $hashed_password, $status, $administrador);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['email'] = $email;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['status'] = $status;
            $_SESSION['administrador'] = $administrador;

            if ($administrador === 'yes') {
                header("Location: index.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            echo "Senha inválida.";
        }
    } else {
        echo "Email não encontrado.";
    }

    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.css">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            background-color: #bad1e5;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
    </style>
</head>

<body>
    <div class="stars"></div>
    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <a href="index.php" class="back-arrow">
                    
                </a>
                <img src="https://img.icons8.com/ios-filled/50/FFFFFF/lock.png" alt="Lock Icon">
                <h2>Já tem uma conta?</h2>
            </div>
            <p>Informe os seus dados abaixo para acessá-la.</p>
            <form method="post" action="login.php">
                <div class="form-group">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Senha *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Acessar Conta</button>
                <a href="forgot-password.php" class="forgot-password">Esqueci minha senha</a>
            </form>
        </div>
        <div class="form-container">
            <div class="form-header">
                <img src="https://img.icons8.com/ios-filled/50/FFFFFF/add-user-male.png" alt="New Client Icon">
                <h2>Novo Cliente</h2>
            </div>
            <p>Criar uma conta é fácil! Informe seus dados e uma senha para aproveitar todos os benefícios de ter uma
                conta.</p>
            <a href="register.php" class="register-link">Cadastrar-se</a>
        </div>
    </div>
</body>

</html>