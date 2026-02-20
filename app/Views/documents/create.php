<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:800px;">
    <h1>Upload de documento</h1>
    <form method="post" action="<?= base_url('/documents') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php $isGuardianUser = !empty($guardianContext['is_guardian']); ?>
        <?php if ($isGuardianUser): ?>
            <input type="hidden" name="guardian_id" value="<?= esc($guardianContext['guardian_id'] ?? '') ?>">
            <div class="alert success">Documento enviado como responsável: <strong><?= esc($guardianContext['guardian_name'] ?? 'Responsável') ?></strong>.</div>
        <?php endif; ?>

        <div class="form-group">
            <label>Tipo de documento</label>
            <select name="document_type_id" required>
                <option value="">Selecione</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= esc($type['id']) ?>" <?= old('document_type_id') == $type['id'] ? 'selected' : '' ?>>
                        <?= esc($type['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!$isGuardianUser): ?>
            <div class="form-group">
                <label>Clube</label>
                <select name="team_id" id="doc_team_id">
                    <option value="">Selecione a equipe</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?= esc($team['id']) ?>" <?= old('team_id') == $team['id'] ? 'selected' : '' ?>>
                            <?= esc($team['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Categoria</label>
                <select name="category_id" id="doc_category_id">
                    <option value="">Selecione a categoria</option>
                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?= esc($category['id']) ?>"
                            data-team-id="<?= esc($category['team_id'] ?? '') ?>"
                            <?= old('category_id') == $category['id'] ? 'selected' : '' ?>
                        >
                            <?= esc(preg_replace('/^sub[\s-]*/iu', 'Categoria ', (string) $category['name'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Atleta</label>
                <select name="athlete_id" id="doc_athlete_id" disabled>
                    <option value="">Selecione o atleta</option>
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
                <small style="color:var(--muted);">Filtrado por clube e categoria.</small>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label>Arquivo (PDF/JPG/PNG)</label>
            <input type="file" name="document_file" accept="application/pdf,image/jpeg,image/png" required>
        </div>

        <div class="form-group">
            <label>Data de emissão</label>
            <input type="date" name="issued_at" value="<?= esc(old('issued_at')) ?>">
        </div>

        <div class="form-group">
            <label>Data de vencimento</label>
            <input type="date" name="expires_at" value="<?= esc(old('expires_at')) ?>">
        </div>

        <div class="form-group">
            <label>Observações</label>
            <input type="text" name="notes" value="<?= esc(old('notes')) ?>">
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="archived" <?= old('status') === 'archived' ? 'selected' : '' ?>>Arquivado</option>
            </select>
        </div>

        <button type="submit">Enviar</button>
        <a href="<?= base_url('/documents') ?>" class="button secondary">Cancelar</a>
    </form>
</div>

<?php if (!$isGuardianUser): ?>
<script>
(() => {
    const teamSelect = document.getElementById('doc_team_id');
    const categorySelect = document.getElementById('doc_category_id');
    const athleteSelect = document.getElementById('doc_athlete_id');
    if (!teamSelect || !categorySelect || !athleteSelect) return;

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

    const filterAthletes = () => {
        const teamId = teamSelect.value;
        const categoryId = categorySelect.value;
        if (!teamId || !categoryId) {
            athleteSelect.value = '';
            athleteSelect.disabled = true;
            Array.from(athleteSelect.options).forEach((opt) => {
                if (!opt.value) return;
                opt.hidden = true;
            });
            return;
        }

        athleteSelect.disabled = false;
        Array.from(athleteSelect.options).forEach((opt) => {
            if (!opt.value) return;
            const optTeam = opt.getAttribute('data-team-id');
            const optCategory = opt.getAttribute('data-category-id');
            opt.hidden = (optTeam !== teamId) || (optCategory !== categoryId);
        });

        const selected = athleteSelect.selectedOptions[0];
        if (selected && selected.hidden) {
            athleteSelect.value = '';
        }
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
<?php endif; ?>
<?= $this->endSection() ?>
