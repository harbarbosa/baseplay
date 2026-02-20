<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Editar categoria</h1>
    <p>Equipe: <strong><?= esc($team['name']) ?></strong></p>
    <form method="post" action="<?= base_url('/categories/' . $category['id'] . '/update') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="<?= esc(old('name') ?? $category['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="year_from">Ano inicial</label>
            <input id="year_from" name="year_from" type="number" value="<?= esc(old('year_from') ?? $category['year_from']) ?>">
        </div>
        <div class="form-group">
            <label for="year_to">Ano final</label>
            <input id="year_to" name="year_to" type="number" value="<?= esc(old('year_to') ?? $category['year_to']) ?>">
        </div>
        <div class="form-group">
            <label for="gender">Gênero</label>
            <select id="gender" name="gender">
                <option value="mixed" <?= (old('gender') ?? $category['gender']) === 'mixed' ? 'selected' : '' ?>>Misto</option>
                <option value="male" <?= (old('gender') ?? $category['gender']) === 'male' ? 'selected' : '' ?>>Masculino</option>
                <option value="female" <?= (old('gender') ?? $category['gender']) === 'female' ? 'selected' : '' ?>>Feminino</option>
            </select>
        </div>
        <div class="form-group">
            <label for="training_days">Dias de treino</label>
            <input id="training_days" name="training_days" type="text" value="<?= esc(old('training_days') ?? $category['training_days']) ?>">
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="active" <?= (old('status') ?? $category['status']) === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="inactive" <?= (old('status') ?? $category['status']) === 'inactive' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <button type="submit">Salvar</button>
        <a href="<?= base_url('/teams/' . $team['id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
