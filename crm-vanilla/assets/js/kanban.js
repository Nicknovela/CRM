/**
 * CRM — Kanban drag-and-drop using SortableJS
 */
document.addEventListener('DOMContentLoaded', () => {
    const lists = document.querySelectorAll('.deal-list');

    lists.forEach(list => {
        new Sortable(list, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'opacity-40',
            dragClass: 'shadow-xl',
            onEnd: async (evt) => {
                const dealId  = evt.item.dataset.dealId;
                const stageId = evt.to.dataset.stageId;

                if (!dealId || !stageId) return;

                try {
                    await apiFetch(`${APP_URL}/api/deals/${dealId}/move`, {
                        method: 'POST',
                        body: JSON.stringify({ stage_id: parseInt(stageId) }),
                    });

                    // Update count badges
                    document.querySelectorAll('.kanban-column').forEach(col => {
                        const countEl = col.querySelector('.deal-list ~ div span, .rounded-t-lg span');
                        if (countEl) {
                            const count = col.querySelector('.deal-list').children.length;
                            countEl.textContent = count;
                        }
                    });

                    showToast('Negocio movido correctamente');
                } catch (err) {
                    showToast('Error al mover el negocio', 'error');
                    // Revert: reload page to restore original state
                    setTimeout(() => location.reload(), 1000);
                }
            },
        });
    });

    // Update all count badges on load
    updateCounts();

    function updateCounts() {
        document.querySelectorAll('.kanban-column').forEach(col => {
            const count   = col.querySelector('.deal-list')?.children.length ?? 0;
            const badge   = col.querySelector('.rounded-t-lg span:last-child');
            if (badge) badge.textContent = count;
        });
    }
});
