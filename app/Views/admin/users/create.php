<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Novo usuário</h1>
    <form method="post" action="<?= base_url('/admin/users') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="<?= esc(old('name')) ?>" required>
        </div>
        <div class="form-group">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" value="<?= esc(old('email')) ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Senha</label>
            <input id="password" name="password" type="password" required>
        </div>
        <?php if (!empty($teams)): ?>
            <?php if (!empty($showTeamSelect)): ?>
                <div class="form-group">
                    <label for="team_id">Equipe</label>
                    <select id="team_id" name="team_id">
                        <option value="">Sem equipe</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= esc($team['id']) ?>" <?= (string) old('team_id') === (string) $team['id'] ? 'selected' : '' ?>>
                                <?= esc($team['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>Equipe</label>
                    <input type="text" value="<?= esc($teams[0]['name'] ?? 'Equipe') ?>" disabled>
                    <input type="hidden" name="team_id" value="<?= esc($selectedTeamId) ?>">
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="form-group">
            <label for="role_id">Papel</label>
            <select id="role_id" name="role_id" required>
                <option value="">Selecione</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= esc($role['id']) ?>" <?= old('role_id') == $role['id'] ? 'selected'  : ''  ?>>
                        <?= esc($role['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Criar</button>
        <a href="<?= base_url('/admin/users') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
