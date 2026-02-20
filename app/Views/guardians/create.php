<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Novo responsável</h1>
    <form method="post" action="<?= base_url('/guardians') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="full_name">Nome completo</label>
            <input id="full_name" name="full_name" type="text" value="<?= esc(old('full_name')) ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Telefone</label>
            <input id="phone" name="phone" type="text" value="<?= esc(old('phone')) ?>">
        </div>
        <div class="form-group">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" value="<?= esc(old('email')) ?>">
        </div>
        <div class="form-group">
            <label for="relation_type">Parentesco</label>
            <input id="relation_type" name="relation_type" type="text" value="<?= esc(old('relation_type')) ?>">
        </div>
        <div class="form-group">
            <label for="document_id">Documento</label>
            <input id="document_id" name="document_id" type="text" value="<?= esc(old('document_id')) ?>">
        </div>
        <div class="form-group">
            <label for="address">Endereço</label>
            <input id="address" name="address" type="text" value="<?= esc(old('address')) ?>">
        </div>

        <hr>
        <h3>Vínculo opcional com atleta</h3>
        <div class="form-group">
            <label for="team_id">Equipe</label>
            <select id="team_id">
                <option value="">Selecione</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= esc($team['id']) ?>" <?= old('team_id') == $team['id'] ? 'selected' : '' ?>><?= esc($team['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="category_id">Categoria</label>
            <select id="category_id">
                <option value="">Selecione</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= esc($category['id']) ?>" data-team-id="<?= esc($category['team_id'] ?? '') ?>" <?= old('category_id') == $category['id'] ? 'selected' : '' ?>>
                        <?= esc($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="athlete_id">Atleta para vincular</label>
            <select id="athlete_id" name="athlete_id" disabled>
                <option value="">Selecione</option>
                <?php foreach ($athletes as $athlete): ?>
                    <?php $full = trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? '')); ?>
                    <option
                        value="<?= esc($athlete['id']) ?>"
                        data-team-id="<?= esc($athlete['team_id'] ?? '') ?>"
                        data-category-id="<?= esc($athlete['category_id'] ?? '') ?>"
                        <?= old('athlete_id') == $athlete['id'] ? 'selected' : '' ?>
                    >
                        <?= esc($full) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color:var(--muted);">Escolha equipe e categoria para liberar atletas.</small>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="link_is_primary" value="1" <?= old('link_is_primary') ? 'checked' : '' ?>> Marcar como primário</label>
        </div>
        <div class="form-group">
            <label for="link_notes">Observações do vínculo</label>
            <input id="link_notes" name="link_notes" type="text" value="<?= esc(old('link_notes')) ?>">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <button type="submit">Criar</button>
        <a href="<?= base_url('/guardians') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<script>
(() => {
    const teamSelect = document.getElementById('team_id');
    const categorySelect = document.getElementById('category_id');
    const athleteSelect = document.getElementById('athlete_id');
    if (!teamSelect || !categorySelect || !athleteSelect) return;

    const filterCategories = () => {
        const teamId = teamSelect.value;
        Array.from(categorySelect.options).forEach((opt) => {
            if (!opt.value) return;
            opt.hidden = !!teamId && opt.getAttribute('data-team-id') !== teamId;
        });
        const selected = categorySelect.selectedOptions[0];
        if (selected && selected.hidden) categorySelect.value = '';
    };

    const filterAthletes = () => {
        const teamId = teamSelect.value;
        const categoryId = categorySelect.value;
        athleteSelect.disabled = !(teamId && categoryId);

        Array.from(athleteSelect.options).forEach((opt) => {
            if (!opt.value) return;
            const optTeamId = opt.getAttribute('data-team-id');
            const optCategoryId = opt.getAttribute('data-category-id');
            opt.hidden = !(optTeamId === teamId && optCategoryId === categoryId);
        });

        const selected = athleteSelect.selectedOptions[0];
        if (selected && selected.hidden) athleteSelect.value = '';
    };

    teamSelect.addEventListener('change', () => {
        filterCategories();
        filterAthletes();
    });
    categorySelect.addEventListener('change', filterAthletes);

    filterCategories();
    filterAthletes();
})();
</script>
<?= $this->endSection() ?>
