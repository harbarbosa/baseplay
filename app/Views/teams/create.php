<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Nova equipe</h1>
    <form method="post" action="<?= base_url('/teams') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="<?= esc(old('name')) ?>" required>
        </div>
        <div class="form-group">
            <label for="short_name">Apelido</label>
            <input id="short_name" name="short_name" type="text" value="<?= esc(old('short_name')) ?>">
        </div>
        <div class="form-group">
            <label for="description">Descrição</label>
            <input id="description" name="description" type="text" value="<?= esc(old('description')) ?>">
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <div class="form-group">
            <label for="primary_color">Cor primaria</label>
            <input id="primary_color" name="primary_color" type="color" class="bp-color-input" value="<?= esc(old('primary_color') ?: '#7A1126') ?>">
        </div>
        <div class="form-group">
            <label for="secondary_color">Cor secundaria</label>
            <input id="secondary_color" name="secondary_color" type="color" class="bp-color-input" value="<?= esc(old('secondary_color') ?: '#F4D6DB') ?>">
        </div>
        <div class="form-group">
            <label for="team_logo">Logo da equipe</label>
            <input id="team_logo" name="team_logo" type="file" accept="image/*">
        </div>
        <div class="form-group">
            <label for="admin_name">Admin da equipe (nome)</label>
            <input id="admin_name" name="admin_name" type="text" value="<?= esc(old('admin_name')) ?>">
        </div>
        <div class="form-group">
            <label for="admin_email">Admin da equipe (email)</label>
            <input id="admin_email" name="admin_email" type="email" value="<?= esc(old('admin_email')) ?>" required>
        </div>
        <div class="form-group">
            <small>Senha sera gerada automaticamente e enviada depois.</small>
        </div>
        <button type="submit">Criar</button>
        <a href="<?= base_url('/teams') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
