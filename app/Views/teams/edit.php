<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Editar equipe</h1>
    <form method="post" action="<?= base_url('/teams/' . $team['id'] . '/update') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="<?= esc(old('name') ?? $team['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="short_name">Apelido</label>
            <input id="short_name" name="short_name" type="text" value="<?= esc(old('short_name') ?? $team['short_name']) ?>">
        </div>
        <div class="form-group">
            <label for="description">Descrição</label>
            <input id="description" name="description" type="text" value="<?= esc(old('description') ?? $team['description']) ?>">
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="active" <?= (old('status') ?? $team['status']) === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="inactive" <?= (old('status') ?? $team['status']) === 'inactive' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <div class="form-group">
            <label for="primary_color">Cor primária</label>
            <input id="primary_color" name="primary_color" type="color" class="bp-color-input" value="<?= esc(old('primary_color') ?? ($team['primary_color'] ?? '#7A1126')) ?>">
        </div>
        <div class="form-group">
            <label for="secondary_color">Cor secundária</label>
            <input id="secondary_color" name="secondary_color" type="color" class="bp-color-input" value="<?= esc(old('secondary_color') ?? ($team['secondary_color'] ?? '#F4D6DB')) ?>">
        </div>
        <div class="form-group">
            <label for="team_logo">Logo da equipe</label>
            <input id="team_logo" name="team_logo" type="file" accept="image/*">
        </div>
        <?php if (!empty($team['logo_path'])): ?>
            <div class="form-group">
                <img src="<?= base_url($team['logo_path']) ?>" alt="Logo atual" style="max-width:220px; max-height:120px; width:auto; height:auto; object-fit:contain;">
            </div>
        <?php endif; ?>

        <div class="form-group" style="margin-top:16px;">
            <label>Admin da equipe</label>
            <?php if (!empty($adminUser)): ?>
                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <div style="min-width:220px;">
                        <small style="color:var(--muted);">Nome</small>
                        <div><?= esc($adminUser['name'] ?? '-') ?></div>
                    </div>
                    <div style="min-width:220px;">
                        <small style="color:var(--muted);">Email</small>
                        <div><?= esc($adminUser['email'] ?? '-') ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div style="color:var(--muted);">Nenhum admin vinculado.</div>
            <?php endif; ?>
        </div>

        <h3 style="margin-top:20px;">Dados adicionais</h3>
        <div class="form-group">
            <label for="legal_name">Razão social</label>
            <input id="legal_name" name="legal_name" type="text" value="<?= esc(old('legal_name') ?? $team['legal_name']) ?>">
        </div>
        <div class="form-group">
            <label for="trade_name">Nome fantasia</label>
            <input id="trade_name" name="trade_name" type="text" value="<?= esc(old('trade_name') ?? $team['trade_name']) ?>">
        </div>
        <div class="form-group">
            <label for="cnpj">CNPJ</label>
            <input id="cnpj" name="cnpj" type="text" value="<?= esc(old('cnpj') ?? $team['cnpj']) ?>">
        </div>
        <div class="form-group">
            <label for="foundation_date">Fundação</label>
            <input id="foundation_date" name="foundation_date" type="date" value="<?= esc(old('foundation_date') ?? $team['foundation_date']) ?>">
        </div>
        <div class="form-group">
            <label for="president_name">Presidente</label>
            <input id="president_name" name="president_name" type="text" value="<?= esc(old('president_name') ?? $team['president_name']) ?>">
        </div>
        <div class="form-group">
            <label for="vice_president_name">Vice-presidente</label>
            <input id="vice_president_name" name="vice_president_name" type="text" value="<?= esc(old('vice_president_name') ?? $team['vice_president_name']) ?>">
        </div>
        <div class="form-group">
            <label for="phone">Telefone</label>
            <input id="phone" name="phone" type="text" value="<?= esc(old('phone') ?? $team['phone']) ?>">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= esc(old('email') ?? $team['email']) ?>">
        </div>
        <div class="form-group">
            <label for="website">Site</label>
            <input id="website" name="website" type="text" value="<?= esc(old('website') ?? $team['website']) ?>">
        </div>
        <div class="form-group">
            <label for="address_street">Endereço</label>
            <input id="address_street" name="address_street" type="text" value="<?= esc(old('address_street') ?? $team['address_street']) ?>">
        </div>
        <div class="form-group">
            <label for="address_number">Número</label>
            <input id="address_number" name="address_number" type="text" value="<?= esc(old('address_number') ?? $team['address_number']) ?>">
        </div>
        <div class="form-group">
            <label for="address_complement">Complemento</label>
            <input id="address_complement" name="address_complement" type="text" value="<?= esc(old('address_complement') ?? $team['address_complement']) ?>">
        </div>
        <div class="form-group">
            <label for="address_neighborhood">Bairro</label>
            <input id="address_neighborhood" name="address_neighborhood" type="text" value="<?= esc(old('address_neighborhood') ?? $team['address_neighborhood']) ?>">
        </div>
        <div class="form-group">
            <label for="address_city">Cidade</label>
            <input id="address_city" name="address_city" type="text" value="<?= esc(old('address_city') ?? $team['address_city']) ?>">
        </div>
        <div class="form-group">
            <label for="address_state">UF</label>
            <input id="address_state" name="address_state" type="text" value="<?= esc(old('address_state') ?? $team['address_state']) ?>">
        </div>
        <div class="form-group">
            <label for="address_zip">CEP</label>
            <input id="address_zip" name="address_zip" type="text" value="<?= esc(old('address_zip') ?? $team['address_zip']) ?>">
        </div>
        <div class="form-group">
            <label for="address_country">País</label>
            <input id="address_country" name="address_country" type="text" value="<?= esc(old('address_country') ?? $team['address_country']) ?>">
        </div>
        <div class="form-group">
            <label for="notes">Observações</label>
            <textarea id="notes" name="notes" rows="3"><?= esc(old('notes') ?? $team['notes']) ?></textarea>
        </div>

        <button type="submit">Salvar</button>
        <a href="<?= base_url('/teams/' . $team['id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
