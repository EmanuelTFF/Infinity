<?php
session_start();

// Conectar ao banco de dados
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id']; // Certifique-se de que o ID do usuário está armazenado na sessão
$isAdmin = isset($_SESSION['status']) && $_SESSION['status'] == 'approved';
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Usuário";

// Função para buscar a imagem de perfil do usuário no banco de dados
function fetchProfileImage($conn, $userId)
{
    $sql = "SELECT profile_image FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($profileImage);
    $stmt->fetch();
    $stmt->close();

    return $profileImage;
}

// Caminho padrão da imagem de perfil, caso o usuário ainda não tenha uma
$defaultProfileImage = 'uploads/profile_images/avatar.jpg';

// Buscar a imagem de perfil do usuário
$profileImage = fetchProfileImage($conn, $userId);
if (!$profileImage) {
    $profileImage = $defaultProfileImage;
}

// Verifique se o arquivo foi enviado e faça o upload da imagem de perfil
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/'; // Diretório para armazenar as imagens
    $uploadFile = $uploadDir . 'profile_' . $userId . '.png'; // Nome do arquivo de perfil baseado no ID do usuário

    // Verifica se o diretório existe, se não existir, cria o diretório
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Move o arquivo enviado para o diretório de uploads
    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
        // Atualizar o caminho da imagem no banco de dados
        $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $uploadFile, $userId);
        $stmt->execute();
        $stmt->close();

        // Atualizar a variável de sessão
        $_SESSION['profile_image'] = $uploadFile;

        // Atualizar a imagem exibida
        $profileImage = $uploadFile;
    } else {
        echo "Erro ao fazer o upload da imagem.";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <title>Infinity Tech - Perfil</title>
    <style>
        .profile-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            /* Centraliza horizontalmente */
            margin-top: 50px;
            /* Espaçamento superior */
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            /* Espaçamento entre a imagem e o botão */
        }

        button {
            padding: 10px 20px;
            cursor: pointer;
        }

        form {
            margin: 0;
            /* Remove margens extras do formulário */
            padding: 0;
            /* Remove preenchimento extra do formulário */
        }


        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h2 {
            color: #333;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .grid a {
            display: block;
            padding: 20px;
            background-color: #f4f4f4;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s;
        }

        .grid a:hover {
            background-color: #e0e0e0;
        }

        .grid a i {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .admin-section {
            margin-top: 40px;
        }

        .admin-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-section th,
        .admin-section td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .admin-section th {
            background-color: #f4f4f4;
        }

        .admin-section form {
            display: inline;
        }

        .admin-section button {
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .admin-section button:hover {
            background-color: #d32f2f;
        }

        .admin-section input[type="text"],
        .admin-section input[type="email"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .admin-section table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .admin-section th,
            .admin-section td {
                white-space: nowrap;
            }

            .admin-section td form {
                display: block;
            }

            .admin-section td button {
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <header>
        <nav class="navigation">
            <a href="#" class="logo">Infi<span>ni</span>ty<span>te</span>ch</a>
            <div class="nav">
                <a href="cart.php" onclick="toggleCart()"><i class='bx bx-cart-alt'> Carrinho</i></a>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php">Inicio</a></li>
                <li class="nav-item"><a href="#">Produtos</a></li>
                <li class="nav-item"><a href="help.html">Ajuda</a></li>
                <li class="nav-item"><a href="perfil.php">Perfil</a></li>
            </ul>
            <div class="menu" onclick="toggleMenu()">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
    </header>

    <!-- Cart Sidebar -->
    <div id="cartSidebar" class="cart-sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="toggleCart()">&times;</a>
        <h2></h2>
        <div class="cart-items">
            <!-- Itens do carrinho aqui -->
        </div>
    </div>
    <main>
        <br><br><br><br>
        <section class="container">
            <div class="header">
                <h2>Olá, <?php echo htmlspecialchars($full_name); ?></h2>
            </div>

            <div class="profile-section">
                <!-- Exibir a imagem de perfil -->
                <img src="<?php echo htmlspecialchars($profileImage); ?>" class="profile-pic" alt="Foto de Perfil">

                <!-- Formulário para alterar a imagem de perfil -->
                <form method="POST" action="perfil.php" enctype="multipart/form-data">
                    <input type="file" name="profile_image" accept="image/*" onchange="this.form.submit()"
                        style="display: none;" id="fileInput">
                    <button type="button" onclick="document.getElementById('fileInput').click()">Alterar Foto de
                        Perfil</button>
                </form>
            </div>


            <div class="grid">
                <a href="forgot_password.php">
                    <i class='bx bx-key'></i>
                    <p>Trocar senha</p>
                </a>
                <a href="meus_pedidos.php">
                    <i class='bx bx-cart'></i>
                    <p>Meus pedidos</p>
                </a>
                <a href="my_addresses.php">
                    <i class='bx bx-map'></i>
                    <p>Meus Endereços</p>
                </a>
                <a href="logout.php">
                    <i class='bx bx-log-out'></i>
                    <p>Sair</p>
                </a>
            </div>
        </section>
    </main>
</body>
<script src="js/script.js"></script>

</html>