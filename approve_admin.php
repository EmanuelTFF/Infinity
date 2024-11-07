<?php
// Mostrar erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Carregar variáveis do .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configurar a conexão com o banco de dados usando variáveis de ambiente
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

// Criar a conexão
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Verificar se os parâmetros necessários estão presentes na URL
if (isset($_GET['code']) && isset($_GET['email']) && isset($_GET['action'])) {
    $code = $_GET['code'];
    $user_email = $_GET['email'];
    $action = $_GET['action'];
    $responseMessage = ''; // Para armazenar a mensagem de resposta ao cliente

    if ($action === 'approve') {
        // Gerar um código de 6 caracteres para o acesso do usuário
        $user_access_code = strtoupper(bin2hex(random_bytes(3))); // 6 caracteres

        // Atualizar o status do usuário no banco de dados
        $stmt = $conn->prepare("UPDATE users SET status = 'approved', access_code = ? WHERE email = ?");
        $stmt->bind_param("ss", $user_access_code, $user_email);

        if ($stmt->execute()) {
            // Enviar email ao usuário com o código de acesso
            $mail = new PHPMailer(true);

            try {
                // Configurações do servidor de email
                $mail->isSMTP();
                $mail->Host = $_ENV['SMTP_HOST'];
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['SMTP_USERNAME'];
                $mail->Password = $_ENV['SMTP_PASSWORD'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $_ENV['SMTP_PORT'];

                // Destinatários
                $mail->setFrom('example@example.com', 'Seu Nome');
                $mail->addAddress($user_email);

                // Conteúdo do email
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8'; // Garantindo a codificação correta
                $mail->Subject = 'Seu Acesso foi Aprovado';
                $mail->Body = "
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 0;
            background-color: #000;
            background-image: url('https://img.freepik.com/fotos-premium/estrelas-e-galaxia-espaco-ceu-noite-universo-preto-fundo-estrelado-de-starfield-brilhante_146539-147.jpg');
            background-size: cover;
            background-attachment: fixed;
            color: #FFFFFF;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }
        .container {
            max-width: 600px;
            padding: 30px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5), 0 4px 10px rgba(255, 255, 255, 0.2);
            text-align: center;
            backdrop-filter: blur(10px);
            animation: fadeIn 2s ease-in-out;
        }
        .header {
            font-size: 32px;
            font-weight: bold;
            color: #FFD700;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8), 0 0 20px rgba(255, 215, 0, 0.6);
            margin-bottom: 20px;
            animation: glow 1.5s infinite alternate;
        }
        .message {
            font-size: 18px;
            line-height: 1.8;
            color: #F0E68C;
            margin-bottom: 20px;
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            background-color: #FFD700;
            color: white;
            padding: 15px;
            border-radius: 10px;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            transition: transform 0.2s;
        }
        .code:hover {
            transform: scale(1.1);
            background-color: #ffdf00;
        }
        .footer {
            font-size: 14px;
            color: #A9A9A9;
            margin-top: 30px;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes glow {
            from { text-shadow: 0 0 10px rgba(255, 255, 255, 0.8), 0 0 20px rgba(255, 215, 0, 0.6); }
            to { text-shadow: 0 0 20px rgba(255, 255, 255, 1), 0 0 30px rgba(255, 215, 0, 0.8); }
        }
        @media (max-width: 600px) {
            .container {
                padding: 15px;
                width: 90%;
            }
            .header {
                font-size: 24px;
            }
            .message {
                font-size: 16px;
            }
            .code {
                font-size: 20px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>Aprovação de Acesso de Administrador 🚀</div>
        <div class='message'>
            <p>Seu pedido de acesso de administrador foi aprovado! 🌟</p>
            <p>Seu código de acesso é:</p>
            <div class='code'>" . htmlspecialchars($user_access_code, ENT_QUOTES, 'UTF-8') . "</div>
            <p>Use este código para completar seu login. 🛸</p>
        </div>
        <div class='footer'>
            &copy; 2024 infinity-tech. Todos os direitos reservados.
        </div>
    </div>
</body>
</html>
                ";

                $mail->send();
                $responseMessage = 'Sua solicitação de administrador foi aprovada. Verifique seu email para o código de acesso.';
            } catch (Exception $e) {
                $responseMessage = "O email de aprovação não pôde ser enviado. Erro: {$mail->ErrorInfo}";
            }
        } else {
            $responseMessage = "Erro ao atualizar o status do usuário.";
        }

        $stmt->close();
    } elseif ($action === 'deny') {
        // Deletar o usuário do banco de dados
        $stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
        $stmt->bind_param("s", $user_email);

        if ($stmt->execute()) {
            // Enviar email ao usuário informando que a solicitação foi negada
            $mail = new PHPMailer(true);

            try {
                // Configurações do servidor de email
                $mail->isSMTP();
                $mail->Host = $_ENV['SMTP_HOST'];
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['SMTP_USERNAME'];
                $mail->Password = $_ENV['SMTP_PASSWORD'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $_ENV['SMTP_PORT'];

                // Destinatários
                $mail->setFrom('example@example.com', 'Seu Nome');
                $mail->addAddress($user_email);

                // Conteúdo do email
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8'; // Garantindo a codificação correta
                $mail->Subject = 'Solicitação de Administrador Negada';
                $mail->Body = "
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        body {
                            font-family: 'Courier New', Courier, monospace;
                            margin: 0;
                            padding: 0;
                            background-color: #000;
                            background-image: url('https://img.freepik.com/fotos-premium/estrelas-e-galaxia-espaco-ceu-noite-universo-preto-fundo-estrelado-de-starfield-brilhante_146539-147.jpg');
                            background-size: cover;
                            background-attachment: fixed;
                            color: #FFFFFF;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            height: 100vh;
                            overflow: hidden;
                        }
                        .container {
                            max-width: 600px;
                            padding: 30px;
                            background: rgba(0, 0, 0, 0.8);
                            border-radius: 15px;
                            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5), 0 4px 10px rgba(255, 255, 255, 0.2);
                            text-align: center;
                            backdrop-filter: blur(10px);
                            animation: fadeIn 2s ease-in-out;
                        }
                        .header {
                            font-size: 32px;
                            font-weight: bold;
                            color: #FFD700;
                            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8), 0 0 20px rgba(255, 215, 0, 0.6);
                            margin-bottom: 20px;
                            animation: glow 1.5s infinite alternate;
                        }
                        .message {
                            font-size: 18px;
                            line-height: 1.8;
                            color: #F0E68C;
                            margin-bottom: 20px;
                        }
                        .footer {
                            font-size: 14px;
                            color: #A9A9A9;
                            margin-top: 30px;
                        }
                        @keyframes fadeIn {
                            from { opacity: 0; }
                            to { opacity: 1; }
                        }
                        @keyframes glow {
                            from { text-shadow: 0 0 10px rgba(255, 255, 255, 0.8), 0 0 20px rgba(255, 215, 0, 0.6); }
                            to { text-shadow: 0 0 20px rgba(255, 255, 255, 1), 0 0 30px rgba(255, 215, 0, 0.8); }
                        }
                        @media (max-width: 600px) {
                            .container {
                                padding: 15px;
                                width: 90%;
                            }
                            .header {
                                font-size: 24px;
                            }
                            .message {
                                font-size: 16px;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>Solicitação de Administrador Negada 🌌</div>
                        <div class='message'>
                            <p>Infelizmente, sua solicitação de acesso de administrador foi negada e seu cadastro foi removido. 😔</p>
                            <p>Se você acha que isso é um erro, entre em contato conosco. 📞</p>
                        </div>
                        <div class='footer'>
                            &copy; 2024 Infinity-tech. Todos os direitos reservados.
                        </div>
                    </div>
                </body>
                </html>
                ";

                $mail->send();
                $responseMessage = 'Sua solicitação foi negada. Um email foi enviado para você com mais detalhes.';
            } catch (Exception $e) {
                $responseMessage = "O email de negação não pôde ser enviado. Erro: {$mail->ErrorInfo}";
            }
        } else {
            $responseMessage = "Erro ao deletar o usuário.";
        }

        $stmt->close();
    } else {
        $responseMessage = "Ação inválida.";
    }

    $conn->close();
} else {
    $responseMessage = "Parâmetros inválidos.";
}
?>
