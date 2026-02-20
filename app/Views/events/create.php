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
                    <option value="<?= esc($category['id']) ?>" <?= old('category_id') == $category['id'] ? 'selected' : ''  ?>>
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
                <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>>Concluído</option>
            </select>
        </div>
        <button type="submit">Criar</button>
        <a href="<?= base_url('/events') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
