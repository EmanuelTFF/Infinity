function sendFaq(message) {
    const chatBody = document.querySelector('.chat-body');
    
    // Adiciona a mensagem do usuário
    const userMessage = document.createElement('div');
    userMessage.className = 'user-message';
    userMessage.innerHTML = `<p>${message}</p>`;
    chatBody.appendChild(userMessage);

    // Exibir indicador de digitação
    const typingIndicator = document.createElement('div');
    typingIndicator.className = 'typing-indicator';
    typingIndicator.innerHTML = `<p>Infinity-bot está digitando</p>`;
    chatBody.appendChild(typingIndicator);

    // Rolagem automática para o fim da conversa
    chatBody.scrollTop = chatBody.scrollHeight;

    // Respostas automáticas
    let botResponse;

    // Verificar palavras-chave na mensagem
    if (message.toLowerCase().includes('perfil')) {
        botResponse = 'Você pode alterar seu perfil acessando a seção de "Perfil" no menu principal.';
    } else if (message.toLowerCase().includes('pagamento')) {
        botResponse = 'Aceitamos cartão de crédito, boleto bancário e transferências via Pix.';
    } else if (message.toLowerCase().includes('rastrear') || message.toLowerCase().includes('pedido')) {
        botResponse = 'Para rastrear seu pedido, acesse a seção "Meus Pedidos" no menu de perfil.';
    } else if (message.toLowerCase().includes('cancelar') || message.toLowerCase().includes('compra')) {
        botResponse = 'Sim, você pode cancelar sua compra em até 24 horas após a confirmação do pedido.';
    } else {
        botResponse = 'Estou aqui para ajudar, mas não entendi a sua pergunta. Por favor, tente reformular.';
    }

    // Simular digitação do bot
    setTimeout(() => {
        // Remove o indicador de digitação
        chatBody.removeChild(typingIndicator);

        // Adiciona a resposta do bot
        const botMessage = document.createElement('div');
        botMessage.className = 'bot-message';
        botMessage.innerHTML = `<p>${botResponse}</p>`;
        chatBody.appendChild(botMessage);

        // Rolagem automática para o fim da conversa
        chatBody.scrollTop = chatBody.scrollHeight;
    }, 1500); // 1.5 segundos de "digitação"
}

function sendMessage() {
    const userInput = document.getElementById('userInput').value;
    if (userInput.trim() !== '') {
        sendFaq(userInput);
        document.getElementById('userInput').value = '';
    }
}
