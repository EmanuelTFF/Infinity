<?php

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Carregar as variáveis de ambiente do arquivo .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!function_exists('generateApprovalCode')) {
    function generateApprovalCode() {
        return bin2hex(random_bytes(4)); // Gera um código aleatório de 8 caracteres
    }
}


function sendAdminApprovalEmail($user_email, $approval_code) {
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

        // Configurar charset para UTF-8
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Destinatários
        $mail->setFrom('example@example.com', 'Seu Nome'); // Substitua pelo seu email e nome
        $mail->addAddress('etonisflorzfilho@gmail.com', 'Admin');

        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = 'Solicitação de Administrador';
        $mail->Body = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&display=swap');

                .email-container {
                    font-family: 'Raleway', sans-serif;
                    color: #FFFFFF;
                    background: #0e0b16;
                    background-image: url('https://www.transparenttextures.com/patterns/black-twill.png');
                    padding: 30px;
                    border-radius: 10px;
                    max-width: 600px;
                    margin: 0 auto;
                    text-align: center;
                }

                .email-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    padding: 20px;
                    border-radius: 10px 10px 0 0;
                    font-size: 24px;
                    font-weight: bold;
                    color: #fff;
                }

                .email-body {
                    padding: 30px;
                    background-color: #1a1a2e;
                }

                .email-body p {
                    margin: 15px 0;
                    font-size: 18px;
                }

                .email-footer {
                    padding: 20px;
                    font-size: 14px;
                    color: #999;
                    background-color: #0e0b16;
                    border-radius: 0 0 10px 10px;
                }

                .approval-link, .deny-link {
                    display: inline-block;
                    padding: 15px 25px;
                    margin: 20px 10px;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-size: 18px;
                }

                .approval-link {
                    background-color: #4CAF50;
                }

                .approval-link:hover {
                    background-color: #45a049;
                }

                .deny-link {
                    background-color: #f44336;
                }

                .deny-link:hover {
                    background-color: #d32f2f;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    Solicitação de Administrador
                </div>
                <div class='email-body'>
                    <p>Um usuário solicitou acesso de administrador.</p>
                    <p><strong>Email do usuário:</strong> " . htmlspecialchars($user_email) . "</p>
                    <p>Para aprovar ou negar o pedido, clique em um dos links abaixo:</p>
                    <p>
                        <a class='approval-link' href='http://localhost/infinity/approve_admin.php?action=approve&code=" . urlencode($approval_code) . "&email=" . urlencode($user_email) . "'>Aprovar</a>
                        <a class='deny-link' href='http://localhost/infinty/approve_admin.php?action=deny&code=" . urlencode($approval_code) . "&email=" . urlencode($user_email) . "'>Negar</a>
                    </p>
                    <p>Este link expira em 24 horas.</p>
                </div>
                <div class='email-footer'>
                    &copy; 2024 Infinity-tech. Todos os direitos reservados.
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';

        $mail->send();
        echo '';
    } catch (Exception $e) {
        echo ": {$mail->ErrorInfo}";
    }
}

// Supondo que você está lidando com um formulário POST para solicitar acesso de administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_email = $_POST['email'];
    $approval_code = generateApprovalCode();
    sendAdminApprovalEmail($user_email, $approval_code);
    echo '.';
}
