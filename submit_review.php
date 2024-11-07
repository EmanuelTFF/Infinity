<?php
session_start();
require 'vendor/autoload.php';

use Dotenv\Dotenv;

// Carregar variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];

// Estabelecer conexão com o banco de dados
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Verificar se os dados do formulário foram enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anonymous = isset($_POST['anonymous']) ? 1 : 0;
    $product_id = $_POST['product_id'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id']; // Supondo que o ID do usuário esteja na sessão.

    // Verifique os dados antes de inserir
    echo "Product ID: $product_id<br>";
    echo "User ID: $user_id<br>";
    echo "Anonymous: $anonymous<br>";
    echo "Comment: $comment<br>";
    
    // Aqui você pode verificar se o product_id realmente existe no banco
    $stmt = $pdo->prepare('SELECT id FROM products WHERE id = :id');
    $stmt->execute([':id' => $product_id]);
    if (!$stmt->fetch()) {
        die('Produto não encontrado no banco de dados.');
    }

    // Inserir avaliação no banco de dados
    $stmt = $pdo->prepare('INSERT INTO product_reviews (products_id, users_id, comment, anonymous) 
                           VALUES (:product_id, :user_id, :comment, :anonymous)');
    $stmt->execute([
        ':product_id' => $product_id,
        ':user_id' => $user_id,
        ':comment' => $comment,
        ':anonymous' => $anonymous
    ]);

    // Redirecionar de volta para a página do produto
    header('Location: product_page.php?id=' . $product_id);
    exit;
}
