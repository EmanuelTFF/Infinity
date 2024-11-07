<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verificar se o usuário é administrador
if (!isset($_SESSION['status']) || $_SESSION['status'] != 'approved') {
    echo "Acesso negado. Somente administradores podem acessar esta página.";
    exit();
}

// Verificar se o ID do usuário foi enviado
if (!isset($_POST['user_id'])) {
    echo "ID de usuário não fornecido.";
    exit();
}

$user_id = $_POST['user_id'];

// Carregar os dados do usuário
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT full_name, email FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($full_name, $email);
    $stmt->fetch();
    $stmt->close();
}

// Atualizar os dados do usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name']) && isset($_POST['email'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];

    $sql = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $full_name, $email, $user_id);

    if ($stmt->execute()) {
        echo "Usuário atualizado com sucesso!";
    } else {
        echo "Erro ao atualizar o usuário: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
</head>
<body>
    <h2>Editar Usuário</h2>
    <form method="POST" action="editar_usuario.php">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
        <label>Nome Completo:</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
        <br>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        <br>
        <button type="submit">Salvar Alterações</button>
    </form>
    <a href="perfil.php">Voltar ao Perfil</a>
</body>
</html>
