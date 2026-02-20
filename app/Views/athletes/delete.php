<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Excluir atleta</h1>
    <p>Tem certeza que deseja excluir o atleta <strong><?= esc($athlete['first_name']) ?></strong></p>
    <form method="post" action="<?= base_url('/athletes/' . $athlete['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit">Confirmar exclusão</button>
        <a href="<?= base_url('/athletes/' . $athlete['id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
