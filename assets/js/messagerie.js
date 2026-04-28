// Messagerie functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeMessaging();
});

function initializeMessaging() {
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const fileInput = document.getElementById('fileInput');
    const attachButton = document.getElementById('attachButton');
    const messagesArea = document.getElementById('messagesArea');
    
    if (!messageForm) return;
    
    // Scroll to bottom on load
    scrollToBottom();
    
    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Handle attach button
    attachButton?.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Handle file selection
    fileInput?.addEventListener('change', function() {
        displayFilePreview(this.files);
    });
    
    // Handle form submission
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    // Send message on Enter (Shift+Enter for new line)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.dispatchEvent(new Event('submit'));
        }
    });
    
    // Auto-refresh messages
    setInterval(refreshMessages, 5000);
}

function sendMessage() {
    const form = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const fileInput = document.getElementById('fileInput');
    const conversationId = document.getElementById('messagesArea').dataset.conversationId;
    
    const formData = new FormData();
    formData.append('contenu', messageInput.value.trim());
    
    // Add files if any
    if (fileInput.files.length > 0) {
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('files[]', fileInput.files[i]);
        }
    }
    
    // Check if message or files exist
    if (!messageInput.value.trim() && fileInput.files.length === 0) {
        return;
    }
    
    // Disable form during submission
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    
    fetch(`/admin/messagerie/send/${conversationId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear form
            messageInput.value = '';
            messageInput.style.height = 'auto';
            fileInput.value = '';
            document.getElementById('filePreview').innerHTML = '';
            
            // Add message to chat
            appendMessage(data.message);
            scrollToBottom();
        } else {
            alert('Erreur lors de l\'envoi du message: ' + (data.error || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de l\'envoi du message');
    })
    .finally(() => {
        submitButton.disabled = false;
    });
}

function appendMessage(messageData) {
    const messagesArea = document.getElementById('messagesArea');
    const currentUserId = window.currentUserId;
    const isSent = messageData.expediteur_id === currentUserId;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isSent ? 'message-sent' : 'message-received'}`;
    messageDiv.dataset.messageId = messageData.id;
    
    let attachmentsHtml = '';
    if (messageData.pieces_jointes && messageData.pieces_jointes.length > 0) {
        attachmentsHtml = '<div class="message-attachments mt-2">';
        messageData.pieces_jointes.forEach(piece => {
            if (piece.type === 'image') {
                attachmentsHtml += `
                    <a href="${piece.url}" target="_blank">
                        <img src="${piece.url}" alt="${piece.nom}" class="attachment-image">
                    </a>
                `;
            } else {
                attachmentsHtml += `
                    <a href="${piece.url}" class="attachment-file" target="_blank">
                        <i class="fas fa-file me-2"></i>${piece.nom}
                    </a>
                `;
            }
        });
        attachmentsHtml += '</div>';
    }
    
    messageDiv.innerHTML = `
        <div class="message-bubble">
            <div class="message-content">
                ${escapeHtml(messageData.contenu).replace(/\n/g, '<br>')}
                ${attachmentsHtml}
            </div>
            <div class="message-meta">
                <small class="text-muted">
                    ${formatDate(messageData.date)}
                    ${isSent ? '<i class="fas fa-check ms-1"></i>' : ''}
                </small>
                ${isSent ? `
                    <div class="message-actions">
                        <button class="btn btn-sm btn-link text-muted" 
                                onclick="editMessage(${messageData.id}, '${escapeHtml(messageData.contenu)}')"
                                title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-link text-danger" 
                                onclick="deleteMessage(${messageData.id})"
                                title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    messagesArea.appendChild(messageDiv);
}

function displayFilePreview(files) {
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const previewItem = document.createElement('div');
        previewItem.className = 'file-preview-item';
        previewItem.innerHTML = `
            <i class="fas fa-file"></i>
            <span>${file.name}</span>
            <span class="text-muted">(${formatFileSize(file.size)})</span>
            <span class="remove-file" onclick="removeFile(${i})">&times;</span>
        `;
        preview.appendChild(previewItem);
    }
}

function removeFile(index) {
    const fileInput = document.getElementById('fileInput');
    const dt = new DataTransfer();
    const files = fileInput.files;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== index) {
            dt.items.add(files[i]);
        }
    }
    
    fileInput.files = dt.files;
    displayFilePreview(fileInput.files);
}

function editMessage(messageId, content) {
    document.getElementById('editMessageId').value = messageId;
    document.getElementById('editMessageContent').value = content;
    
    const modal = new bootstrap.Modal(document.getElementById('editMessageModal'));
    modal.show();
}

function saveEditedMessage() {
    const messageId = document.getElementById('editMessageId').value;
    const content = document.getElementById('editMessageContent').value;
    
    if (!content.trim()) {
        alert('Le message ne peut pas être vide');
        return;
    }
    
    const formData = new FormData();
    formData.append('contenu', content);
    
    fetch(`/admin/messagerie/message/${messageId}/edit`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update message in DOM
            const messageDiv = document.querySelector(`[data-message-id="${messageId}"]`);
            const contentDiv = messageDiv.querySelector('.message-content');
            contentDiv.innerHTML = escapeHtml(data.message.contenu).replace(/\n/g, '<br>');
            
            // Add edited indicator
            const metaDiv = messageDiv.querySelector('.message-meta small');
            if (!metaDiv.querySelector('.fa-edit')) {
                metaDiv.innerHTML += ' <i class="fas fa-edit ms-1" title="Modifié"></i>';
            }
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('editMessageModal')).hide();
        } else {
            alert('Erreur lors de la modification');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la modification');
    });
}

function deleteMessage(messageId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', window.csrfToken);
    
    fetch(`/admin/messagerie/message/${messageId}/delete`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove message from DOM
            const messageDiv = document.querySelector(`[data-message-id="${messageId}"]`);
            messageDiv.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => messageDiv.remove(), 300);
        } else {
            alert('Erreur lors de la suppression');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la suppression');
    });
}

function refreshMessages() {
    const messagesArea = document.getElementById('messagesArea');
    if (!messagesArea) return;
    
    const conversationId = messagesArea.dataset.conversationId;
    const lastMessageId = getLastMessageId();
    
    fetch(`/admin/messagerie/conversation/${conversationId}/messages?last_id=${lastMessageId}`)
        .then(response => response.json())
        .then(messages => {
            messages.forEach(message => {
                appendMessage(message);
            });
            if (messages.length > 0) {
                scrollToBottom();
            }
        })
        .catch(error => console.error('Error refreshing messages:', error));
}

function getLastMessageId() {
    const messages = document.querySelectorAll('.message');
    if (messages.length === 0) return 0;
    
    const lastMessage = messages[messages.length - 1];
    return parseInt(lastMessage.dataset.messageId) || 0;
}

function scrollToBottom() {
    const messagesArea = document.getElementById('messagesArea');
    if (messagesArea) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
}

// Utility functions
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'À l\'instant';
    if (diff < 3600000) return Math.floor(diff / 60000) + ' min';
    if (diff < 86400000) return Math.floor(diff / 3600000) + ' h';
    
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 o';
    const k = 1024;
    const sizes = ['o', 'Ko', 'Mo', 'Go'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Add fade out animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(-20px); }
    }
`;
document.head.appendChild(style);