<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:600px;">
    <h1>Excluir plano</h1>
    <p>Tem certeza que deseja excluir <strong><?= esc($plan['title']) ?></strong></p>
    <form method="post" action="<?= base_url('/training-plans/' . $plan['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit">Confirmar exclusão</button>
        <a href="<?= base_url('/training-plans') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>