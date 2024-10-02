<?php
session_start();
require __DIR__ . '/vendor/autoload.php'; 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    // Verificar se o email existe
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        // Gerar token
        $token = bin2hex(random_bytes(50));
        
        // Atualizar token no banco de dados
        $conn->query("UPDATE users SET token = '$token' WHERE email = '$email'");
        
        // Enviar email com o link de redefinição de senha
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->setFrom('no-reply@infinitytech.com', 'Infinity Tech');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Redefinição de Senha';
            $mail->Body = "Clique no link para redefinir sua senha: <a href='http://localhost/infinity/reset_password_confirm.php?token=$token'>Redefinir Senha</a>";
            
            $mail->send();
            $message = 'Link de redefinição de senha enviado!<br>verifique sua caixa de e-mail';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Erro ao enviar o email: {$mail->ErrorInfo}";
            $messageType = 'error';
        }
    } else {
        $message = "Email não encontrado!";
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueceu a Senha</title>
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
        form input[type="email"] {
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
            background-color: #276a81;
        }
        .breadcrumb {
            margin: 10px 0;
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
        /* Estilos do modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 80%;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }
        .modal-content p {
            font-size: 16px;
            color: #333;
        }
        .modal-content .success {
            color: #28a745;
        }
        .modal-content .error {
            color: #dc3545;
        }
        .close-btn {
            background-color: #276a81;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .close-btn:hover {
            background-color: #276a81;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar se há uma mensagem para exibir
            const message = "<?php echo $message; ?>";
            const messageType = "<?php echo $messageType; ?>";

            if (message) {
                const modal = document.getElementById('messageModal');
                const modalContent = document.querySelector('.modal-content p');
                
                modalContent.innerHTML = message;
                modalContent.classList.add(messageType);
                
                modal.style.display = 'flex';
            }

            // Fechar o modal ao clicar no botão de fechar
            document.getElementById('closeModal').addEventListener('click', function() {
                document.getElementById('messageModal').style.display = 'none';
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="perfil.php">Voltar para o perfil</a> > Esqueceu sua senha
        </div>
        <h2>Esqueceu sua senha?</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Digite seu email" required>
            <button type="submit">Enviar link de redefinição</button>
        </form>
    </div>

    <!-- Modal de mensagem -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <p></p>
            <button id="closeModal" class="close-btn">Fechar</button>
        </div>
    </div>
</body>
</html>
