<?php
session_start();
if (!isset($_SESSION['full_name']) || $_SESSION['admin'] !== 'sim') {
    header("Location: index.php");
    exit();
}

$full_name = $_SESSION['full_name'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<link rel="stylesheet" href="css/style.css">
<style>
    body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #cce0ff;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .container {
            text-align: center;
            margin-top: 20px;
        }
        h1 {
            color: #333;
            font-size: 2em;
        }
        a {
            color: #6a0dad;
            text-decoration: none;
            font-size: 1em;
        }
</style>
<head>
    <meta charset="UTF-8">
    <title>Bem-vindo Administrador</title>
    <link rel="stylesheet" href="css/admin_welcome.css">
</head>
<body>
    <h1>Bem-vindo à Infinitytech, <?php echo htmlspecialchars($full_name); ?>!</h1>
    <a href="manage_users.php">Gerenciar Usuários</a>
</body>
</html>
