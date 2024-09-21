<?php
session_start();

// Carregar as variáveis de ambiente do .env
require __DIR__ . '/vendor/autoload.php'; // Certifique-se de que o autoload do Composer está correto
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Criar a conexão com o banco de dados usando as variáveis de ambiente
$conn = new mysqli(
    $_ENV['DB_HOST'], 
    $_ENV['DB_USER'], 
    $_ENV['DB_PASS'], 
    $_ENV['DB_NAME']
);

// Verifique a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifique se o usuário está logado
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Verifique se o usuário é administrador
$isAdmin = isset($_SESSION['status']) && $_SESSION['status'] == 'approved';

$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Usuário";

// Função para buscar todos os usuários (somente para administradores)
function fetchUsers($conn) {
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

// Função para excluir um usuário
function deleteUser($conn, $userId) {
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    return $stmt->execute();
}

// Função para atualizar um usuário
function updateUser($conn, $userId, $fullName, $email, $status) {
    $sql = "UPDATE users SET full_name = ?, email = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $fullName, $email, $status, $userId);
    return $stmt->execute();
}

// Verificar se a ação de exclusão foi solicitada
if ($isAdmin && isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    deleteUser($conn, $userId);
    echo "<script>alert('Usuário excluído com sucesso!'); window.location.href='perfil.php';</script>";
    exit();
}

// Verificar se a ação de atualização foi solicitada
if ($isAdmin && isset($_POST['update_user'])) {
    $userId = $_POST['user_id'];
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $status = $_POST['status'];
    updateUser($conn, $userId, $fullName, $email, $status);
    echo "<script>alert('Usuário atualizado com sucesso!'); window.location.href='perfil.php';</script>";
    exit();
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

        .admin-section th, .admin-section td {
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

            .admin-section th, .admin-section td {
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
        <?php if ($isAdmin) : ?>
        <div class="admin-section">
            <h3>Usuários Cadastrados</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nome Completo</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
                <?php
                $users = fetchUsers($conn);
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                    echo "<form method='POST' action='perfil.php'>";
                    echo "<td><input type='text' name='full_name' value='" . htmlspecialchars($user['full_name']) . "'></td>";
                    echo "<td><input type='email' name='email' value='" . htmlspecialchars($user['email']) . "'></td>";
                    echo "<td><input type='text' name='status' value='" . htmlspecialchars($user['status']) . "'></td>";
                    echo "<td>
                            <input type='hidden' name='user_id' value='" . htmlspecialchars($user['id']) . "'>
                            <button type='submit' name='update_user'>Atualizar</button>
                            <button type='submit' name='delete_user'>Excluir</button>
                          </td>";
                    echo "</form>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
        <?php endif; ?>
    </section>
</main>
</body>
<script src="js/script.js"></script>
</html>
