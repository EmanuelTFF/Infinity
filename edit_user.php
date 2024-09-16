<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id']) || $_SESSION['status'] !== 'approved') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssi", $full_name, $email, $status, $id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Erro ao atualizar usuário: " . $conn->error;
    }

    $stmt->close();
} else {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT id, full_name, email, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($id, $full_name, $email, $status);
    $stmt->fetch();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
</head>
<body>
    <h2>Editar Usuário</h2>
    <form method="post" action="edit_user.php">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div>
            <label for="full_name">Nome Completo:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo $full_name; ?>" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
        </div>
        <div>
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="approved" <?php if ($status == 'approved') echo 'selected'; ?>>Aprovado</option>
                <option value="pending" <?php if ($status == 'pending') echo 'selected'; ?>>Pendente</option>
                <option value="rejected" <?php if ($status == 'rejected') echo 'selected'; ?>>Rejeitado</option>
            </select>
        </div>
        <button type="submit">Atualizar</button>
    </form>
</body>
</html>
