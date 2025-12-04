console.log('🎯 chat.js - Sistema completo cargado');

let perfilActivoId = null;
let chatActualParaRenombrar = null;
let chatActualId = null;

async function enviarMensajeIA(mensaje, chatId, perfilId) {
    try {
        console.log("📤 Enviando mensaje:", { mensaje, chatId, perfilId });
        mostrarLoadingIndicator();
        
        const formData = new FormData();
        formData.append('mensaje', mensaje);
        formData.append('chat_id', chatId || '0'); //0 para nuevo chat
        formData.append('perfil_id', perfilId);
        
        const response = await fetch('/robinutri/api_final.php', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        console.log("📄 Respuesta:", text);
        
        ocultarLoadingIndicator();
        
        // Extraer JSON si hay warnings
        let jsonText = text;
        if (text.includes('{') && text.includes('}')) {
            const jsonStart = text.indexOf('{');
            const jsonEnd = text.lastIndexOf('}') + 1;
            jsonText = text.substring(jsonStart, jsonEnd);
        }
        
        const data = JSON.parse(jsonText);
        
        if (data.success) {
            if (data.chat_id && data.chat_id !== chatId) {
                chatActualId = data.chat_id;
                const chatInput = document.getElementById('chat-input');
                if (chatInput) {
                    chatInput.dataset.chatId = data.chat_id;
                }
                console.log("🆕 Nuevo chat creado:", data.chat_id);
            }
            
            return data.bot_response;
        } else {
            throw new Error(data.message);
        }
        
    } catch (error) {
        console.error('💥 Error:', error);
        ocultarLoadingIndicator();
        return "Error: " + error.message;
    }
}

async function cargarHistorialChat(chatId) {
    try {
        console.log("📂 Cargando historial del chat:", chatId);
        
        const response = await fetch(`/robinutri/index.php/chat/loadMessages?chat_id=${chatId}`);
        const data = await response.json();
        
        if (data.success && data.mensajes) {
            const chatArea = document.getElementById('chat-area');
            const welcomeMessage = document.getElementById('welcome-message');
            
            if (chatArea) {
                chatArea.innerHTML = '';
                //Ocultar mensaje de bienvenida si hay historial
                if (welcomeMessage && data.mensajes.length > 0) {
                    welcomeMessage.style.display = 'none';
                }
                
                // Mostrar todos los mensajes del historial
                data.mensajes.forEach(mensaje => {
                    const messageElement = document.createElement('div');
                    messageElement.classList.add('chat-message', 
                        mensaje.tipo === 'user' ? 'user-message' : 'bot-message');
                    messageElement.innerHTML = `<p>${mensaje.mensaje}</p>`;
                    chatArea.appendChild(messageElement);
                });
                
                scrollToBottom();
                
                // Actualizar chat actual
                chatActualId = chatId;
                const chatInput = document.getElementById('chat-input');
                if (chatInput) {
                    chatInput.dataset.chatId = chatId;
                }
            }
        }
    } catch (error) {
        console.error('Error cargando historial:', error);
    }
}
function cargarHistorialDesdeBD(perfilId = null) {
    console.log('📥 Cargando historial para perfil:', perfilId);
    
    if (perfilId) {
        perfilActivoId = perfilId;
    }
    
    let url = '/robinutri/index.php/chat/getChatsUsuario';
    if (perfilActivoId) {
        url += `?perfil_id=${perfilActivoId}`;
        console.log('🎯 URL con filtro de perfil:', url);
    } else {
        console.log('⚠️  URL sin filtro de perfil');
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log('✅ Respuesta del servidor:', {
                success: data.success,
                total_chats: data.total_chats,
                perfil_filtro: data.perfil_filtro,
                chats: data.chats
            });
            
            if (data.chats && data.chats.length > 0) {
                const perfilesEnChats = [...new Set(data.chats.map(chat => chat.perfil_id))];
                console.log('👥 Perfiles en los chats recibidos:', perfilesEnChats);
                
                if (perfilActivoId && perfilesEnChats.length > 1) {
                    console.warn('⚠️  ADVERTENCIA: Se recibieron chats de múltiples perfiles:', perfilesEnChats);
                }
            }
            
            renderizarChatsEnHTML(data);
            actualizarInterfazPerfil();
        })
        .catch(error => {
            console.error('💥 Error cargando historial:', error);
            mostrarErrorEnHistorial();
        });
}

