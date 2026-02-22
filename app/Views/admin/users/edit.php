<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Editar usuário</h1>
    <form method="post" action="<?= base_url('/admin/users/' . $user['id'] . '/update') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="<?= esc(old('name') ?? $user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" value="<?= esc(old('email') ?? $user['email']) ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Nova senha (opcional)</label>
            <input id="password" name="password" type="password">
        </div>
        <?php if (!empty($teams)): ?>
            <?php if (!empty($showTeamSelect)): ?>
                <div class="form-group">
                    <label for="team_id">Equipe</label>
                    <select id="team_id" name="team_id">
                        <option value="">Sem equipe</option>
                        <?php foreach ($teams as $team): ?>
                            <?php $selected = (string) ($selectedTeamId ?? old('team_id')) === (string) $team['id']; ?>
                            <option value="<?= esc($team['id']) ?>" <?= $selected ? 'selected' : '' ?>>
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
                    <?php $selectedRole = (string) ($selectedRoleId ?? old('role_id')) === (string) $role['id']; ?>
                    <option value="<?= esc($role['id']) ?>" <?= $selectedRole ? 'selected'  : ''  ?>>
                        <?= esc($role['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (!empty($categories)): ?>
            <?php $currentCategoryIds = old('category_ids') ?? $selectedCategoryIds; ?>
            <div class="form-group">
                <label for="category_ids">Categorias (opcional)</label>
                <select id="category_ids" name="category_ids[]" multiple size="6">
                    <?php foreach ($categories as $category): ?>
                        <?php $selected = in_array((int) $category['id'], array_map('intval', (array) $currentCategoryIds), true); ?>
                        <option value="<?= esc($category['id']) ?>" data-team-id="<?= esc($category['team_id']) ?>" <?= $selected ? 'selected' : '' ?>>
                            <?= esc($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Se não selecionar, o usuário acessa todas as categorias da equipe.</small>
            </div>
        <?php endif; ?>
        <button type="submit">Salvar</button>
        <a href="<?= base_url('/admin/users') ?>" class="button secondary">Cancelar</a>
    </form>
</div>

<?php if (!empty($showTeamSelect) && !empty($categories)): ?>
<script>
    (function () {
        const teamSelect = document.getElementById('team_id');
        const categorySelect = document.getElementById('category_ids');
        if (!teamSelect || !categorySelect) {
            return;
        }
        const filterOptions = () => {
            const teamId = teamSelect.value;
            const options = categorySelect.querySelectorAll('option');
            options.forEach((option) => {
                if (!teamId) {
                    option.hidden = true;
                    option.disabled = true;
                    option.selected = false;
                    return;
                }
                const match = option.dataset.teamId === teamId;
                option.hidden = !match;
                option.disabled = !match;
                if (!match) {
                    option.selected = false;
                }
            });
        };
        teamSelect.addEventListener('change', filterOptions);
        filterOptions();
    })();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
