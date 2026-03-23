/**
 * CRM — Main JS
 */

// Auto-dismiss flash messages after 4 seconds
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.flash-msg').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });
});

// Generic fetch helper with CSRF
async function apiFetch(url, options = {}) {
    const defaults = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
    };
    const response = await fetch(url, { ...defaults, ...options, headers: { ...defaults.headers, ...(options.headers || {}) } });
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }
    return response.json();
}

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-600',
        error:   'bg-red-600',
        info:    'bg-blue-600',
    };
    toast.className = `fixed bottom-5 right-5 z-50 px-5 py-3 rounded-lg text-white text-sm font-medium shadow-lg ${colors[type] || colors.success}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 0.4s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

// Close modal on ESC key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id^="modal-"]').forEach(m => m.classList.add('hidden'));
    }
});

// Close modal on backdrop click
document.addEventListener('click', e => {
    if (e.target.matches('[id^="modal-"]')) {
        e.target.classList.add('hidden');
    }
});
