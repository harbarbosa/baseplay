<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<h1>Recuperar senha</h1>
<form method="post" action="<?= base_url('password/forgot') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" value="<?= esc(old('email')) ?>" required>
    </div>
    <button type="submit">Enviar</button>
</form>
<p style="margin-top:16px;"><a href="<?= base_url('login') ?>">Voltar ao login</a></p>
<?= $this->endSection() ?>
