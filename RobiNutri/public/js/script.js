document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. REFERENCIAS DE BARRA LATERAL IZQUIERDA (HISTORIAL) ---
    const historySidebar = document.getElementById('history-sidebar');
    const menuToggleIcon = document.querySelector('.menu-toggle-icon');

    // --- 2. REFERENCIAS DE BARRA LATERAL DERECHA (PERFILES) ---
    const profileButton = document.getElementById('open-profiles-sidebar'); 
    const profileSidebar = document.getElementById('profile-sidebar'); 
    const dynamicProfileList = document.getElementById('dynamic-profile-list');

    // --- 3. REFERENCIAS DE CHAT ---
    const sendButton = document.getElementById('send-button');
    const chatInput = document.getElementById('chat-input');
    const chatArea = document.getElementById('chat-area');
    const welcomeMessage = document.getElementById('welcome-message');

    // --- 3. Barra arriba al inicio ---
    const inputBar = document.querySelector(".chat-input-container-fixed");
    inputBar.classList.add("input-up");

    // --- 4. REFERENCIAS PARA "NUEVO CHAT" Y "RECIENTES" ---
    const newChatBtn = document.querySelector('.new-chat');  
    const recentChatsList = document.querySelector('.recents ul');
    const noChatsText = document.getElementById('no-chats-text');
 
    let chatCounter = 1;
    let currentChatId = null;
    let chatMessages = {
    "chat_1": ["Hola", "¿Qué se ofrece?"],
    "chat_2": ["Otro chat", "Mensaje aquí"],
    };

function sendMessage() {
    const messageText = chatInput.value.trim();

    if (messageText !== "") {

        // 1. CERRAR LA BARRA LATERAL 
        if (historySidebar && historySidebar.classList.contains('open')) {
            historySidebar.classList.remove('open');
        }

        // 2. OCULTAR EL MENSAJE DE BIENVENIDA
        if (welcomeMessage) {
            welcomeMessage.style.display = 'none';
        }

        // 3. BAJAR LA BARRA CUANDO SE ENVÍA EL PRIMER MENSAJE
        if (inputBar.classList.contains("input-up")) {
            inputBar.classList.remove("input-up");
        }

        // 4. MOSTRAR EL MENSAJE EN EL ÁREA DE CHAT
        const messageElement = document.createElement('div');
        messageElement.classList.add('chat-message', 'user-message');
        messageElement.innerHTML = `<p>${messageText}</p>`;

        // Guardar el mensaje dentro del chat actual
        if (!chatMessages[currentChatId]) {
            chatMessages[currentChatId] = [];
        }
        chatMessages[currentChatId].push(messageText);


        if (chatArea) {
            chatArea.appendChild(messageElement);
        }

        // 5. Limpiar el input
        chatInput.value = '';

        // 6. Desplazar hacia el último mensaje 
        if (chatArea) {
            chatArea.scrollTop = chatArea.scrollHeight;
        }
    }
}
    // Variable para guardar el ID del perfil actualmente seleccionado
    let selectedProfileId = localStorage.getItem('selectedProfileId'); 

    function loadProfilesToSidebar() {
        if (!dynamicProfileList) return; 

        // Reutiliza el script PHP
        fetch('server/load_profiles.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.perfiles) {
                    dynamicProfileList.innerHTML = ''; 
                    
                    data.perfiles.forEach(perfil => {
                        const perfilElement = document.createElement('p');
                        perfilElement.classList.add('profile-item');
                        perfilElement.dataset.id = perfil.id; // Guardar el ID en el HTML
                        perfilElement.textContent = perfil.nombre + ' ' + perfil.apellido;

                        // Si este perfil es el que estaba seleccionado, marcar su estilo
                        if (String(perfil.id) === selectedProfileId) {
                            perfilElement.classList.add('selected');
                        }
                        
                        dynamicProfileList.appendChild(perfilElement);
                    });
                } else {
                    console.error("Error al cargar perfiles en el chat:", data.message);
                    dynamicProfileList.innerHTML = '<p class="profile-item">Error al cargar.</p>';
                }
            })
            .catch(error => {
                console.error('Error de red al cargar perfiles:', error);
            });
    }

function getFormattedTime() {
    const now = new Date();

    const hours = now.getHours();
    const minutes = now.getMinutes().toString().padStart(2, '0');
    
    const ampm = hours >= 12 ? "PM" : "AM";
    const hour12 = (hours % 12) || 12;

    return `${hour12}:${minutes} ${ampm}`;
}

