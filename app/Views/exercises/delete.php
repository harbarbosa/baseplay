<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:600px;">
    <h1>Excluir exercício</h1>
    <p>Tem certeza que deseja excluir <strong><?= esc($exercise['title']) ?></strong></p>
    <form method="post" action="<?= base_url('/exercises/' . $exercise['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit">Confirmar exclusão</button>
        <a href="<?= base_url('/exercises') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>