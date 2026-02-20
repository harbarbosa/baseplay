<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:600px;">
    <h1>Excluir aviso</h1>
    <p>Tem certeza que deseja excluir o aviso <strong><?= esc($notice['title']) ?></strong></p>
    <form method="post" action="<?= base_url('/notices/' . $notice['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit">Confirmar exclusão</button>
        <a href="<?= base_url('/notices') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>