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
            <label for="description">DescriÃ§Ã£o</label>
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
            <label for="primary_color">Cor primÃ¡ria</label>
            <input id="primary_color" name="primary_color" type="color" class="bp-color-input" value="<?= esc(old('primary_color') ?: '#7A1126') ?>">
        </div>
        <div class="form-group">
            <label for="secondary_color">Cor secundÃ¡ria</label>
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
            <small>Senha serÃ¡ gerada automaticamente e enviada depois.</small>
        </div>

        <h3 style="margin-top:20px;">Dados adicionais</h3>
        <div class="form-group">
            <label for="legal_name">RazÃ£o social</label>
            <input id="legal_name" name="legal_name" type="text" value="<?= esc(old('legal_name')) ?>">
        </div>
        <div class="form-group">
            <label for="trade_name">Nome fantasia</label>
            <input id="trade_name" name="trade_name" type="text" value="<?= esc(old('trade_name')) ?>">
        </div>
        <div class="form-group">
            <label for="cnpj">CNPJ</label>
            <input id="cnpj" name="cnpj" type="text" value="<?= esc(old('cnpj')) ?>">
        </div>
        <div class="form-group">
            <label for="foundation_date">FundaÃ§Ã£o</label>
            <input id="foundation_date" name="foundation_date" type="date" value="<?= esc(old('foundation_date')) ?>">
        </div>
        <div class="form-group">
            <label for="president_name">Presidente</label>
            <input id="president_name" name="president_name" type="text" value="<?= esc(old('president_name')) ?>">
        </div>
        <div class="form-group">
            <label for="vice_president_name">Vice-presidente</label>
            <input id="vice_president_name" name="vice_president_name" type="text" value="<?= esc(old('vice_president_name')) ?>">
        </div>
        <div class="form-group">
            <label for="phone">Telefone</label>
            <input id="phone" name="phone" type="text" value="<?= esc(old('phone')) ?>">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= esc(old('email')) ?>">
        </div>
        <div class="form-group">
            <label for="website">Site</label>
            <input id="website" name="website" type="text" value="<?= esc(old('website')) ?>">
        </div>
        <div class="form-group">
            <label for="address_street">EndereÃ§o</label>
            <input id="address_street" name="address_street" type="text" value="<?= esc(old('address_street')) ?>">
        </div>
        <div class="form-group">
            <label for="address_number">NÃºmero</label>
            <input id="address_number" name="address_number" type="text" value="<?= esc(old('address_number')) ?>">
        </div>
        <div class="form-group">
            <label for="address_complement">Complemento</label>
            <input id="address_complement" name="address_complement" type="text" value="<?= esc(old('address_complement')) ?>">
        </div>
        <div class="form-group">
            <label for="address_neighborhood">Bairro</label>
            <input id="address_neighborhood" name="address_neighborhood" type="text" value="<?= esc(old('address_neighborhood')) ?>">
        </div>
        <div class="form-group">
            <label for="address_city">Cidade</label>
            <input id="address_city" name="address_city" type="text" value="<?= esc(old('address_city')) ?>">
        </div>
        <div class="form-group">
            <label for="address_state">UF</label>
            <input id="address_state" name="address_state" type="text" value="<?= esc(old('address_state')) ?>">
        </div>
        <div class="form-group">
            <label for="address_zip">CEP</label>
            <input id="address_zip" name="address_zip" type="text" value="<?= esc(old('address_zip')) ?>">
        </div>
        <div class="form-group">
            <label for="address_country">PaÃ­s</label>
            <input id="address_country" name="address_country" type="text" value="<?= esc(old('address_country')) ?>">
        </div>
        <div class="form-group">
            <label for="notes">ObservaÃ§Ãµes</label>
            <textarea id="notes" name="notes" rows="3"><?= esc(old('notes')) ?></textarea>
        </div>
        <button type="submit">Criar</button>
        <a href="<?= base_url('/teams') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>