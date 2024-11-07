<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_address'])) {
        $conn->query("UPDATE users SET endereco_salvo = 0 WHERE id = $userId");
        echo "Endereço excluído com sucesso!";
    } elseif (isset($_POST['update_address'])) {
        $cep = $_POST['cep'];
        $logradouro = $_POST['logradouro'];
        $bairro = $_POST['bairro'];
        $cidade_id = $_POST['cidade']; // Agora usamos o ID da cidade
        $estado_id = $_POST['estado']; // Agora usamos o ID do estado
        $numero = $_POST['numero'];

        // Atualizar as informações de endereço
        $stmt = $conn->prepare("UPDATE users SET cep = ?, logradouro = ?, bairro = ?, cidade_id = ?, estado_id = ?, numero = ?, endereco_salvo = 1 WHERE id = ?");
        $stmt->bind_param('sssiisi', $cep, $logradouro, $bairro, $cidade_id, $estado_id, $numero, $userId);
        $stmt->execute();
        echo "Endereço atualizado com sucesso!";
    }
}

// Buscar as informações do usuário, cidade e estado
$sql = "SELECT u.*, c.nome AS cidade, e.sigla AS estado 
        FROM users u 
        LEFT JOIN cidade c ON u.cidade_id = c.id 
        LEFT JOIN estado e ON u.estado_id = e.id 
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Endereços</title>
    <style>
        /* CSS para uma página de endereços responsiva e animada */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: #bad1e5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            animation: fadeIn 1.2s ease-in-out;
        }

        h2 {
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
            position: relative;
            color: #333;
        }

        h2::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background-color: #276a81;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 50px;
            animation: slideIn 1.5s ease-in-out;
        }

        form {
            display: grid;
            gap: 15px;
        }

        input {
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s ease;
            animation: fadeInUp 1s ease-in-out;
        }

        input:focus {
            outline: none;
            border-color: #276a81;
            box-shadow: 0 0 5px rgba(0, 191, 166, 0.5);
        }

        button {
            padding: 12px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #276a81;
            color: #fff;
            font-size: 1rem;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        button:hover {
            background-color: #276a81;
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(1px);
        }

        /* Animações */
        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: scale(0.9);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes slideIn {
            0% {
                width: 0;
            }

            100% {
                width: 80px;
            }
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Estilo responsivo */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            h2 {
                font-size: 1.5rem;
            }

            input,
            button {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 1.3rem;
            }

            input,
            button {
                padding: 10px;
                font-size: 0.85rem;
            }
        }

        .back-to-profile {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-profile a {
            display: inline-block;
            padding: 10px 15px;
            background-color: #276a81;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .back-to-profile a:hover {
            background-color: #276a81;
            transform: translateY(-2px);
        }

        .back-to-profile a:active {
            transform: translateY(1px);
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Meu Endereço</h2>
        <form method="POST">
            <input type="text" name="cep" value="<?php echo $user['cep']; ?>" placeholder="CEP">
            <input type="text" name="logradouro" value="<?php echo $user['logradouro']; ?>" placeholder="Logradouro">
            <input type="text" name="bairro" value="<?php echo $user['bairro']; ?>" placeholder="Bairro">
            <input type="text" name="cidade" value="<?php echo $user['cidade']; ?>" placeholder="Cidade">
            <input type="text" name="estado" value="<?php echo $user['estado']; ?>" placeholder="Estado">
            <input type="text" name="numero" value="<?php echo $user['numero']; ?>" placeholder="Número">
            <button type="submit" name="update_address">Atualizar Endereço</button>
            <button type="submit" name="delete_address">Excluir Endereço</button>
        </form>
        <div class="back-to-profile">
            <a href="perfil.php">Voltar para o Perfil</a>
        </div>
    </div>
</body>

</html>
