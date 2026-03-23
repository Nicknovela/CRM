/**
 * CRM — Deals page JS
 * - Dynamic stage loading when vertical changes
 * - Add activity via fetch on deal detail page
 */
document.addEventListener('DOMContentLoaded', () => {

    // ── Dynamic stage loading ──────────────────────────────────
    const verticalSelect = document.getElementById('vertical_select');
    const stageSelect    = document.getElementById('stage_select');
    const commissionField= document.getElementById('commission_field');

    if (verticalSelect && stageSelect) {
        verticalSelect.addEventListener('change', async () => {
            const verticalId = verticalSelect.value;
            const selectedOption = verticalSelect.options[verticalSelect.selectedIndex];
            const trackCommission = selectedOption.dataset.commission === '1';

            // Commission visibility
            if (commissionField) {
                commissionField.style.display = trackCommission ? 'block' : 'none';
            }

            if (!verticalId) {
                stageSelect.innerHTML = '<option value="">Primero elige vertical</option>';
                return;
            }

            stageSelect.innerHTML = '<option value="">Cargando...</option>';
            stageSelect.disabled = true;

            try {
                const stages = await apiFetch(`${APP_URL}/api/stages?vertical_id=${verticalId}`);
                stageSelect.innerHTML = '<option value="">Seleccionar etapa...</option>';
                stages.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    stageSelect.appendChild(opt);
                });
            } catch (e) {
                stageSelect.innerHTML = '<option value="">Error cargando etapas</option>';
            } finally {
                stageSelect.disabled = false;
            }
        });

        // On page load: if editing, show commission field if applicable
        const initOption = verticalSelect.options[verticalSelect.selectedIndex];
        if (initOption && commissionField) {
            commissionField.style.display = initOption.dataset.commission === '1' ? 'block' : 'none';
        }
    }

    // ── Add activity (deal detail page) ───────────────────────
    const activityForm = document.getElementById('activity-form');
    if (activityForm) {
        activityForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const dealId = activityForm.dataset.dealId;
            const type   = activityForm.querySelector('[name="type"]:checked')?.value || 'note';
            const desc   = activityForm.querySelector('[name="description"]').value.trim();

            if (!desc) {
                showToast('Escribe una descripción para la actividad.', 'error');
                return;
            }

            const submitBtn = activityForm.querySelector('[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Guardando...';

            try {
                await apiFetch(`${APP_URL}/api/activities`, {
                    method: 'POST',
                    body: JSON.stringify({ deal_id: parseInt(dealId), type, description: desc }),
                });

                // Add activity to DOM optimistically
                const icons = { note:'📝', call:'📞', email:'📧', meeting:'🤝' };
                const list  = document.getElementById('activity-list');
                const item  = document.createElement('div');
                item.className = 'flex gap-3 text-sm activity-item';
                item.innerHTML = `
                    <span class="text-xl leading-none mt-0.5">${icons[type] || '📌'}</span>
                    <div class="flex-1">
                        <p class="text-gray-800">${desc.replace(/</g,'&lt;')}</p>
                        <p class="text-xs text-gray-400 mt-1">Tú · hace un momento</p>
                    </div>`;
                list.insertBefore(item, list.firstChild);

                activityForm.querySelector('[name="description"]').value = '';
                showToast('Actividad guardada.');
            } catch (err) {
                showToast('Error al guardar la actividad.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Guardar';
            }
        });
    }
});
