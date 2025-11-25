/**
 * Sites da Fábrica - Editor Feedback System
 * Sistema completo de feedback visual para o editor
 */

class EditorFeedback {
    constructor() {
        this.spinnerOverlay = null;
        this.toastContainer = null;
        this.progressBar = null;
        this.saveIndicator = null;
        this.init();
    }

    init() {
        this.createSpinner();
        this.createToastContainer();
        this.createProgressBar();
        this.createSaveIndicator();
    }

    // ==========================================
    //   SPINNER
    // ==========================================

    createSpinner() {
        const overlay = document.createElement('div');
        overlay.className = 'sf-spinner-overlay';
        overlay.innerHTML = `
            <div class="sf-spinner-container">
                <div style="position: relative;">
                    <div class="sf-spinner-logo">
                        <img src="/uploads/logo_SF_dark.jpeg" alt="Sites da Fábrica">
                    </div>
                    <div class="sf-spinner-ring"></div>
                </div>
                <div class="sf-spinner-dots">
                    <div class="sf-spinner-dot"></div>
                    <div class="sf-spinner-dot"></div>
                    <div class="sf-spinner-dot"></div>
                </div>
                <div class="sf-spinner-text">Carregando...</div>
                <div class="sf-spinner-subtext">Aguarde um momento</div>
            </div>
        `;
        document.body.appendChild(overlay);
        this.spinnerOverlay = overlay;
    }

    showSpinner(message = 'Carregando...', subtext = 'Aguarde um momento') {
        if (this.spinnerOverlay) {
            const textEl = this.spinnerOverlay.querySelector('.sf-spinner-text');
            const subtextEl = this.spinnerOverlay.querySelector('.sf-spinner-subtext');
            
            if (textEl) textEl.textContent = message;
            if (subtextEl) subtextEl.textContent = subtext;
            
            this.spinnerOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    hideSpinner() {
        if (this.spinnerOverlay) {
            this.spinnerOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // ==========================================
    //   TOAST NOTIFICATIONS
    // ==========================================

    createToastContainer() {
        const container = document.createElement('div');
        container.className = 'sf-toast-container';
        document.body.appendChild(container);
        this.toastContainer = container;
    }

    showToast(type, title, message, duration = 4000) {
        const icons = {
            success: `<svg class="sf-toast-icon" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>`,
            error: `<svg class="sf-toast-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>`,
            warning: `<svg class="sf-toast-icon" fill="none" stroke="#f59e0b" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>`,
            info: `<svg class="sf-toast-icon" fill="none" stroke="#4285f4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>`
        };

        const toast = document.createElement('div');
        toast.className = `sf-toast ${type}`;
        toast.innerHTML = `
            ${icons[type] || icons.info}
            <div class="sf-toast-content">
                <div class="sf-toast-title">${title}</div>
                <div class="sf-toast-message">${message}</div>
            </div>
            <button class="sf-toast-close" aria-label="Fechar">×</button>
        `;

        this.toastContainer.appendChild(toast);

        // Close button
        const closeBtn = toast.querySelector('.sf-toast-close');
        closeBtn.addEventListener('click', () => this.removeToast(toast));

        // Auto-remove
        if (duration > 0) {
            setTimeout(() => this.removeToast(toast), duration);
        }

        return toast;
    }

    removeToast(toast) {
        toast.classList.add('removing');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    // Atalhos
    success(title, message, duration) {
        return this.showToast('success', title, message, duration);
    }

    error(title, message, duration) {
        return this.showToast('error', title, message, duration);
    }

    warning(title, message, duration) {
        return this.showToast('warning', title, message, duration);
    }

    info(title, message, duration) {
        return this.showToast('info', title, message, duration);
    }

    // ==========================================
    //   PROGRESS BAR
    // ==========================================

    createProgressBar() {
        const bar = document.createElement('div');
        bar.className = 'sf-progress-bar';
        bar.innerHTML = '<div class="sf-progress-fill"></div>';
        document.body.appendChild(bar);
        this.progressBar = bar;
    }

    showProgress(percent = null) {
        if (!this.progressBar) return;

        this.progressBar.style.display = 'block';
        const fill = this.progressBar.querySelector('.sf-progress-fill');

        if (percent === null) {
            // Modo indeterminado
            this.progressBar.classList.add('indeterminate');
        } else {
            // Modo determinado
            this.progressBar.classList.remove('indeterminate');
            fill.style.width = `${Math.min(100, Math.max(0, percent))}%`;
        }
    }

    hideProgress() {
        if (this.progressBar) {
            this.progressBar.style.display = 'none';
            this.progressBar.classList.remove('indeterminate');
        }
    }

    // ==========================================
    //   SAVE INDICATOR
    // ==========================================

    createSaveIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'sf-save-indicator';
        indicator.innerHTML = `
            <svg class="sf-save-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="sf-save-text">Salvo automaticamente</span>
        `;
        document.body.appendChild(indicator);
        this.saveIndicator = indicator;
    }

    showSaving() {
        if (!this.saveIndicator) return;

        this.saveIndicator.classList.add('saving');
        this.saveIndicator.classList.add('show');

        const icon = this.saveIndicator.querySelector('.sf-save-icon');
        const text = this.saveIndicator.querySelector('.sf-save-text');

        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>`;
        text.textContent = 'Salvando...';
    }

    showSaved() {
        if (!this.saveIndicator) return;

        this.saveIndicator.classList.remove('saving');

        const icon = this.saveIndicator.querySelector('.sf-save-icon');
        const text = this.saveIndicator.querySelector('.sf-save-text');

        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>`;
        text.textContent = 'Salvo automaticamente';

        // Esconder após 3 segundos
        setTimeout(() => {
            this.saveIndicator.classList.remove('show');
        }, 3000);
    }

    showSaveError() {
        if (!this.saveIndicator) return;

        this.saveIndicator.classList.remove('saving');

        const icon = this.saveIndicator.querySelector('.sf-save-icon');
        const text = this.saveIndicator.querySelector('.sf-save-text');

        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>`;
        icon.style.stroke = '#ef4444';
        text.textContent = 'Erro ao salvar';
        text.style.color = '#ef4444';

        this.saveIndicator.classList.add('show');

        // Resetar após 3 segundos
        setTimeout(() => {
            this.saveIndicator.classList.remove('show');
            icon.style.stroke = '';
            text.style.color = '';
        }, 3000);
    }
}

// Instância global
window.editorFeedback = new EditorFeedback();