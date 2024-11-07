<?php
session_start();
include 'include/include.php';

if (!isset($_SESSION['user_id']) || $_SESSION['status'] !== 'approved') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel do Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        <?php echo file_get_contents("css/admin.css"); ?>
        .back-arrow {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-arrow a {
            color: #1e90ff;
            text-decoration: none;
            font-size: 1.2em;
            display: flex;
            align-items: center;
        }
        .back-arrow a:hover {
            text-decoration: underline;
        }
        .back-arrow i {
            margin-right: 5px;
        }
        .logout {
            color: #ff6347;
            text-decoration: none;
            font-size: 1em;
        }
        .logout:hover {
            text-decoration: underline;
        }
    </style>    
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
</head>
<body class="min-h-screen bg-gray-100">

    <div class="container mx-auto p-4">
        <div class="back-arrow mb-4">
            <a href="index.php" class="text-blue-500 hover:underline flex items-center"><i class='bx bx-arrow-back'></i> Voltar</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        <header class="mb-6">
            <h1 class="text-2xl font-bold">Bem-vindo(a), <?php echo htmlspecialchars($full_name); ?>!</h1>
            <h2 class="text-xl mt-2">Lista de Usuários</h2>
        </header>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">ID</th>
                        <th class="py-2 px-4 border-b">Primeiro nome</th>
                        <th class="py-2 px-4 border-b">E-mail</th>
                        <th class="py-2 px-4 border-b">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, full_name, email FROM users";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='py-2 px-4 border-b'>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td class='py-2 px-4 border-b'>" . htmlspecialchars($row['full_name']) . "</td>";
                            echo "<td class='py-2 px-4 border-b'>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td class='py-2 px-4 border-b'><a href='edit_user.php?id=" . htmlspecialchars($row['id']) . "' class='text-blue-500 hover:underline'>Alterar</a> | <a href='delete_user.php?id=" . htmlspecialchars($row['id']) . "' class='text-red-500 hover:underline'>Excluir</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='py-2 px-4 border-b text-center'>Nenhum usuário encontrado.</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
