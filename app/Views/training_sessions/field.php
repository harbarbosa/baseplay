<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card field-mode">
    <h1>Modo campo - <?= esc($session['title']) ?></h1>
    <p><?= esc(format_date_br($session['session_date'])) ?>  <?= esc($session['team_name'] ?? '-') ?> / <?= esc($session['category_name'] ?? '-') ?></p>

    <?php foreach ($athletes as $athlete): ?>
        <form method="post" action="<?= base_url('/training-sessions/' . $session['id'] . '/athletes') ?>" style="margin:12px 0; padding:12px; border:1px solid var(--border); border-radius:10px;">
            <?= csrf_field() ?>
            <input type="hidden" name="athlete_id" value="<?= esc($athlete['athlete_id'] ?? $athlete['id']) ?>">
            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:12px;">
                <strong><?= esc(trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? ''))) ?></strong>
                <select name="attendance_status">
                    <?php foreach (['present','late','absent','justified'] as $status): ?>
                        <option value="<?= esc($status) ?>" <?= ($athlete['attendance_status'] ?? 'present') === $status ? 'selected'  : ''  ?>><?= esc(enum_label($status, 'attendance')) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="performance_note" placeholder="Observação" value="<?= esc($athlete['performance_note'] ?? '') ?>">
                <select name="rating">
                    <option value="">Nota</option>
                    <?php foreach (range(1, 10) as $rating): ?>
                        <option value="<?= $rating ?>" <?= (string)($athlete['rating'] ?? '') === (string)$rating ? 'selected'  : ''  ?>><?= $rating ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Salvar</button>
            </div>
        </form>
    <?php endforeach; ?>

    <div style="margin-top:16px;">
        <a href="<?= base_url('/training-sessions/' . $session['id']) ?>" class="button secondary">Voltar</a>
    </div>
</div>
<?= $this->endSection() ?>
