// =====================================================
// MESSAGERIE.JS - Fichier principal de la messagerie
// =====================================================

// ==================== UTILITAIRES ====================

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function formatDate(dateString) {
    try {
        const date = new Date(dateString.replace(' ', 'T'));
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateString;
    }
}

function formatFileSize(bytes) {
    if (!bytes || bytes === 0) return '0 o';
    const k = 1024;
    const sizes = ['o', 'Ko', 'Mo', 'Go'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return (bytes / Math.pow(k, i)).toFixed(1) + ' ' + sizes[i];
}

function scrollToBottom() {
    const area = document.getElementById('messagesArea');
    if (area) {
        area.scrollTop = area.scrollHeight;
    }
}

function getLastMessageId() {
    const msgs = document.querySelectorAll('[data-message-id]');
    if (!msgs.length) return 0;
    const ids = Array.from(msgs).map(m => parseInt(m.dataset.messageId) || 0);
    return Math.max(...ids);
}

// ==================== PREVIEW FICHIERS ====================

function displayFilePreview(files) {
    const preview = document.getElementById('filePreview');
    if (!preview) return;
    preview.innerHTML = '';

    Array.from(files).forEach((file, i) => {
        const div = document.createElement('div');
        div.className = 'file-preview-item';
        div.innerHTML = `
            <i class="fas fa-file me-1 text-secondary"></i>
            <span class="me-1">${escapeHtml(file.name)}</span>
            <small class="text-muted">(${formatFileSize(file.size)})</small>
            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2"
                    onclick="removeFile(${i})" title="Supprimer">
                <i class="fas fa-times"></i>
            </button>
        `;
        preview.appendChild(div);
    });
}

function removeFile(index) {
    const input = document.getElementById('fileInput');
    if (!input) return;
    const dt = new DataTransfer();
    Array.from(input.files).forEach((f, i) => {
        if (i !== index) dt.items.add(f);
    });
    input.files = dt.files;
    displayFilePreview(input.files);
}

// ==================== CONSTRUCTION HTML MESSAGE ====================

function buildMessageHtml(msg, currentUserId) {
    const isSent = parseInt(msg.expediteur_id) === parseInt(currentUserId);

    // Pièces jointes
    let attachmentsHtml = '';
    if (msg.pieces_jointes && msg.pieces_jointes.length > 0) {
        attachmentsHtml = '<div class="message-attachments mt-2">';
        msg.pieces_jointes.forEach(pj => {
            if (pj.type === 'image') {
                attachmentsHtml += `
                    <div class="mb-1">
                        <a href="${escapeHtml(pj.url)}" target="_blank">
                            <img src="${escapeHtml(pj.url)}"
                                 alt="${escapeHtml(pj.nom)}"
                                 style="max-width:200px;max-height:200px;border-radius:8px;display:block;cursor:pointer;">
                        </a>
                    </div>`;
            } else {
                attachmentsHtml += `
                    <div class="mb-1">
                        <a href="${escapeHtml(pj.url)}" target="_blank" download
                           class="d-inline-flex align-items-center gap-2 p-2 rounded border text-decoration-none"
                           style="background:rgba(255,255,255,0.2);color:inherit;">
                            <i class="fas fa-file"></i>
                            <span>${escapeHtml(pj.nom)}</span>
                            ${pj.taille ? `<small>(${escapeHtml(pj.taille)})</small>` : ''}
                        </a>
                    </div>`;
            }
        });
        attachmentsHtml += '</div>';
    }

    // Nom de l'expéditeur (messages reçus seulement)
    const senderHtml = (!isSent && msg.expediteur_nom)
        ? `<div class="sender-name">${escapeHtml(msg.expediteur_nom)}</div>`
        : '';

    // Icône modifié
    const editedIcon = msg.est_modifie
        ? '<i class="fas fa-pencil-alt ms-1" style="font-size:0.65rem;opacity:0.7;" title="Modifié"></i>'
        : '';

    // Icône statut (lu/envoyé) - seulement pour les messages envoyés
    const statusIcon = isSent
        ? (msg.est_lu
            ? '<i class="fas fa-check-double ms-1" style="opacity:0.8;" title="Lu"></i>'
            : '<i class="fas fa-check ms-1" style="opacity:0.6;" title="Envoyé"></i>')
        : '';

    // Boutons d'action (modifier/supprimer) - seulement pour les messages envoyés
    const actionsHtml = isSent
        ? `<div class="message-actions">
                <button class="btn btn-sm btn-link p-0" style="opacity:0.7;"
                        onclick="openEditModal(${msg.id})" title="Modifier">
                    <i class="fas fa-edit" style="font-size:0.8rem;"></i>
                </button>
                <button class="btn btn-sm btn-link text-danger p-0"
                        onclick="confirmDelete(${msg.id})" title="Supprimer">
                    <i class="fas fa-trash" style="font-size:0.8rem;"></i>
                </button>
           </div>`
        : '';

    return `
        <div class="message ${isSent ? 'message-sent' : 'message-received'}"
             data-message-id="${msg.id}">
            <div class="message-bubble">
                <div class="message-content">
                    ${senderHtml}
                    <div class="message-text">${escapeHtml(msg.contenu).replace(/\n/g, '<br>')}</div>
                    ${attachmentsHtml}
                </div>
                <div class="message-footer">
                    <small class="message-time">
                        ${formatDate(msg.date)}${editedIcon}${statusIcon}
                    </small>
                    ${actionsHtml}
                </div>
            </div>
        </div>
    `;
}

function appendMessage(msg, currentUserId) {
    const area = document.getElementById('messagesArea');
    if (!area) return;

    // Éviter les doublons
    if (document.querySelector(`[data-message-id="${msg.id}"]`)) return;

    area.insertAdjacentHTML('beforeend', buildMessageHtml(msg, currentUserId));
}

// ==================== INITIALISATION ENVOI ====================

function initSendMessage(sendRoute, currentUserId) {
    const form = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const fileInput = document.getElementById('fileInput');
    const attachBtn = document.getElementById('attachButton');
    const sendBtn = document.getElementById('sendButton');

    if (!form || !messageInput || !sendBtn) {
        console.error('Éléments du formulaire manquants');
        return;
    }

    // Auto-resize textarea
    messageInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 150) + 'px';
    });

    // Bouton paperclip
    if (attachBtn && fileInput) {
        attachBtn.addEventListener('click', () => fileInput.click());
    }

    // Preview fichiers
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            displayFilePreview(this.files);
        });
    }

    // Envoi sur Enter (sans Shift)
    messageInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
        }
    });

    // Soumission du formulaire
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const contenu = messageInput.value.trim();
        const hasFiles = fileInput && fileInput.files.length > 0;

        if (!contenu && !hasFiles) return;

        const fd = new FormData();
        fd.append('contenu', contenu);

        if (fileInput && fileInput.files.length > 0) {
            Array.from(fileInput.files).forEach(f => fd.append('files[]', f));
        }

        // Désactiver le bouton pendant l'envoi
        sendBtn.disabled = true;
        const originalHtml = sendBtn.innerHTML;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(sendRoute, {
            method: 'POST',
            body: fd
        })
        .then(r => {
            if (!r.ok) {
                return r.text().then(text => {
                    throw new Error('Erreur HTTP ' + r.status + ': ' + text);
                });
            }
            return r.json();
        })
        .then(data => {
            if (data.success) {
                // Réinitialiser le formulaire
                messageInput.value = '';
                messageInput.style.height = 'auto';
                if (fileInput) fileInput.value = '';
                const preview = document.getElementById('filePreview');
                if (preview) preview.innerHTML = '';

                // Afficher le message
                appendMessage(data.message, currentUserId);
                scrollToBottom();
            } else {
                alert('Erreur : ' + (data.error || 'Erreur inconnue'));
            }
        })
        .catch(err => {
            console.error('Erreur envoi:', err);
            alert('Erreur lors de l\'envoi du message : ' + err.message);
        })
        .finally(() => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalHtml;
        });
    });
}

