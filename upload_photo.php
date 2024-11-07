<?php
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Verifique se o usuário está logado
if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    echo "Sessão não configurada corretamente. Redirecionando para login.";
    header("Location: login.php");
    exit();
} else {
    echo "Sessão ativa: Email: " . $_SESSION['email'] . " | User ID: " . $_SESSION['user_id'];
}


// Receber `users_id` da sessão
$users_id = $_SESSION['users_id'];

// Verificar se o formulário foi enviado e se um arquivo foi carregado
if (isset($_FILES['profile-pic']) && $_FILES['profile-pic']['error'] === UPLOAD_ERR_OK) {
    // Obter informações do arquivo
    $fileTmpPath = $_FILES['profile-pic']['tmp_name'];
    $fileName = $_FILES['profile-pic']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Definir um nome único para a imagem usando `users_id`
    $newFileName = $users_id . '_profile.' . $fileExtension;

    // Definir o caminho para armazenar a foto dentro da pasta 'images'
    $uploadFileDir = __DIR__ . '/images/'; // Pasta 'images' dentro do diretório atual
    $dest_path = $uploadFileDir . $newFileName;

    // Verificar se o diretório 'images' existe, se não, criar
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0777, true);
    }

    // Mover o arquivo para o diretório correto
    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        // Armazenar o nome do arquivo na sessão para ser usado depois
        $_SESSION['profile_pic'] = $newFileName;

        echo "Foto de perfil carregada com sucesso!";
        // Redirecionar para a página de perfil ou para exibir a foto
        header("Location: perfil.php");
        exit();
    } else {
        echo "Erro ao mover o arquivo para o diretório desejado.";
    }
} else {
    echo "Nenhuma foto foi carregada ou houve um erro no upload.";
}

// Exibir a foto se estiver presente na sessão
if (isset($_SESSION['profile_pic'])) {
    echo "<img src='images/" . $_SESSION['profile_pic'] . "' alt='Foto de perfil' style='width:150px;height:150px;border-radius:50%;'>";
} else {
    echo "<p>Nenhuma foto de perfil carregada.</p>";
}

?>
