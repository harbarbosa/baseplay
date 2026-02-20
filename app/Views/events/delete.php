<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Excluir evento</h1>
    <p>Tem certeza que deseja excluir o evento <strong><?= esc($event['title']) ?></strong></p>
    <form method="post" action="<?= base_url('/events/' . $event['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit">Confirmar exclusf¯,¿,½o</button>
        <a href="<?= base_url('/events/' . $event['id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
