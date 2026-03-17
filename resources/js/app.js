import './bootstrap';

// Alpine is loaded by Livewire/Breeze. We register the toast store via the document event.
document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        messages: [],
        add(message, type = 'success') {
            const id = Date.now();
            this.messages.push({ id, message, type });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            this.messages = this.messages.filter(m => m.id !== id);
        },
    });
});
