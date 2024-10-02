document.getElementById("profile-img").addEventListener("click", function() {
    document.getElementById("profile-pic-input").click();
});

// Enviar automaticamente o formulário quando o arquivo é selecionado
document.getElementById("profile-pic-input").addEventListener("change", function() {
    document.getElementById("profile-pic-form").submit();
});
