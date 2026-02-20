<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:900px;">
    <h1>Nova sessão</h1>
    <form method="post" action="<?= base_url('/training-sessions') ?>">
        <?= csrf_field() ?>
        <?php if (!empty($event)): ?>
            <input type="hidden" name="event_id" value="<?= esc($event['id']) ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Equipe</label>
            <select name="team_id" id="team_id">
                <?php foreach ($teams as $team): ?>
                    <option value="<?= esc($team['id']) ?>" <?= old('team_id', $event['team_id'] ?? '') == $team['id'] ? 'selected'  : ''  ?>>
                        <?= esc($team['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Categoria</label>
            <select name="category_id" id="category_id">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= esc($category['id']) ?>"
                        data-team-id="<?= esc($category['team_id'] ?? '') ?>"
                        <?= old('category_id', $event['category_id'] ?? '') == $category['id'] ? 'selected'  : ''  ?>>
                        <?= esc($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Plano de treino (opcional)</label>
            <select name="training_plan_id">
                <option value="">Selecione</option>
                <?php foreach ($plans as $plan): ?>
                    <option value="<?= esc($plan['id']) ?>" <?= old('training_plan_id') == $plan['id'] ? 'selected'  : ''  ?>>
                        <?= esc($plan['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="title" value="<?= esc(old('title', $event['title'] ?? '')) ?>">
        </div>
        <div class="form-group">
            <label>Data</label>
            <input type="date" name="session_date" value="<?= esc(old('session_date', isset($event['start_datetime']) ? substr($event['start_datetime'],0,10) : '')) ?>">
        </div>
        <div class="form-group">
            <label>Início</label>
            <input type="datetime-local" name="start_datetime" value="<?= esc(old('start_datetime')) ?>">
        </div>
        <div class="form-group">
            <label>Fim</label>
            <input type="datetime-local" name="end_datetime" value="<?= esc(old('end_datetime')) ?>">
        </div>
        <div class="form-group">
            <label>Local</label>
            <input type="text" name="location" value="<?= esc(old('location', $event['location'] ?? '')) ?>">
        </div>
        <div class="form-group">
            <label>Observações gerais</label>
            <textarea name="general_notes" rows="4" style="padding:12px; border-radius:10px; border:1px solid var(--border);"><?= esc(old('general_notes')) ?></textarea>
        </div>

        <button type="submit">Salvar</button>
        <a href="<?= base_url('/training-sessions') ?>" class="button secondary">Cancelar</a>
    </form>
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
            opt.hidden = teamId && optTeam !== teamId;
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
