<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1><?= esc($event['title']) ?></h1>
            <p style="color:var(--muted);"><?= esc($types[$event['type']] ?? $event['type']) ?> | <?= esc(format_datetime_br($event['start_datetime'])) ?></p>
        </div>
        <div>
            <?php if (has_permission('events.update')): ?>
                <a href="<?= base_url('/events/' . $event['id'] . '/edit') ?>" class="button">Editar evento</a>
            <?php endif; ?>
            <?php if (has_permission('training_sessions.create')): ?>
                <a href="<?= base_url('/training-sessions/create-from-event/' . $event['id']) ?>" class="button secondary">Criar sessão</a>
            <?php endif; ?>
            <?php if ($event['type'] === 'MATCH' && has_permission('matches.create')): ?>
                <a href="<?= base_url('/matches/create-from-event/' . $event['id']) ?>" class="button secondary">Criar partida</a>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top:16px;">
        <h2>Dados do evento</h2>
        <p>Equipe: <?= esc($event['team_name'] ?? '-') ?></p>
        <p>Categoria: <?= esc($event['category_name'] ?? '-') ?></p>
        <p>Status: <strong><?= esc(enum_label($event['status'], 'status')) ?></strong></p>
        <p>Local: <?= esc($event['location'] ?? '-') ?></p>
        <p>Descrição: <?= esc($event['description'] ?? '-') ?></p>
    </div>
</div>
<?= $this->endSection() ?>