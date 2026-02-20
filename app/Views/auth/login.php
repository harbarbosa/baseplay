<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<h1 class="bp-auth-title">Entrar no BasePlay</h1>
<p class="bp-auth-subtitle">Gestao de equipes esportivas de base.</p>
<form method="post" action="<?= base_url('login') ?>" autocomplete="off" class="bp-form">
    <?= csrf_field() ?>
    <div class="bp-form-group">
        <label for="email">E-mail</label>
        <input
            id="email"
            name="email"
            type="email"
            value="<?= esc(old('email')) ?>"
            autocomplete="new-password"
            autocapitalize="off"
            autocorrect="off"
            spellcheck="false"
            inputmode="email"
            readonly
            required
            class="bp-input"
        >
    </div>
    <div class="bp-form-group">
        <label for="password">Senha</label>
        <input
            id="password"
            name="password"
            type="password"
            autocomplete="new-password"
            data-lpignore="true"
            data-1p-ignore="true"
            readonly
            required
            class="bp-input"
        >
    </div>
    <button type="submit" class="bp-btn-primary bp-btn-block">Entrar</button>
</form>
<p class="bp-auth-footer"><a href="<?= base_url('password/forgot') ?>">Esqueci minha senha</a></p>
<script>
(() => {
    const email = document.getElementById('email');
    const password = document.getElementById('password');

    const unlock = (el) => {
        if (el.hasAttribute('readonly')) {
            el.removeAttribute('readonly');
        }
    };

    ['focus', 'mousedown', 'touchstart', 'keydown'].forEach((eventName) => {
        email.addEventListener(eventName, () => unlock(email), { once: true });
        password.addEventListener(eventName, () => unlock(password), { once: true });
    });
})();
</script>
<?= $this->endSection() ?>