function renderizarChatsEnHTML(data) {
    const lista = document.getElementById('chats-list');
    if (!lista) {
        console.error('❌ No se encontró el elemento chats-list');
        return;
    }
    
    if (data.success && data.chats && data.chats.length > 0) {
        lista.innerHTML = '';
        
        console.log('🎨 Renderizando ' + data.chats.length + ' chats:');
        
        data.chats.forEach((chat, index) => {
            console.log(`   ${index + 1}. "${chat.nombre_chat}" - Perfil: ${chat.perfil_nombre} (ID: ${chat.perfil_id})`);
            
            const chatElement = crearElementoChat(chat);
            lista.appendChild(chatElement);
        });
        
        inicializarBotonesAccion();
        console.log('✅ Chats renderizados correctamente');
        
    } else {
        lista.innerHTML = `
            <div class="no-chats" style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-comments" style="font-size: 3em; margin-bottom: 15px; color: #A390D3;"></i>
                <p style="margin: 0; font-size: 1.1em;">No hay conversaciones para este perfil</p>
                <button onclick="crearNuevoChat()" 
                        style="margin-top: 15px; padding: 10px 20px; 
                               background: #A390D3; color: white; border: none; 
                               border-radius: 8px; cursor: pointer; font-weight: bold;">
                    + Crear primer chat
                </button>
            </div>
        `;
        console.log('ℹ️  No hay chats para mostrar');
    }
}

function crearElementoChat(chat) {
    const template = document.getElementById('chat-template');
    const clone = template.content.cloneNode(true);
    const chatItem = clone.querySelector('.chat-history-item');
    
    //Llenar datos
    chatItem.querySelector('.chat-title').textContent = chat.nombre_chat;
    chatItem.querySelector('.chat-profile').textContent = `👦 ${chat.perfil_nombre}`;
    chatItem.querySelector('.chat-date').textContent = `📅 ${formatearFecha(chat.fecha_ultimo_mensaje)}`;
    
    //Configurar botones
    chatItem.querySelector('.delete-chat-btn').dataset.chatId = chat.id;
    chatItem.querySelector('.rename-chat-btn').dataset.chatId = chat.id;
    
    //Evento para seleccionar chat
    chatItem.addEventListener('click', (e) => {
        if (!e.target.closest('.chat-actions')) {
            seleccionarChat(chat);
        }
    });
    
    return chatItem;
}

function inicializarBotonesAccion() {
    console.log('🔧 Inicializando botones de acción...');
    
    //Botones eliminar
    const deleteButtons = document.querySelectorAll('.delete-chat-btn');
    console.log('🔧 Botones eliminar encontrados:', deleteButtons.length);
    
    deleteButtons.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.stopPropagation();
            const chatId = this.dataset.chatId;
            console.log('🗑️ Click en eliminar, chat_id:', chatId);
            confirmarEliminarChat(chatId);
        });
    });
    
    //Botones renombrar
    const renameButtons = document.querySelectorAll('.rename-chat-btn');
    console.log('🔧 Botones renombrar encontrados:', renameButtons.length);
    
    renameButtons.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.stopPropagation();
            const chatId = this.dataset.chatId;
            console.log('✏️ Click en renombrar, chat_id:', chatId);
            abrirModalRenombrar(chatId);
        });
    });
}

function confirmarEliminarChat(chatId) {
    if (confirm('¿Estás seguro de eliminar este chat? Se perderán todos los mensajes.')) {
        eliminarChat(chatId);
    }
}

async function eliminarChat(chatId) {
    try {
        console.log('🗑️ Eliminando chat:', chatId);
        
        const response = await fetch('/robinutri/index.php/chat/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `chat_id=${chatId}`
        });
        
        console.log('📡 Response status eliminar:', response.status);
        console.log('📡 Response ok eliminar:', response.ok);
        
        const text = await response.text();
        console.log('📄 Respuesta cruda eliminar:', text);
        
        try {
            const data = JSON.parse(text);
            console.log('✅ JSON parseado eliminar:', data);
            
            if (data.success) {
                console.log('✅ Chat eliminado');
                cargarHistorialDesdeBD();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            console.error('❌ Error parseando JSON eliminar:', e);
            alert('Error del servidor al eliminar');
        }
        
    } catch (error) {
        console.error('💥 Error de conexión eliminar:', error);
        alert('Error de conexión al eliminar chat');
    }
}

