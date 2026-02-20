<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:640px;">
    <h1>Excluir jogo</h1>
    <p>Deseja remover o jogo contra <strong><?= esc($match['opponent_name']) ?></strong></p>

    <form method="post" action="<?= base_url('/matches/' . $match['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit">Confirmar exclusão</button>
        <a href="<?= base_url('/matches') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>