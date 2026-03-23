<div class="mb-4 flex items-center justify-between">
    <a href="<?= url('/clients') ?>" class="text-sm text-indigo-600 hover:underline">← Volver a clientes</a>
    <?php if (!\Core\Auth::is('viewer')): ?>
    <a href="<?= url('/clients/'.$client['id'].'/edit') ?>"
       class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Editar</a>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="text-center mb-5">
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-3">
                    <span class="text-indigo-700 font-bold text-xl"><?= initials($client['name']) ?></span>
                </div>
                <h2 class="text-lg font-bold text-gray-900"><?= e($client['name']) ?></h2>
                <?php if ($client['company_name']): ?>
                    <p class="text-sm text-gray-500"><?= e($client['company_name']) ?></p>
                <?php endif; ?>
            </div>
            <dl class="space-y-3 text-sm">
                <?php
                $fields = ['email'=>'Email','phone'=>'Teléfono','industry'=>'Industria','website'=>'Sitio web','address'=>'Dirección'];
                foreach ($fields as $key=>$label):
                    if (!empty($client[$key])):
                ?>
                <div>
                    <dt class="text-xs text-gray-500 uppercase tracking-wide"><?= $label ?></dt>
                    <dd class="text-gray-800 mt-0.5 break-words">
                        <?php if ($key==='website'): ?>
                            <a href="<?= e($client[$key]) ?>" target="_blank" class="text-indigo-600 hover:underline"><?= e($client[$key]) ?></a>
                        <?php elseif ($key==='email'): ?>
                            <a href="mailto:<?= e($client[$key]) ?>" class="text-indigo-600 hover:underline"><?= e($client[$key]) ?></a>
                        <?php else: ?>
                            <?= e($client[$key]) ?>
                        <?php endif; ?>
                    </dd>
                </div>
                <?php endif; endforeach; ?>
                <?php if ($client['notes']): ?>
                <div>
                    <dt class="text-xs text-gray-500 uppercase tracking-wide">Notas</dt>
                    <dd class="text-gray-700 mt-0.5 whitespace-pre-wrap"><?= e($client['notes']) ?></dd>
                </div>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-700 text-sm">Negocios (<?= count($deals) ?>)</h3>
                <?php if (!\Core\Auth::is('viewer')): ?>
                <a href="<?= url('/deals/new?client_id='.$client['id']) ?>"
                   class="text-xs px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">+ Nuevo negocio</a>
                <?php endif; ?>
            </div>
            <?php if (empty($deals)): ?>
                <p class="text-center text-gray-400 text-sm py-10">Sin negocios asociados</p>
            <?php else: ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($deals as $d): ?>
                <div class="px-5 py-4 hover:bg-gray-50 flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <a href="<?= url('/deals/'.$d['id']) ?>" class="font-medium text-indigo-600 hover:underline text-sm"><?= e($d['title']) ?></a>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <?= e($d['vertical_name']) ?> · <?= e($d['assigned_name'] ?? 'Sin asignar') ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <?= stage_badge($d['stage_name'], $d['stage_color']) ?>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= format_money((float)$d['amount'], $d['currency']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