async function renombrarChat(chatId, nuevoNombre) {
    try {
        console.log('✏️ Renombrando chat:', chatId, 'a:', nuevoNombre);
        
        const response = await fetch('/robinutri/index.php/chat/rename', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `chat_id=${chatId}&nuevo_nombre=${encodeURIComponent(nuevoNombre)}`
        });
        
        console.log('📡 Response status renombrar:', response.status);
        console.log('📡 Response ok renombrar:', response.ok);
        
        const text = await response.text();
        console.log('📄 Respuesta cruda renombrar:', text);
        
        try {
            const data = JSON.parse(text);
            console.log('✅ JSON parseado renombrar:', data);
            
            if (data.success) {
                console.log('✅ Chat renombrado');
                cargarHistorialDesdeBD(); 
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            console.error('❌ Error parseando JSON renombrar:', e);
            alert('Error del servidor al renombrar');
        }
        
    } catch (error) {
        console.error('💥 Error de conexión renombrar:', error);
        alert('Error de conexión al renombrar chat');
    }
}

function abrirModalRenombrar(chatId) {
    chatActualParaRenombrar = chatId;
    const modal = document.getElementById('rename-modal');
    const input = document.getElementById('rename-input');
    
    //Obtener nombre actual del chat
    const chatItem = document.querySelector(`[data-chat-id="${chatId}"]`).closest('.chat-history-item');
    const nombreActual = chatItem.querySelector('.chat-title').textContent;
    
    input.value = nombreActual;
    modal.classList.add('active');
    input.focus();
}

function cerrarModalRenombrar() {
    const modal = document.getElementById('rename-modal');
    modal.classList.remove('active');
    chatActualParaRenombrar = null;
}

function seleccionarChat(chat) {
    console.log('💬 Chat seleccionado:', chat.id, 'del perfil:', chat.perfil_id);
    
    //Redirigir a la página del chat específico
    window.location.href = `/robinutri/index.php/chat/conversacion?chat_id=${chat.id}&perfil_id=${chat.perfil_id}`;
}

function formatearFecha(fechaString) {
    const fecha = new Date(fechaString);
    const ahora = new Date();
    const diffMs = ahora - fecha;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    
    if (diffMins < 1) return 'Ahora mismo';
    if (diffMins < 60) return `Hace ${diffMins} minutos`;
    if (diffHours < 24) return `Hace ${diffHours} horas`;
    
    return fecha.toLocaleDateString('es-ES');
}

function mostrarErrorEnHistorial() {
    const lista = document.getElementById('chats-list');
    if (lista) {
        lista.innerHTML = '<div class="no-chats" style="color: red;">Error cargando conversaciones</div>';
    }
}

function inicializarBotonNuevoChat() {
    const boton = document.getElementById('new-chat-btn');
    if (boton) {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            const perfilId = this.dataset.perfilId;
            if (perfilId) {
                window.location.href = `/robinutri/index.php/chat?perfil_id=${perfilId}&nuevo_chat=true`;
            }
        });
    }
}
function inicializarSistemaChat() {
    console.log('🚀 Iniciando sistema de chat completo...');

    const urlParams = new URLSearchParams(window.location.search);
    const perfilIdUrl = urlParams.get('perfil_id');
    
    if (perfilIdUrl) {
        perfilActivoId = perfilIdUrl;
        console.log('📋 Perfil desde URL:', perfilActivoId);
    } else {
        const firstProfile = document.querySelector('.profile-item[data-profile-id]');
        if (firstProfile) {
            perfilActivoId = firstProfile.dataset.profileId;
            console.log('📋 Usando primer perfil disponible:', perfilActivoId);
            
            const nuevaUrl = new URL(window.location);
            nuevaUrl.searchParams.set('perfil_id', perfilActivoId);
            window.history.replaceState({}, '', nuevaUrl);
        } else {
            console.error('❌ No se encontraron perfiles disponibles');
        }
    }
    
    cargarHistorialDesdeBD();
    inicializarBotonNuevoChat();
    inicializarModalRenombrar();
    inicializarSidebarPerfiles();
    
    console.log('✅ Sistema de chat inicializado. Perfil activo:', perfilActivoId);
}

