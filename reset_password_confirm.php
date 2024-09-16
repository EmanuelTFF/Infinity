<?php
session_start();
require __DIR__ . '/vendor/autoload.php'; 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $conn->real_escape_string($_GET['token']);
    $result = $conn->query("SELECT * FROM users WHERE token = '$token'");

    if ($result->num_rows > 0) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nova_senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password = '$nova_senha', token = NULL WHERE token = '$token'");
            header("Location: confirm_password_change.php");
            exit();
        }
    } else {
        echo "Token inválido ou expirado!";
    }
} else {
    echo "Nenhum token fornecido!";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #bad1e5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }
        h2 {
            color: #333;
        }
        form input[type="password"] {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        form button {
            padding: 10px 20px;
            background-color: #276a81;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        form button:hover {
            background-color: #218838;
        }
        .password-requirements {
            text-align: left;
            margin-top: 10px;
            color: #555;
        }
        .password-requirements .requirement {
            display: flex;
            align-items: center;
        }
        .password-requirements .requirement i {
            margin-right: 10px;
        }
        .valid {
            color: green;
        }
        .invalid {
            color: red;
        }
        .breadcrumb {
            margin-bottom: 20px;
            text-align: center;
        }
        .breadcrumb a {
            text-decoration: none;
            color: #276a81;
            transition: color 0.3s;
        }
        .breadcrumb a:hover {
            color: #0056b3;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script>
        function validatePassword() {
            const password = document.querySelector('input[name="senha"]').value;
            const lengthRequirement = document.getElementById('length');
            const uppercaseRequirement = document.getElementById('uppercase');
            const lowercaseRequirement = document.getElementById('lowercase');
            const numberRequirement = document.getElementById('number');
            const specialCharRequirement = document.getElementById('special-char');

            lengthRequirement.classList.toggle('valid', password.length >= 8);
            lengthRequirement.classList.toggle('invalid', password.length < 8);

            uppercaseRequirement.classList.toggle('valid', /[A-Z]/.test(password));
            uppercaseRequirement.classList.toggle('invalid', !/[A-Z]/.test(password));

            lowercaseRequirement.classList.toggle('valid', /[a-z]/.test(password));
            lowercaseRequirement.classList.toggle('invalid', !/[a-z]/.test(password));

            numberRequirement.classList.toggle('valid', /[0-9]/.test(password));
            numberRequirement.classList.toggle('invalid', !/[0-9]/.test(password));

            specialCharRequirement.classList.toggle('valid', /[!@#$%^&*(),.?":{}|<>]/.test(password));
            specialCharRequirement.classList.toggle('invalid', !/[!@#$%^&*(),.?":{}|<>]/.test(password));
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="senha"]').addEventListener('input', validatePassword);
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="perfil.php">Voltar para o perfil</a> > Redefinir Senha
        </div>
        <h2>Redefinir sua senha</h2>
        <form method="POST">
            <input type="password" name="senha" placeholder="Digite sua nova senha" required>
            <button type="submit">Redefinir senha</button>
            <div class="password-requirements">
                <div class="requirement" id="length">
                    <i class="fa">&#10004;</i> Pelo menos 8 caracteres
                </div>
                <div class="requirement" id="uppercase">
                    <i class="fa">&#10004;</i> Uma letra maiúscula
                </div>
                <div class="requirement" id="lowercase">
                    <i class="fa">&#10004;</i> Uma letra minúscula
                </div>
                <div class="requirement" id="number">
                    <i class="fa">&#10004;</i> Um número
                </div>
                <div class="requirement" id="special-char">
                    <i class="fa">&#10004;</i> Um caractere especial
                </div>
            </div>
        </form>
    </div>
</body>
</html>