// ==================== REFRESH AUTOMATIQUE ====================

function initRefresh(getMessagesRoute, currentUserId) {
    // Vérifier que la route est valide
    if (!getMessagesRoute || getMessagesRoute.includes('undefined') || getMessagesRoute.includes('null')) {
        console.warn('Route de refresh invalide:', getMessagesRoute);
        return;
    }

    setInterval(() => {
        const lastId = getLastMessageId();

        fetch(getMessagesRoute + '?last_id=' + lastId, {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(r => {
            if (!r.ok) {
                throw new Error('HTTP ' + r.status);
            }
            return r.json();
        })
        .then(messages => {
            if (Array.isArray(messages) && messages.length > 0) {
                messages.forEach(m => appendMessage(m, currentUserId));
                scrollToBottom();
            }
        })
        .catch(err => {
            // Erreur silencieuse pour ne pas spammer la console
            if (!err.message.includes('403') && !err.message.includes('404')) {
                console.error('Erreur refresh:', err.message);
            }
        });
    }, 5000);
}

// ==================== MODIFICATION MESSAGE ====================

function openEditModal(messageId) {
    const msgDiv = document.querySelector(`[data-message-id="${messageId}"]`);
    if (!msgDiv) {
        console.error('Message introuvable:', messageId);
        return;
    }

    const textDiv = msgDiv.querySelector('.message-text');
    if (!textDiv) {
        console.error('Texte du message introuvable');
        return;
    }

    // Convertir le HTML en texte brut
    const tmp = document.createElement('textarea');
    tmp.innerHTML = textDiv.innerHTML.replace(/<br\s*\/?>/gi, '\n');
    const currentText = tmp.value;

    const editIdInput = document.getElementById('editMessageId');
    const editContent = document.getElementById('editMessageContent');
    const editModal = document.getElementById('editMessageModal');

    if (!editIdInput || !editContent || !editModal) {
        console.error('Éléments du modal d\'édition manquants');
        return;
    }

    editIdInput.value = messageId;
    editContent.value = currentText;

    const modal = new bootstrap.Modal(editModal);
    modal.show();

    // Focus sur le textarea après ouverture
    editModal.addEventListener('shown.bs.modal', function () {
        editContent.focus();
        editContent.setSelectionRange(editContent.value.length, editContent.value.length);
    }, {once: true});
}

function saveEditedMessage() {
    const messageId = document.getElementById('editMessageId').value;
    const contenu = document.getElementById('editMessageContent').value.trim();

    if (!contenu) {
        alert('Le message ne peut pas être vide.');
        return;
    }

    if (!window.MESSAGERIE_EDIT_ROUTE) {
        console.error('MESSAGERIE_EDIT_ROUTE non défini');
        return;
    }

    const saveBtn = document.querySelector('#editMessageModal .btn-primary, #editMessageModal .btn-success, #editMessageModal .btn-warning');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Enregistrement...';
    }

    const fd = new FormData();
    fd.append('contenu', contenu);

    fetch(window.MESSAGERIE_EDIT_ROUTE + messageId + '/edit', {
        method: 'POST',
        body: fd
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            // Mettre à jour le texte du message dans la page
            const msgDiv = document.querySelector(`[data-message-id="${messageId}"]`);
            if (msgDiv) {
                const textDiv = msgDiv.querySelector('.message-text');
                if (textDiv) {
                    textDiv.innerHTML = escapeHtml(data.message.contenu).replace(/\n/g, '<br>');
                }

                // Ajouter/mettre à jour l'icône "modifié"
                const timeEl = msgDiv.querySelector('.message-time');
                if (timeEl && !timeEl.querySelector('.fa-pencil-alt')) {
                    timeEl.insertAdjacentHTML('beforeend',
                        '<i class="fas fa-pencil-alt ms-1" style="font-size:0.65rem;opacity:0.7;" title="Modifié"></i>');
                }
            }

            // Fermer le modal
            const modalEl = document.getElementById('editMessageModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

        } else {
            alert('Erreur : ' + (data.error || 'Erreur lors de la modification'));
        }
    })
    .catch(err => {
        console.error('Erreur modification:', err);
        alert('Erreur lors de la modification : ' + err.message);
    })
    .finally(() => {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Enregistrer';
        }
    });
}

// ==================== SUPPRESSION MESSAGE ====================

function confirmDelete(messageId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) return;

    if (!window.MESSAGERIE_DELETE_ROUTE) {
        console.error('MESSAGERIE_DELETE_ROUTE non défini');
        return;
    }

    if (!window.MESSAGERIE_CSRF_TOKEN) {
        console.error('MESSAGERIE_CSRF_TOKEN non défini');
        return;
    }

    const fd = new FormData();
    fd.append('_token', window.MESSAGERIE_CSRF_TOKEN);

    fetch(window.MESSAGERIE_DELETE_ROUTE + messageId + '/delete', {
        method: 'POST',
        body: fd
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            const msgDiv = document.querySelector(`[data-message-id="${messageId}"]`);
            if (msgDiv) {
                msgDiv.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                msgDiv.style.opacity = '0';
                msgDiv.style.transform = 'scale(0.95)';
                setTimeout(() => msgDiv.remove(), 300);
            }
        } else {
            alert('Erreur : ' + (data.error || 'Erreur lors de la suppression'));
        }
    })
    .catch(err => {
        console.error('Erreur suppression:', err);
        alert('Erreur lors de la suppression : ' + err.message);
    });
}