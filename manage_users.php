<?php
session_start();
if (!isset($_SESSION['full_name']) || $_SESSION['admin'] !== 'sim') {
    header("Location: index.php");
    exit();
}

include 'include/include.php';

// Handle user deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM users WHERE id='$id'";
    $conn->query($sql);
}

// Fetch all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
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
        table {
            width: 80%;
            margin-top: 20px;
            border-collapse: collapse;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .actions a {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>Usuários Cadastrados</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Gênero</th>
                <th>Data de Nascimento</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Administrador</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['full_name']; ?></td>
                <td><?php echo $row['cpf']; ?></td>
                <td><?php echo $row['gender']; ?></td>
                <td><?php echo $row['birth_date']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['administrador']; ?></td>
                <td>
                    <a href="edit_user.php?id=<?php echo $row['id']; ?>">Editar</a>
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Tem certeza que deseja deletar este usuário?');">Deletar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
