<?php
session_start();
require 'vendor/autoload.php';
use Dotenv\Dotenv;

// Carregar variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Estabelecer conexão com o banco de dados
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checa a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

require_once 'request_admin.php'; // Certifique-se de que esse arquivo existe e está funcionando corretamente

// Função para gerar um código de aprovação aleatório
if (!function_exists('generateApprovalCode')) {
    function generateApprovalCode() {
        return bin2hex(random_bytes(4)); // Gera um código aleatório de 8 caracteres
    }
}

$duplicate_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Captura os dados do formulário
    $first_name = $_POST['firstname'];
    $last_name = $_POST['lastname'];
    $full_name = $first_name . ' ' . $last_name;  // Junta nome e sobrenome
    $cpf = $_POST['cpf'];
    $gender = $_POST['gender'];
    $birth_date = $_POST['birth_date'];
    $phone = $_POST['number'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Criptografa a senha
    $source = isset($_POST['source']) ? $_POST['source'] : 'website';

    // Verifica se o campo 'admin' foi enviado e define o valor (padrão: 'no')
    $admin_request = isset($_POST['admin']) ? $_POST['admin'] : 'no';
    $status = $admin_request === 'yes' ? 'approved' : 'no-adm';
    $access_code = ($admin_request === 'yes') ? generateApprovalCode() : '';

    // Verificação de duplicatas
    $sql_check = "SELECT cpf, phone, email FROM users WHERE cpf = ? OR phone = ? OR email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("sss", $cpf, $phone, $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->bind_result($existing_cpf, $existing_phone, $existing_email);
        $stmt_check->fetch();

        // Define mensagens de erro para duplicatas
        if ($existing_cpf == $cpf) {
            $duplicate_error = "O CPF informado já está em uso.";
        } elseif ($existing_phone == $phone) {
            $duplicate_error = "O telefone informado já está em uso.";
        } elseif ($existing_email == $email) {
            $duplicate_error = "O e-mail informado já está em uso.";
        }
    } else {
        // Insere o novo usuário na tabela users
        $sql = "INSERT INTO users (full_name, cpf, gender, birth_date, phone, email, password, source, administrador, status, access_code) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssss", $full_name, $cpf, $gender, $birth_date, $phone, $email, $password, $source, $admin_request, $status, $access_code);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Se for um pedido de administrador, insere na tabela admin_requests
            if ($admin_request === 'yes') {
                $admin_sql = "INSERT INTO admin_requests (users_id, email, approval_code) VALUES (?, ?, ?)";
                $admin_stmt = $conn->prepare($admin_sql);
                $admin_stmt->bind_param("iss", $user_id, $email, $access_code);
                $admin_stmt->execute();
                $admin_stmt->close();

                // Envia e-mail de aprovação ao administrador
                sendAdminApprovalEmail($email, $access_code);

                // Redireciona para a página de inserção do código
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;
                $_SESSION['status'] = 'approved';

                header("Location: insert_code.php");
                exit();
            } else {
                // Redireciona usuário padrão para a página inicial
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;
                $_SESSION['status'] = 'no-adm';

                header("Location: login.php");
                exit();
            }
        } else {
            echo "Erro: " . $sql . "<br>" . $conn->error;
        }

        $stmt->close();
    }

    $stmt_check->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Registro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #bad1e5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #fff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
            max-width: 1200px;
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 20px;
            animation: slideIn 1s ease-out forwards;
        }

        .form-image {
            flex-basis: 40%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #276a81;
            border-radius: 15px;
            padding: 20px;
            animation: fadeIn 1s ease-in;
        }

        .form-image img {
            width: 80%;
            animation: fadeIn 2s ease-in-out forwards;
        }

        .form {
            flex-basis: 58%;
            padding: 20px;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 2rem;
            color: #333;
            animation: fadeInDown 1s ease-out forwards;
        }

        .login-button {
            margin-left: 15px;
        }

        .login-button button {
            padding: 8px 15px;
            background-color: #bad1e5;
            color: white;
            font-size: 0.9rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.4s ease, transform 0.3s ease;
        }

        .login-button button:hover {
            background-color: #276a81;
        }

        .login-button button a {
            text-decoration: none;
            color: white;
        }

        .input-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .input-box {
            flex-basis: 48%;
            margin-bottom: 15px;
        }

        .input-box label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="date"],
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            transition: background-color 0.3s, box-shadow 0.3s;
            background: #f9f9f9;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="tel"]:focus,
        input[type="date"]:focus {
            background-color: #bad1e5;
            border-color: #276a81;
            box-shadow: 0 0 10px rgba(110, 69, 226, 0.2);
        }

        .gender-inputs,
        .adm-inputs {
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
        }

        .gender-inputs h6,
        .adm-inputs h6 {
            font-size: 1rem;
            color: #333;
            margin-bottom: 10px;
        }

        .gender-group,
        .admin-group {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .gender-input,
        .admin-input {
            display: flex;
            align-items: center;
        }

        .gender-input label,
        .admin-input label {
            margin-left: 8px;
            color: #555;
        }

        button {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #bad1e5;
            color: white;
            font-size: 1.2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.4s ease, transform 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background-color: #276a81;
            transform: scale(1.05);
        }

        button a {
            text-decoration: none;
            color: white;
        }

        .error {
            background-color: #ff4444;
            color: white;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: shake 0.5s;
        }

        /* Animações */
        @keyframes slideIn {
            0% {
                transform: translateY(100%);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeInDown {
            0% {
                opacity: 0;
                transform: translateY(-50px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }

            .form-image {
                flex-basis: 100%;
            }

            .form {
                flex-basis: 100%;
                padding: 10px;
            }

            .form-header {
                flex-direction: column;
                align-items: center;
            }

            .input-box {
                flex-basis: 100%;
            }

            .gender-group,
            .admin-group {
                flex-direction: column;
                gap: 10px;
            }

            button {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-image">
            <img src="img/undraw_shopping_re_3wst.svg" alt="">
        </div>
        <div class="form">
            <?php if (!empty($duplicate_error)) { ?>
                <div class="error"><?php echo $duplicate_error; ?></div>
            <?php } ?>
            <form method="POST" action="register.php">
                <div class="form-header">
                    <div class="title">
                        <h1>Cadastre-se</h1>
                    </div>
                    <div class="login-button">
                        <button><a href="login.php">Entrar</a></button>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-box">
                        <label for="firstname">Primeiro Nome</label>
                        <input id="firstname" type="text" name="firstname" placeholder="Digite seu primeiro nome"
                            required>
                    </div>

                    <div class="input-box">
                        <label for="lastname">Sobrenome</label>
                        <input id="lastname" type="text" name="lastname" placeholder="Digite seu sobrenome" required>
                    </div>
                    <div class="input-box">
                        <label for="email">E-mail</label>
                        <input id="email" type="email" name="email" placeholder="Digite seu e-mail" required>
                    </div>

                    <div class="input-box">
                        <label for="cpf">CPF</label>
                        <input id="cpf" type="text" name="cpf" placeholder="Digite seu CPF" required>
                    </div>

                    <div class="input-box">
                        <label for="number">Celular</label>
                        <input id="number" type="tel" name="number" placeholder="(xx) xxxx-xxxx" required>
                    </div>

                    <div class="input-box">
                        <label for="birth_date">Data de Nascimento</label>
                        <input id="birth_date" type="date" name="birth_date" required>
                    </div>

                    <div class="input-box">
                        <label for="password">Senha</label>
                        <input id="password" type="password" name="password" placeholder="Digite sua senha" required>
                    </div>

                    <div class="input-box">
                        <label for="confirmPassword">Confirme sua Senha</label>
                        <input id="confirmPassword" type="password" name="confirmPassword"
                            placeholder="Digite sua senha novamente" required>
                    </div>
                    <div class="input-box">
                        <label for="source">Fonte</label>
                        <input id="source" type="text" name="source" placeholder="Onde conheceu a gente?" required>
                    </div>

                </div>

                <div class="gender-inputs">
                    <div class="gender-title">
                        <h6>Gênero</h6>
                    </div>

                    <div class="gender-group">
                        <div class="gender-input">
                            <input id="female" type="radio" name="gender" value="F">
                            <label for="female">Feminino</label>
                        </div>

                        <div class="gender-input">
                            <input id="male" type="radio" name="gender" value="M">
                            <label for="male">Masculino</label>
                        </div>

                        <div class="gender-input">
                            <input id="others" type="radio" name="gender" value="O">
                            <label for="others">Outros</label>
                        </div>

                        <div class="gender-input">
                            <input id="none" type="radio" name="gender" value="N">
                            <label for="none">Prefiro não dizer</label>
                        </div>
                    </div>
                </div>
                <br>
                <br>
                <br>

                <div class="adm-inputs">
                    <div class="adm-title">
                        <h6>Conta como administrador?</h6>
                    </div>
                    <div class="admin-group">
                        <div class="admin-input">
                        <input id="admin_yes" type="radio" name="admin" value="yes">
                            <label for="admin_yes">Sim</label>
                        </div>
                        <div class="admin-input">
                        <input id="admin_no" type="radio" name="admin" value="no">
                            <label for="admin_no">Não</label>
                        </div>
                    </div>
                </div>

                <div class="continue-button">
                    <button type="submit">Continuar</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>