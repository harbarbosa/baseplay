<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Excluir responsável</h1>
    <p>Tem certeza que deseja excluir o responsável <strong><?= esc($guardian['full_name']) ?></strong></p>
    <form method="post" action="<?= base_url('/guardians/' . $guardian['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit">Confirmar exclusão</button>
        <a href="<?= base_url('/guardians/' . $guardian['id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
