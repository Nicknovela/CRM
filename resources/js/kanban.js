import Sortable from 'sortablejs';

function initKanban() {
    document.querySelectorAll('[data-kanban-column]').forEach(col => {
        if (col._sortable) {
            col._sortable.destroy();
        }
        col._sortable = Sortable.create(col, {
            group: 'deals',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function (evt) {
                const dealId = parseInt(evt.item.dataset.dealId);
                const stageId = parseInt(evt.to.dataset.stageId);
                if (dealId && stageId) {
                    window.Livewire.dispatch('deal-moved', { dealId, stageId });
                }
            },
        });
    });
}

document.addEventListener('livewire:initialized', () => {
    initKanban();

    window.Livewire.hook('morph.updated', ({ component }) => {
        if (component.name === 'kanban.kanban-board') {
            // Re-init after Livewire DOM update
            setTimeout(initKanban, 50);
        }
    });
});

// Stage reordering (used in VerticalManager)
function initStageSort(selector, callback) {
    const el = document.querySelector(selector);
    if (!el) return;
    if (el._stageSortable) el._stageSortable.destroy();
    el._stageSortable = Sortable.create(el, {
        animation: 150,
        handle: '.stage-drag-handle',
        onEnd: function (evt) {
            const orderedIds = Array.from(evt.from.children).map(c => parseInt(c.dataset.id));
            if (callback) callback(orderedIds);
        },
    });
}

export { initKanban, initStageSort };
