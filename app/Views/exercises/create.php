<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:900px;">
    <h1>Novo exercício</h1>
    <form method="post" action="<?= base_url('/exercises') ?>">
        <?= csrf_field() ?>

        <?php
        $ageLabels = [
            'u10' => 'Sub-10',
            'u11' => 'Sub-11',
            'u12' => 'Sub-12',
            'u13' => 'Sub-13',
            'u14' => 'Sub-14',
            'u15' => 'Sub-15',
            'u16' => 'Sub-16',
            'u17' => 'Sub-17',
            'u18' => 'Sub-18',
            'u19' => 'Sub-19',
            'u20' => 'Sub-20',
            'all' => 'Todas',
        ];
        $intensityLabels = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
        ];
        $boardOptions = $boardOptions ?? [];
        $linkedBoardIds = $linkedBoardIds ?? [];
        ?>

        <div class="form-group">
            <label>Título</label>
            <input type="text" name="title" value="<?= esc(old('title')) ?>">
        </div>
        <div class="form-group">
            <label>Objetivo</label>
            <input type="text" name="objective" value="<?= esc(old('objective')) ?>">
        </div>
        <div class="form-group">
            <label>Descrição</label>
            <textarea name="description" rows="5" style="padding:12px; border-radius:10px; border:1px solid var(--border);"><?= esc(old('description')) ?></textarea>
        </div>
        <div class="form-group">
            <label>Faixa etária</label>
            <select name="age_group">
                <?php foreach ($ageLabels as $age => $label): ?>
                    <option value="<?= esc($age) ?>" <?= old('age_group', 'all') === $age ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Intensidade</label>
            <select name="intensity">
                <?php foreach ($intensityLabels as $intensity => $label): ?>
                    <option value="<?= esc($intensity) ?>" <?= old('intensity', 'medium') === $intensity ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Duração (min)</label>
            <input type="number" name="duration_min" value="<?= esc(old('duration_min')) ?>">
        </div>
        <div class="form-group">
            <label>Jogadores (mín / máx)</label>
            <input type="number" name="players_min" value="<?= esc(old('players_min')) ?>">
            <input type="number" name="players_max" value="<?= esc(old('players_max')) ?>">
        </div>
        <div class="form-group">
            <label>Materiais</label>
            <input type="text" name="materials" value="<?= esc(old('materials')) ?>">
        </div>
        <div class="form-group">
            <label>Vídeo (URL)</label>
            <input type="text" name="video_url" value="<?= esc(old('video_url')) ?>">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <div class="form-group">
            <label>Quadros táticos (opcional)</label>
            <select name="tactical_board_ids[]" multiple size="5">
                <?php if (empty($boardOptions)): ?>
                    <option value="">Nenhuma prancheta disponível</option>
                <?php else: ?>
                    <?php foreach ($boardOptions as $boardOption): ?>
                        <option value="<?= esc($boardOption['id']) ?>" <?= in_array((int) $boardOption['id'], $linkedBoardIds, true) ? 'selected' : '' ?>>
                            <?= esc($boardOption['title']) ?> (<?= esc($boardOption['team_name'] ?? '-') ?> / <?= esc($boardOption['category_name'] ?? '-') ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <button type="submit">Salvar</button>
        <a href="<?= base_url('/exercises') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
