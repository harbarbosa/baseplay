<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Excluir equipe</h1>
    <p>Tem certeza que deseja excluir a equipe <strong><?= esc($team['name']) ?></strong></p>
    <form method="post" action="<?= base_url('/teams/' . $team['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit">Confirmar exclusão</button>
        <a href="<?= base_url('/teams/' . $team['id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
