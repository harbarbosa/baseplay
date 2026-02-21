<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Agenda</h1>
            <p style="color:var(--muted);">Eventos, convocações e presença.</p>
        </div>
        <?php if (has_permission('events.create')): ?>
            <a href="<?= base_url('/events/create') ?>" class="button">Novo evento</a>
        <?php endif; ?>
    </div>

    <div style="margin-top:12px;">
        <a href="<?= base_url('/events?view=list') ?>" class="button secondary">Lista</a>
        <a href="<?= base_url('/events?view=calendar') ?>" class="button secondary">Calendário</a>
    </div>

    <form method="get" action="<?= base_url('/events') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <input type="hidden" name="view" value="<?= esc($viewMode) ?>">
        <select name="team_id" id="team_id" onchange="this.form.submit()">
            <option value="">Equipe</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= esc($team['id']) ?>" <?= ($filters['team_id'] ?? '') == $team['id'] ? 'selected' : '' ?>>
                    <?= esc($team['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="category_id" id="category_id">
            <option value="">Categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= esc($category['id']) ?>"
                    data-team-id="<?= esc($category['team_id'] ?? '') ?>"
                    <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                    <?= esc($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="type">
            <option value="">Tipo</option>
            <?php foreach ($types as $typeKey => $typeLabel): ?>
                <option value="<?= esc($typeKey) ?>" <?= ($filters['type'] ?? '') === $typeKey ? 'selected' : '' ?>>
                    <?= esc($typeLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value="">Status</option>
            <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Agendado</option>
            <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
            <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Concluído</option>
        </select>
        <input type="date" name="from_date" value="<?= esc($filters['from_date'] ?? '') ?>">
        <input type="date" name="to_date" value="<?= esc($filters['to_date'] ?? '') ?>">
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/events') ?>" class="button secondary">Limpar</a>
    </form>

    <?php if ($viewMode === 'calendar'): ?>
        <?php
            $currentMonth = $filters['from_date'] ?? date('Y-m');
            $monthStart = new DateTime($currentMonth . '-01');
            $monthEnd = clone $monthStart;
            $monthEnd->modify('last day of this month');
            $startDay = (int) $monthStart->format('N');
            $daysInMonth = (int) $monthEnd->format('j');
        ?>
        <div style="display:grid; grid-template-columns: repeat(7, 1fr); gap:8px;">
            <?php foreach (['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'] as ): ?>
                <div style="font-weight:600; text-align:center;"><?= esc($dayLabel) ?></div>
            <?php endforeach; ?>
            <?php for ($i = 1; $i < $startDay; $i++): ?>
                <div></div>
            <?php endfor; ?>
            <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                <?php $dateKey = $monthStart->format('Y-m-') . str_pad((string) $day, 2, '0', STR_PAD_LEFT); ?>
                <div style="border:1px solid var(--border); border-radius:10px; padding:8px; min-height:90px;">
                    <div style="font-weight:600; font-size:12px;"><?= $day ?></div>
                    <?php if (!empty($eventsByDate[$dateKey])): ?>
                        <?php foreach ($eventsByDate[$dateKey] as $event): ?>
                            <div class="event-chip event-<?= esc(strtolower($event['type'])) ?>">
                                <a href="<?= base_url('/events/' . $event['id']) ?>">
                                    <?= esc($event['title']) ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <?php if (empty($events)): ?>
        <div class="bp-empty-state" style="margin-top:16px;">
            <strong>Nenhum evento ainda</strong>
            <div>Crie o primeiro evento para iniciar a agenda.</div>
            <?php if (has_permission('events.create')): ?>
                <div style="margin-top:12px;">
                    <a href="<?= base_url('/events/create') ?>" class="bp-btn-primary">Criar evento</a>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Título</th>
                    <th>Equipe</th>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= esc(format_datetime_br($event['start_datetime'])) ?></td>
                    <td><?= esc($event['title']) ?></td>
                    <td><?= esc($event['team_name'] ?? '-') ?></td>
                    <td><?= esc($event['category_name'] ?? '-') ?></td>
                    <td><?= esc($types[$event['type']] ?? $event['type']) ?></td>
                    <td><?= esc(enum_label($event['status'], 'status')) ?></td>
                    <td>
                        <a href="<?= base_url('/events/' . $event['id']) ?>">Detalhes</a>
                        <?php if (has_permission('events.update')): ?>
                            | <a href="<?= base_url('/events/' . $event['id'] . '/edit') ?>">Editar</a>
                        <?php endif; ?>
                        <?php if (has_permission('events.delete')): ?>
                            | <a href="<?= base_url('/events/' . $event['id'] . '/delete') ?>">Excluir</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

        <?php if ($pager): ?>
            <?= $pager->links('events', 'default_full') ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
<script>
(() => {
    const teamSelect = document.getElementById('team_id');
    const categorySelect = document.getElementById('category_id');
    if (!teamSelect || !categorySelect) return;
    const filterCategories = () => {
        const teamId = teamSelect.value;
        Array.from(categorySelect.options).forEach((opt) => {
            if (!opt.value) return;
            const optTeam = opt.getAttribute('data-team-id');
            opt.hidden = !!teamId && !!optTeam && optTeam !== teamId;
        });
        if (categorySelect.selectedOptions[0].hidden) {
            categorySelect.value = '';
        }
    };
    teamSelect.addEventListener('change', filterCategories);
    filterCategories();
})();
</script>
<?= $this->endSection() ?>