<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Versões da prancheta</h1>
            <p style="color:var(--muted);"><?= esc($board['title']) ?></p>
        </div>
        <a href="<?= base_url('/tactical-boards/' . $board['id']) ?>" class="button secondary">Voltar ao editor</a>
    </div>

    <table class="table" style="margin-top:16px;">
        <thead>
        <tr>
            <th>Versão</th>
            <th>Criado em</th>
            <th>Usuário</th>
            <th>Ação</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($versions as $state): ?>
            <tr>
                <td>v<?= esc($state['version']) ?></td>
                <td><?= esc(format_datetime_br($state['created_at'] ?? null)) ?></td>
                <td><?= esc($state['created_by_name'] ?? '-') ?></td>
                <td><a href="<?= base_url('/tactical-boards/' . $board['id'] . '/load/' . $state['id']) ?>">Carregar</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>

