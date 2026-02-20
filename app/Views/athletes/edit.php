<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Editar atleta</h1>
    <form method="post" action="<?= base_url('/athletes/' . $athlete['id'] . '/update') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="team_id">Equipe</label>
            <select id="team_id" name="team_id" onchange="window.location='<?= base_url('/athletes/' . $athlete['id'] . '/edit') ?>?team_id=' + this.value">
                <option value="">Selecione</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= esc($team['id']) ?>" <?= ($team_id ?? '') == $team['id'] ? 'selected' : '' ?>>
                        <?= esc($team['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="category_id">Categoria</label>
            <select id="category_id" name="category_id" required>
                <?php if (empty($categories)): ?>
                    <option value="">Selecione uma equipe primeiro</option>
                <?php else: ?>
                    <option value="">Selecione</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= esc($category['id']) ?>" <?= (old('category_id') ?? $athlete['category_id']) == $category['id'] ? 'selected' : '' ?>>
                            <?= esc($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="first_name">Nome</label>
            <input id="first_name" name="first_name" type="text" value="<?= esc(old('first_name') ?? $athlete['first_name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Sobrenome</label>
            <input id="last_name" name="last_name" type="text" value="<?= esc(old('last_name') ?? $athlete['last_name']) ?>">
        </div>
        <div class="form-group">
            <label for="birth_date">Data de nascimento</label>
            <input id="birth_date" name="birth_date" type="date" value="<?= esc(old('birth_date') ?? $athlete['birth_date']) ?>" required>
        </div>
        <div class="form-group">
            <label for="document_id">Documento</label>
            <input id="document_id" name="document_id" type="text" value="<?= esc(old('document_id') ?? $athlete['document_id']) ?>">
        </div>
        <div class="form-group">
            <label for="position">Posição</label>
            <input id="position" name="position" type="text" value="<?= esc(old('position') ?? $athlete['position']) ?>">
        </div>
        <div class="form-group">
            <label for="dominant_foot">Pé dominante</label>
            <select id="dominant_foot" name="dominant_foot">
                <option value="">Selecione</option>
                <option value="right" <?= (old('dominant_foot') ?? $athlete['dominant_foot']) === 'right' ? 'selected' : '' ?>>Direito</option>
                <option value="left" <?= (old('dominant_foot') ?? $athlete['dominant_foot']) === 'left' ? 'selected' : '' ?>>Esquerdo</option>
                <option value="both" <?= (old('dominant_foot') ?? $athlete['dominant_foot']) === 'both' ? 'selected' : '' ?>>Ambos</option>
            </select>
        </div>
        <div class="form-group">
            <label for="height_cm">Altura (cm)</label>
            <input id="height_cm" name="height_cm" type="number" value="<?= esc(old('height_cm') ?? $athlete['height_cm']) ?>">
        </div>
        <div class="form-group">
            <label for="weight_kg">Peso (kg)</label>
            <input id="weight_kg" name="weight_kg" type="number" step="0.01" value="<?= esc(old('weight_kg') ?? $athlete['weight_kg']) ?>">
        </div>
        <div class="form-group">
            <label for="internal_notes">Observações internas</label>
            <input id="internal_notes" name="internal_notes" type="text" value="<?= esc(old('internal_notes') ?? $athlete['internal_notes']) ?>">
        </div>
        <div class="form-group">
            <label for="medical_notes">Observações de saúde</label>
            <input id="medical_notes" name="medical_notes" type="text" value="<?= esc(old('medical_notes') ?? $athlete['medical_notes']) ?>">
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="active" <?= (old('status') ?? $athlete['status']) === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="inactive" <?= (old('status') ?? $athlete['status']) === 'inactive' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <button type="submit">Salvar</button>
        <a href="<?= base_url('/athletes/' . $athlete['id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
