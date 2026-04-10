// Main application JavaScript
import './bootstrap';
import 'bootstrap';
import 'aos/dist/aos.css';
import 'swiper/css';
import 'glightbox/dist/css/glightbox.css';

import AOS from 'aos';
import Swiper from 'swiper';
import GLightbox from 'glightbox';

AOS.init();
// Global utilities
window.showNotification = showNotification;
window.confirmAction = confirmAction;
window.formatDate = formatDate;
window.formatFileSize = formatFileSize;

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize popovers
    initializePopovers();
    
    // Initialize form validations
    initializeFormValidations();
    
    // Initialize auto-dismiss alerts
    initializeAlerts();
    
    // Initialize image preview
    initializeImagePreview();
    
    // Initialize datepickers
    initializeDatepickers();
    
    // Initialize table search
    initializeTableSearch();
    
    // Initialize confirmation dialogs
    initializeConfirmationDialogs();
}

// Initialize Bootstrap tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize Bootstrap popovers
function initializePopovers() {
    const popoverTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="popover"]')
    );
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// Form validation
function initializeFormValidations() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// Auto-dismiss alerts after 5 seconds
function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

// Image preview before upload
function initializeImagePreview() {
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    let preview = input.parentElement.querySelector('.image-preview');
                    
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'image-preview mt-3';
                        input.parentElement.appendChild(preview);
                    }
                    
                    preview.innerHTML = `
                        <img src="${e.target.result}" 
                             class="img-thumbnail" 
                             style="max-width: 200px; max-height: 200px;">
                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i> Supprimer l'aperçu
                        </button>
                    `;
                };
                
                reader.readAsDataURL(file);
            }
        });
    });
}

// Initialize datepickers
function initializeDatepickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // Add calendar icon
        const wrapper = document.createElement('div');
        wrapper.className = 'input-group';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        const icon = document.createElement('span');
        icon.className = 'input-group-text';
        icon.innerHTML = '<i class="fas fa-calendar"></i>';
        wrapper.appendChild(icon);
    });
}

// Table search functionality
function initializeTableSearch() {
    const searchInputs = document.querySelectorAll('.table-search');
    
    searchInputs.forEach(input => {
        const tableId = input.dataset.table;
        const table = document.getElementById(tableId);
        
        if (!table) return;
        
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    });
}

// Confirmation dialogs
function initializeConfirmationDialogs() {
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Êtes-vous sûr ?';
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
}

// Show notification
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    
    const icon = {
        'success': 'fa-check-circle',
        'danger': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    }[type] || 'fa-info-circle';
    
    alertDiv.innerHTML = `
        <i class="fas ${icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}

// Confirm action with custom modal
function confirmAction(message, callback) {
    const modalHtml = `
        <div class="modal fade" id="confirmModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirmer</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('confirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
    
    document.getElementById('confirmActionBtn').addEventListener('click', function() {
        modal.hide();
        if (typeof callback === 'function') {
            callback();
        }
    });
    
    // Clean up modal after hiding
    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Format date
function formatDate(dateString, format = 'short') {
    const date = new Date(dateString);
    
    if (format === 'short') {
        return date.toLocaleDateString('fr-FR');
    } else if (format === 'long') {
        return date.toLocaleDateString('fr-FR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } else if (format === 'datetime') {
        return date.toLocaleDateString('fr-FR') + ' ' + 
               date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    }
    
    return date.toLocaleDateString('fr-FR');
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 o';
    
    const k = 1024;
    const sizes = ['o', 'Ko', 'Mo', 'Go', 'To'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

// Loading state
function setLoadingState(element, loading = true) {
    if (loading) {
        element.classList.add('loading');
        element.setAttribute('disabled', 'disabled');
    } else {
        element.classList.remove('loading');
        element.removeAttribute('disabled');
    }
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copié dans le presse-papiers !', 'success');
    }).catch(err => {
        console.error('Failed to copy:', err);
        showNotification('Erreur lors de la copie', 'danger');
    });
}

// Add copy button to code blocks
document.querySelectorAll('code, pre').forEach(block => {
    if (block.textContent.trim().length > 10) {
        const button = document.createElement('button');
        button.className = 'btn btn-sm btn-outline-secondary copy-btn';
        button.innerHTML = '<i class="fas fa-copy"></i>';
        button.title = 'Copier';
        button.onclick = () => copyToClipboard(block.textContent);
        
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        block.parentNode.insertBefore(wrapper, block);
        wrapper.appendChild(block);
        wrapper.appendChild(button);
    }
});

// Lazy loading for images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img.lazy').forEach(img => {
        imageObserver.observe(img);
    });
}

// Export utilities
export {
    showNotification,
    confirmAction,
    formatDate,
    formatFileSize,
    setLoadingState,
    copyToClipboard
};