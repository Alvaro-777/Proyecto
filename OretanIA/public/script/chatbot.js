document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const mensajeInput = document.getElementById('mensaje-usuario');
    const enviarBtn = document.getElementById('enviar-mensaje');
    const reiniciarBtn = document.getElementById('reiniciar-chat');

    // Enviar mensaje
    function enviarMensaje() {
        const mensaje = mensajeInput.value.trim();
        if (!mensaje) return;

        agregarMensaje('user', mensaje);
        mensajeInput.value = '';
        mensajeInput.disabled = true;
        enviarBtn.disabled = true;

        fetch('/chatbotia/enviar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `mensaje=${encodeURIComponent(mensaje)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    agregarMensaje('error', data.error);
                    if (data.error.includes('créditos')) {
                        window.location.href = '/planes';
                    }
                } else {
                    agregarMensaje('assistant', data.respuesta);
                    document.querySelector('.chat-info strong').textContent = data.creditos_restantes;
                }
            })
            .catch(error => {
                agregarMensaje('error', 'Error de conexión con el servidor');
            })
            .finally(() => {
                mensajeInput.disabled = false;
                enviarBtn.disabled = false;
                mensajeInput.focus();
            });
    }

    function agregarMensaje(role, contenido) {
        const mensajeDiv = document.createElement('div');
        mensajeDiv.className = `mensaje mensaje-${role}`;

        if (role === 'error') {
            mensajeDiv.innerHTML = `<strong>Error:</strong> ${contenido}`;
        } else {
            const nombre = role === 'user' ? 'Tú' : 'Chatbot-IA';
            const timestamp = new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            mensajeDiv.innerHTML = `
                <div class="mensaje-header">${nombre}<small>(${timestamp})</small></div>
                <div class="mensaje-contenido">${contenido}</div>
            `;
        }

        chatMessages.appendChild(mensajeDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Eventos
    enviarBtn.addEventListener('click', enviarMensaje);
    mensajeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') enviarMensaje();
    });

    // REINICIAR - SOLO ESTE BLOQUE
    reiniciarBtn.addEventListener('click', function() {
        if (confirm('¿Estás seguro de que quieres reiniciar la conversación?')) {
            // Limpiar interfaz
            chatMessages.innerHTML = '';

            // Notificar al servidor que se reinició
            fetch('/chatbotia/reiniciar', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
                .then(() => {
                    // Cargar mensaje de bienvenida
                    return fetch('/chatbotia/inicial', {
                        method: 'GET',
                        headers: {'X-Requested-With': 'XMLHttpRequest'}
                    });
                })
                .then(response => response.json())
                .then(data => {
                    data.mensajes.forEach(mensaje => {
                        agregarMensaje(mensaje.tipo, mensaje.contenido);
                    });
                })
                .catch(error => {
                    agregarMensaje('assistant', '¡Hola! Soy tu asistente virtual. Estoy configurado para responder siempre en español. ¿En qué puedo ayudarte hoy?');
                });
        }
    });
});