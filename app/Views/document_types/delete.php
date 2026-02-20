<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:600px;">
    <h1>Excluir tipo</h1>
    <p>Tem certeza que deseja excluir o tipo <strong><?= esc($type['name']) ?></strong></p>
    <form method="post" action="<?= base_url('/document-types/' . $type['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit">Confirmar exclusão</button>
        <a href="<?= base_url('/document-types') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>