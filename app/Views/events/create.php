<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Novo evento</h1>
    <form method="post" action="<?= base_url('/events') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="team_id">Equipe</label>
            <select id="team_id" name="team_id" onchange="window.location='<?= base_url('/events/create') ?>?team_id=' + this.value" required>
                <option value="">Selecione</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= esc($team['id']) ?>" <?= ($team_id ?? '') == $team['id'] ? 'selected' : ''  ?>>
                        <?= esc($team['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="category_id">Categoria</label>
            <select id="category_id" name="category_id" required>
                <option value="">Selecione</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= esc($category['id']) ?>" data-team-id="<?= esc($category['team_id'] ?? '') ?>" <?= old('category_id') == $category['id'] ? 'selected' : ''  ?>>
                        <?= esc($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="type">Tipo</label>
            <select id="type" name="type" required>
                <option value="">Selecione</option>
                <?php foreach ($types as $typeKey => $typeLabel): ?>
                    <option value="<?= esc($typeKey) ?>" <?= old('type') === $typeKey ? 'selected' : ''  ?>>
                        <?= esc($typeLabel) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Título</label>
            <input id="title" name="title" type="text" value="<?= esc(old('title')) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Descrição</label>
            <input id="description" name="description" type="text" value="<?= esc(old('description')) ?>">
        </div>
        <div class="form-group">
            <label for="start_datetime">Início</label>
            <input id="start_datetime" name="start_datetime" type="datetime-local" value="<?= esc(old('start_datetime')) ?>" required>
        </div>
        <div class="form-group">
            <label for="end_datetime">Fim</label>
            <input id="end_datetime" name="end_datetime" type="datetime-local" value="<?= esc(old('end_datetime')) ?>">
        </div>
        <div class="form-group">
            <label for="location">Local</label>
            <input id="location" name="location" type="text" value="<?= esc(old('location')) ?>">
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="scheduled" <?= old('status') === 'scheduled' ? 'selected' : '' ?>>Agendado</option>
                <option value="cancelled" <?= old('status') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>Concluído</option>
            </select>
        </div>

        <div class="form-group" style="margin-top:8px;">
            <strong>Jogadores (Atletas)</strong>
        </div>
        <div class="form-group">
            <label style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="participants_all_category" value="1" <?= old('participants_all_category') ? 'checked' : '' ?>>
                Selecionar todos da categoria
            </label>
            <label style="display:flex; align-items:center; gap:8px; margin-top:8px;">
                <input type="checkbox" name="participants_all_team" value="1" <?= old('participants_all_team') ? 'checked' : '' ?>>
                Selecionar todos da equipe
            </label>
            <small style="color:var(--muted);">Você pode usar os dois ou selecionar atletas específicos.</small>
        </div>
        <div class="form-group">
            <label for="participant_athlete_ids">Selecionar atletas específicos</label>
            <select id="participant_athlete_ids" name="participant_athlete_ids[]" multiple size="8">
                <?php foreach ($athletes as $athlete): ?>
                    <?php $fullName = trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? '')); ?>
                    <option value="<?= esc($athlete['id']) ?>"
                        data-team-id="<?= esc($athlete['team_id'] ?? '') ?>"
                        data-category-id="<?= esc($athlete['category_id'] ?? '') ?>"
                        <?= in_array($athlete['id'], (array) old('participant_athlete_ids', []), true) ? 'selected' : '' ?>>
                        <?= esc($fullName) ?> (<?= esc($athlete['category_name'] ?? '-') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color:var(--muted);">Use Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplos.</small>
        </div>
        <div class="form-group">
            <label>Atletas da categoria selecionada</label>
            <div id="athlete-preview" style="display:flex; flex-wrap:wrap; gap:6px;"></div>
        </div>

        <div class="form-group" style="margin-top:14px;">
            <strong>Responsáveis</strong>
        </div>
        <div class="form-group">
            <label style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="participants_guardians_all_category" value="1" <?= old('participants_guardians_all_category') ? 'checked' : '' ?>>
                Selecionar responsáveis da categoria
            </label>
            <label style="display:flex; align-items:center; gap:8px; margin-top:8px;">
                <input type="checkbox" name="participants_guardians_all_team" value="1" <?= old('participants_guardians_all_team') ? 'checked' : '' ?>>
                Selecionar responsáveis da equipe
            </label>
        </div>
        <div class="form-group">
            <label for="participant_guardian_ids">Selecionar responsáveis específicos</label>
            <select id="participant_guardian_ids" name="participant_guardian_ids[]" multiple size="6">
                <?php foreach ($guardians as $guardian): ?>
                    <?php $guardianName = trim($guardian['full_name'] ?? ''); ?>
                    <option value="<?= esc($guardian['id']) ?>" data-team-id="<?= esc($guardian['team_id'] ?? '') ?>"
                        <?= in_array($guardian['id'], (array) old('participant_guardian_ids', []), true) ? 'selected' : '' ?>>
                        <?= esc($guardianName) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Responsáveis da categoria selecionada</label>
            <div id="guardian-preview" style="display:flex; flex-wrap:wrap; gap:6px;"></div>
        </div>

        <div class="form-group" style="margin-top:14px;">
            <strong>Equipe Técnica</strong>
        </div>
        <div class="form-group">
            <label style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="participants_staff_all_team" value="1" <?= old('participants_staff_all_team') ? 'checked' : '' ?>>
                Selecionar equipe técnica da equipe
            </label>
        </div>
        <div class="form-group">
            <label for="participant_staff_ids">Selecionar membros específicos</label>
            <select id="participant_staff_ids" name="participant_staff_ids[]" multiple size="6">
                <?php foreach ($staff as $staffMember): ?>
                    <?php $staffName = trim($staffMember['name'] ?? ''); ?>
                    <option value="<?= esc($staffMember['id']) ?>" data-team-id="<?= esc($staffMember['team_id'] ?? '') ?>"
                        <?= in_array($staffMember['id'], (array) old('participant_staff_ids', []), true) ? 'selected' : '' ?>>
                        <?= esc($staffName) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Equipe técnica da equipe selecionada</label>
            <div id="staff-preview" style="display:flex; flex-wrap:wrap; gap:6px;"></div>
        </div>

        <button type="submit">Criar</button>
        <a href="<?= base_url('/events') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<script>
(() => {
    const teamSelect = document.getElementById('team_id');
    const categorySelect = document.getElementById('category_id');
    const athleteSelect = document.getElementById('participant_athlete_ids');
    const guardianSelect = document.getElementById('participant_guardian_ids');
    const staffSelect = document.getElementById('participant_staff_ids');
    if (!teamSelect || !categorySelect || !athleteSelect || !guardianSelect || !staffSelect) return;

    const renderPreview = (selectEl, containerEl, labelPrefix = '', enabled = true, emptyLabel = 'Nenhum selecionado.') => {
        if (!containerEl) return;
        if (!enabled) {
            containerEl.innerHTML = `<span style="color:var(--muted);">${emptyLabel}</span>`;
            return;
        }
        const names = [];
        Array.from(selectEl.options).forEach((opt) => {
            if (opt.selected && !opt.hidden && opt.value) {
                names.push(opt.text);
            }
        });
        if (names.length === 0) {
            containerEl.innerHTML = '<span style="color:var(--muted);">Nenhum selecionado.</span>';
            return;
        }
        containerEl.innerHTML = names.map((name) => `<span class="badge badge-info">${labelPrefix}${name}</span>`).join('');
    };

    const filterByTeamCategory = () => {
        const teamId = teamSelect.value;
        const categoryId = categorySelect.value;
        const hasCategory = !!categoryId;
        const hasTeam = !!teamId;
        athleteSelect.disabled = !hasCategory;
        guardianSelect.disabled = !hasCategory;
        staffSelect.disabled = !hasTeam;
        Array.from(athleteSelect.options).forEach((opt) => {
            const optTeam = opt.getAttribute('data-team-id');
            const optCategory = opt.getAttribute('data-category-id');
            let visible = true;
            if (!hasCategory) visible = false;
            if (teamId && optTeam !== teamId) visible = false;
            if (categoryId && optCategory !== categoryId) visible = false;
            opt.hidden = !visible;
            if (!visible) opt.selected = false;
        });
        Array.from(guardianSelect.options).forEach((opt) => {
            const optTeam = opt.getAttribute('data-team-id');
            let visible = true;
            if (!hasCategory) visible = false;
            if (teamId && optTeam && optTeam !== teamId) visible = false;
            opt.hidden = !visible;
            if (!visible) opt.selected = false;
        });
        Array.from(staffSelect.options).forEach((opt) => {
            const optTeam = opt.getAttribute('data-team-id');
            let visible = true;
            if (!hasTeam) visible = false;
            if (teamId && optTeam && optTeam !== teamId) visible = false;
            opt.hidden = !visible;
            if (!visible) opt.selected = false;
        });

        renderPreview(
            athleteSelect,
            document.getElementById('athlete-preview'),
            '',
            !!categoryId,
            'Selecione uma categoria para listar os atletas.'
        );
        renderPreview(
            guardianSelect,
            document.getElementById('guardian-preview'),
            '',
            !!categoryId,
            'Selecione uma categoria para listar os responsáveis.'
        );
        renderPreview(
            staffSelect,
            document.getElementById('staff-preview'),
            '',
            !!teamId,
            'Selecione uma equipe para listar a equipe técnica.'
        );
    };

    teamSelect.addEventListener('change', filterByTeamCategory);
    categorySelect.addEventListener('change', filterByTeamCategory);
    filterByTeamCategory();
})();
</script>
<?= $this->endSection() ?>
