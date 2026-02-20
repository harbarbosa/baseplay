<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<h1>Redefinir senha</h1>
<form method="post" action="<?= base_url('password/reset') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= esc($token) ?>">
    <div class="form-group">
        <label for="password">Nova senha</label>
        <input id="password" name="password" type="password" required>
    </div>
    <button type="submit">Atualizar senha</button>
</form>
<p style="margin-top:16px;"><a href="<?= base_url('login') ?>">Voltar ao login</a></p>
<?= $this->endSection() ?>