function startNewChat() {

    // 1. Crear ID del chat y su lista de mensajes
    const chatId = "chat_" + chatCounter;
    currentChatId = chatId;
    chatMessages[chatId] = [];

    // 2. Limpiar chat y mostrar bienvenida
    chatArea.innerHTML = "";
    welcomeMessage.style.display = "block";
    inputBar.classList.add("input-up");

    // 3. Crear nombre visible
    const chatName = "Chat " + chatCounter;

    // 4. Crear el <li> de recientes
    const li = document.createElement("li");
    li.classList.add("recent-chat-item");
    li.setAttribute("data-chat-id", chatId);

    li.innerHTML = `
        <div class="chat-info">
            <span class="chat-name">${chatName}</span>
            <span class="chat-time">${getFormattedTime()}</span>
        </div>
        <i class="fas fa-ellipsis-v options-btn"></i>

        <div class="chat-options-menu">
            <p class="rename-option">Renombrar</p>
            <p class="delete-option">Eliminar</p>
        </div>
    `;

    const optionsBtn = li.querySelector(".options-btn");
    const optionsMenu = li.querySelector(".chat-options-menu");
    const renameOption = li.querySelector(".rename-option");
    const deleteOption = li.querySelector(".delete-option");

    optionsBtn.addEventListener("click", (e) => {
        e.stopPropagation(); 
        optionsMenu.style.display = 
            optionsMenu.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", () => {
        optionsMenu.style.display = "none";
    });

    renameOption.addEventListener("click", (e) => {
        e.stopPropagation();

        const nameSpan = li.querySelector(".chat-name");
        const input = document.createElement("input");
        input.type = "text";
        input.value = nameSpan.textContent;
        input.classList.add("rename-input");
        nameSpan.replaceWith(input);
        input.focus();

        const saveName = () => {
            if (input.value.trim() !== "") {
                const newName = document.createElement("span");
                newName.classList.add("chat-name");
                newName.textContent = input.value.trim();
                input.replaceWith(newName);
            } else {
                const newName = document.createElement("span");
                newName.classList.add("chat-name");
                newName.textContent = nameSpan.textContent;
                input.replaceWith(newName);
            }
        };

        input.addEventListener("blur", saveName);

        input.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                saveName();
            }
        });
    });

    deleteOption.addEventListener("click", () => {
        li.remove();
        delete chatMessages[chatId];

        if (currentChatId === chatId) {
            chatArea.innerHTML = "";
            welcomeMessage.style.display = "block";
            inputBar.classList.add("input-up");
            currentChatId = null;
        }

        if (recentChatsList.children.length === 0) {
            noChatsText.style.display = "block";
        }
    });

    li.addEventListener("click", () => openChat(chatId));

    recentChatsList.appendChild(li);

    highlightActiveChat(chatId);
    noChatsText.style.display = "none";

    chatCounter++;
}

   function openChat(chatId) {

    currentChatId = chatId;

    chatArea.innerHTML = "";

    if (!chatMessages[chatId] || chatMessages[chatId].length === 0) {
        welcomeMessage.style.display = "block";
    } else {
        welcomeMessage.style.display = "none";

        chatMessages[chatId].forEach(msg => {
            const msgElement = document.createElement('div');
            msgElement.classList.add('chat-message', 'user-message');
            msgElement.innerHTML = `<p>${msg}</p>`;
            chatArea.appendChild(msgElement);
        });
    }

    inputBar.classList.add("input-up");

    highlightActiveChat(chatId);

    chatArea.scrollTop = chatArea.scrollHeight;
}

    function highlightActiveChat(chatId) {
        document.querySelectorAll(".recent-chat-item")
            .forEach(item => item.classList.remove("active-chat"));

        const active = document.querySelector(`.recent-chat-item[data-chat-id="${chatId}"]`);

        if (active) {
            active.classList.add("active-chat");
        }
    }

    function checkIfNoChats() {
    if (recentChatsList.children.length === 0) {
        noChatsText.style.display = "block";
    } else {
        noChatsText.style.display = "none";
    }
}

    function toggleHistorySidebar() {
        if (historySidebar) {
            historySidebar.classList.toggle('open');
        }
    }
    
    if (menuToggleIcon) {
        menuToggleIcon.addEventListener('click', toggleHistorySidebar);
    }
    
    if (profileButton && profileSidebar) {
        profileButton.addEventListener('click', function(e) {
            e.preventDefault(); 
            profileSidebar.classList.toggle('active'); 
        });

        document.body.addEventListener('click', function(e) {
            if (profileSidebar.classList.contains('active')) {
                const clickedOutsideSidebar = !profileSidebar.contains(e.target);
                const clickedOutsideButton = !profileButton.contains(e.target);

                if (clickedOutsideSidebar && clickedOutsideButton) {
                    profileSidebar.classList.remove('active');
                }
            }
        }, true); 
    }

    if (sendButton) {
        sendButton.addEventListener('click', sendMessage);
    }

    if (chatInput) {
        chatInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault(); 
                sendMessage();
            }
        });
    }
    if (newChatBtn) {
    newChatBtn.addEventListener("click", (e) => {
        e.preventDefault();
        startNewChat();
    });
    loadProfilesToSidebar(); 

    if (dynamicProfileList) {
        dynamicProfileList.addEventListener('click', function(e) {
            if (e.target.classList.contains('profile-item')) {
                document.querySelectorAll('.profile-item').forEach(p => p.classList.remove('selected'));
                e.target.classList.add('selected');
                
                selectedProfileId = e.target.dataset.id;
                localStorage.setItem('selectedProfileId', selectedProfileId);
                
                alert(`Perfil seleccionado: ${e.target.textContent}`);
                
                profileSidebar.classList.remove('active');
            }
        });
    }
}
});