<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Excluir categoria</h1>
    <p>Tem certeza que deseja excluir a categoria <strong><?= esc($category['name']) ?></strong></p>
    <form method="post" action="<?= base_url('/categories/' . $category['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit">Confirmar exclusão</button>
        <a href="<?= base_url('/teams/' . $category['team_id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
