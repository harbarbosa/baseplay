<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:600px;">
    <h1>Excluir documento</h1>
    <p>Tem certeza que deseja excluir o documento <strong><?= esc($document['original_name'] ?? 'Documento') ?></strong></p>
    <form method="post" action="<?= base_url('/documents/' . $document['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit">Confirmar exclusão</button>
        <a href="<?= base_url('/documents') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>