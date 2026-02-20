<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1><?= esc($session['title']) ?></h1>
    <p><strong>Equipe:</strong> <?= esc($session['team_name'] ?? '-') ?></p>
    <p><strong>Categoria:</strong> <?= esc($session['category_name'] ?? '-') ?></p>
    <p><strong>Data:</strong> <?= esc(format_date_br($session['session_date'])) ?></p>
    <p><strong>Local:</strong> <?= esc($session['location'] ?? '-') ?></p>
    <p><strong>Observações:</strong> <?= esc($session['general_notes'] ?? '-') ?></p>

    <h3 style="margin-top:16px;">Atletas</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Atleta</th>
                <th>Presença</th>
                <th>Nota</th>
                <th>Rating</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($athletes as $athlete): ?>
            <tr>
                <td><?= esc(trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? ''))) ?></td>
                <td><?= esc($athlete['attendance_status'] ?? '-') ?></td>
                <td><?= esc($athlete['performance_note'] ?? '-') ?></td>
                <td><?= esc($athlete['rating'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top:16px;">
        <a href="<?= base_url('/training-sessions/' . $session['id'] . '/field') ?>" class="button">Modo campo</a>
        <a href="<?= base_url('/training-sessions') ?>" class="button secondary">Voltar</a>
    </div>
</div>
<?= $this->endSection() ?>
