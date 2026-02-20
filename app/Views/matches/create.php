<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:900px;">
    <h1>Novo jogo</h1>
    <form method="post" action="<?= base_url('/matches') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Equipe</label>
            <select name="team_id" id="team_id">
                <?php foreach ($teams as $team): ?>
                    <option value="<?= esc($team['id']) ?>" <?= old('team_id', $team_id ?? '') == $team['id'] ? 'selected' : '' ?>><?= esc($team['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Categoria</label>
            <select name="category_id" id="category_id">
                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?= esc($category['id']) ?>"
                        data-team-id="<?= esc($category['team_id'] ?? '') ?>"
                        <?= old('category_id') == $category['id'] ? 'selected' : '' ?>
                    >
                        <?= esc($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Evento (MATCH)</label>
            <select name="event_id">
                <option value="">Sem vínculo</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?= esc($event['id']) ?>" <?= old('event_id') == $event['id'] ? 'selected' : '' ?>>
                        <?= esc($event['title']) ?> (<?= esc(format_datetime_br($event['start_datetime'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Adversário</label>
            <input type="text" name="opponent_name" value="<?= esc(old('opponent_name')) ?>">
        </div>
        <div class="form-group">
            <label>Competição</label>
            <input type="text" name="competition_name" value="<?= esc(old('competition_name')) ?>">
        </div>
        <div class="form-group">
            <label>Rodada</label>
            <input type="text" name="round_name" value="<?= esc(old('round_name')) ?>">
        </div>
        <div class="form-group">
            <label>Data</label>
            <input type="date" name="match_date" value="<?= esc(old('match_date')) ?>">
        </div>
        <div class="form-group">
            <label>Hora</label>
            <input type="time" name="start_time" value="<?= esc(old('start_time')) ?>">
        </div>
        <div class="form-group">
            <label>Local</label>
            <input type="text" name="location" value="<?= esc(old('location')) ?>">
        </div>
        <div class="form-group">
            <label>Mando</label>
            <select name="home_away">
                <option value="neutral" <?= old('home_away') === 'neutral' ? 'selected' : '' ?>>Neutro</option>
                <option value="home" <?= old('home_away') === 'home' ? 'selected' : '' ?>>Casa</option>
                <option value="away" <?= old('home_away') === 'away' ? 'selected' : '' ?>>Fora</option>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="scheduled" <?= old('status') === 'scheduled' ? 'selected' : '' ?>>Agendado</option>
                <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>>Concluído</option>
                <option value="cancelled" <?= old('status') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
            </select>
        </div>
        <div class="form-group">
            <label>Placar</label>
            <div style="display:flex; gap:8px;">
                <input type="number" name="score_for" placeholder="A favor" value="<?= esc(old('score_for')) ?>">
                <input type="number" name="score_against" placeholder="Contra" value="<?= esc(old('score_against')) ?>">
            </div>
        </div>

        <button type="submit">Salvar</button>
        <a href="<?= base_url('/matches') ?>" class="button secondary">Cancelar</a>
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

        const selected = categorySelect.selectedOptions[0];
        if (selected && selected.hidden) {
            categorySelect.value = '';
        }
    };

    teamSelect.addEventListener('change', filterCategories);
    filterCategories();
})();
</script>
<?= $this->endSection() ?>