function inicializarSidebarPerfiles() {
    console.log('🔧 Inicializando sidebar de perfiles...');
    
    const toggleBtn = document.getElementById('toggle-profiles-sidebar');
    const sidebar = document.getElementById('profile-sidebar');
    const closeBtn = document.getElementById('close-profiles-sidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            sidebar.classList.toggle('active');
            console.log('🔄 Sidebar estado:', sidebar.classList.contains('active') ? 'abierto' : 'cerrado');
        });
    }
    
    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            sidebar.classList.remove('active');
        });
    }
    
    if (sidebar && toggleBtn) {
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                e.target !== toggleBtn && 
                !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    }
    
    console.log('✅ Sidebar de perfiles inicializado');
}

function cambiarPerfil(perfilId) {
    console.log('👦 Cambiando al perfil desde HTML:', perfilId);
    
    perfilActivoId = perfilId;
    
    const nuevaUrl = new URL(window.location);
    nuevaUrl.searchParams.set('perfil_id', perfilId);
    window.history.pushState({}, '', nuevaUrl);
    
    cargarHistorialDesdeBD();
    
    const sidebar = document.getElementById('profile-sidebar');
    if (sidebar) {
        sidebar.classList.remove('active');
    }
    
    console.log('✅ Perfil cambiado a:', perfilId);
}

function inicializarModalRenombrar() {
    const modal = document.getElementById('rename-modal');
    const input = document.getElementById('rename-input');
    const cancelBtn = document.getElementById('rename-cancel-btn');
    const saveBtn = document.getElementById('rename-save-btn');
    
    cancelBtn.addEventListener('click', cerrarModalRenombrar);
    
    saveBtn.addEventListener('click', () => {
        const nuevoNombre = input.value.trim();
        if (nuevoNombre && chatActualParaRenombrar) {
            renombrarChat(chatActualParaRenombrar, nuevoNombre);
            cerrarModalRenombrar();
        }
    });
    
    input.addEventListener('keyup', (e) => {
        if (e.key === 'Escape') {
            cerrarModalRenombrar();
        } else if (e.key === 'Enter') {
            const nuevoNombre = input.value.trim();
            if (nuevoNombre && chatActualParaRenombrar) {
                renombrarChat(chatActualParaRenombrar, nuevoNombre);
                cerrarModalRenombrar();
            }
        }
    });
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            cerrarModalRenombrar();
        }
    });
}
function actualizarInterfazPerfil() {
    console.log('🔄 Actualizando interfaz para perfil:', perfilActivoId);
    
    const newChatBtn = document.getElementById('new-chat-btn');
    if (newChatBtn && perfilActivoId) {
        newChatBtn.dataset.perfilId = perfilActivoId;
        console.log('✅ Botón nuevo chat actualizado con perfil:', perfilActivoId);
    }
    
    const profileItems = document.querySelectorAll('.profile-item[data-profile-id]');
    profileItems.forEach(item => {
        const itemPerfilId = item.dataset.profileId;
        if (itemPerfilId == perfilActivoId) {
            item.classList.add('active-profile');
            item.style.background = '#e8f5e8';
            item.style.border = '2px solid #8BC53F';
            console.log('✅ Marcando perfil activo:', itemPerfilId);
        } else {
            item.classList.remove('active-profile');
            item.style.background = '#f0edff';
            item.style.border = '2px solid transparent';
        }
    });
    
    actualizarTituloChat(perfilActivoId);
    
    console.log('👦 Interfaz actualizada. Perfil activo:', perfilActivoId);
}

function actualizarTituloChat(perfilId) {
    const chatHeader = document.querySelector('.chat-header h2');
    if (chatHeader && perfilId) {
        const profileItem = document.querySelector(`.profile-item[data-profile-id="${perfilId}"]`);
        if (profileItem) {
            const nombrePerfil = profileItem.querySelector('strong').textContent;
            chatHeader.innerHTML = `<i class="fas fa-robot" style="color: #A390D3;"></i> Chat con ${nombrePerfil}`;
            console.log('✅ Título actualizado para:', nombrePerfil);
        } else {
            chatHeader.innerHTML = `<i class="fas fa-robot" style="color: #A390D3;"></i> Chat del Perfil`;
        }
    }
}

function crearNuevoChat() {
    if (!perfilActivoId) {
        alert('Por favor, selecciona un perfil primero');
        return;
    }
    
    console.log('🆕 Creando nuevo chat para perfil:', perfilActivoId);
    
    window.location.href = `/robinutri/index.php/chat?perfil_id=${perfilActivoId}&nuevo_chat=true`;
}

document.addEventListener('DOMContentLoaded', inicializarSistemaChat);

window.addEventListener('popstate', function(event) {
    console.log('🔄 Cambio en la URL detectado');
    const urlParams = new URLSearchParams(window.location.search);
    const perfilIdUrl = urlParams.get('perfil_id');
    
    if (perfilIdUrl && perfilIdUrl !== perfilActivoId) {
        perfilActivoId = perfilIdUrl;
        cargarHistorialDesdeBD();
    }
});

function verificarEstadoSistema() {
    console.log('🔍 Estado del sistema:');
    console.log('  - Perfil activo:', perfilActivoId);
    console.log('  - URL actual:', window.location.href);
    console.log('  - Perfiles encontrados:', document.querySelectorAll('.profile-item').length);
    console.log('  - Chats cargados:', document.querySelectorAll('.chat-history-item').length);
    
    const perfilesActivos = document.querySelectorAll('.profile-item.active-profile');
    console.log('  - Perfiles marcados como activos:', perfilesActivos.length);
}

function diagnosticarSeparacionChats() {
    console.log('🔍 DIAGNÓSTICO DE SEPARACIÓN DE CHATS');
    console.log('=====================================');
    
    const perfilesHTML = document.querySelectorAll('.profile-item[data-profile-id]');
    console.log('👥 Perfiles encontrados en HTML:', perfilesHTML.length);
    perfilesHTML.forEach(perfil => {
        console.log(`   - ID: ${perfil.dataset.profileId}, Texto: ${perfil.querySelector('strong').textContent}`);
    });
    
    const chatsRenderizados = document.querySelectorAll('.chat-history-item');
    console.log('💬 Chats renderizados:', chatsRenderizados.length);
    chatsRenderizados.forEach(chat => {
        const perfilNombre = chat.querySelector('.chat-profile').textContent;
        console.log(`   - Perfil: ${perfilNombre}`);
    });
    
    console.log('🎯 Perfil activo actual:', perfilActivoId);
    console.log('=====================================');
}

setTimeout(diagnosticarSeparacionChats, 2000);

async function enviarMensajeIA(mensaje, chatId, perfilId) {
    try {
        console.log("📤 Enviando mensaje a IA:", { mensaje, chatId, perfilId });
        mostrarLoadingIndicator();
        
        const formData = new FormData();
        formData.append('mensaje', mensaje);
        formData.append('chat_id', chatId);
        formData.append('perfil_id', perfilId);
        
        const response = await fetch('/robinutri/api_final.php', {
            method: 'POST',
            body: formData
        });
        
        console.log("📡 Response status:", response.status);
        
        const text = await response.text();
        console.log("📄 Respuesta completa:", text);
        
        ocultarLoadingIndicator();
        
        try {
            const data = JSON.parse(text);
            console.log("✅ JSON parseado:", data);
            
            if (data.success) {
                return data.bot_response;
            } else {
                throw new Error(data.message || 'Error del servidor');
            }
            
        } catch (jsonError) {
            console.error('❌ Error parseando JSON:', jsonError);
            throw new Error('El servidor no devolvió JSON válido');
        }
        
    } catch (error) {
        console.error('💥 Error enviando mensaje:', error);
        ocultarLoadingIndicator();
        return "Lo siento, hubo un error: " + error.message;
    }
}

function mostrarLoadingIndicator() {
    const loadingHTML = `
        <div class="chat-message bot-message" id="loading-message">
            <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    `;
    
    const chatArea = document.getElementById('chat-area');
    if (chatArea) {
        chatArea.innerHTML += loadingHTML;
        scrollToBottom();
    }
}

function ocultarLoadingIndicator() {
    const loadingElement = document.getElementById('loading-message');
    if (loadingElement) {
        loadingElement.remove();
    }
}

function scrollToBottom() {
    const chatArea = document.getElementById('chat-area');
    if (chatArea) {
        chatArea.scrollTop = chatArea.scrollHeight;
    }
}

function obtenerPerfilActivoId() {
    const perfilActivo = document.querySelector('.profile-item.active-profile');
    return perfilActivo ? perfilActivo.dataset.profileId : null;
}