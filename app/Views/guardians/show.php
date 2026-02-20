<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1><?= esc($guardian['full_name']) ?></h1>
    <p>Telefone: <?= esc($guardian['phone'] ?? '-') ?></p>
    <p>E-mail: <?= esc($guardian['email'] ?? '-') ?></p>
    <p>Parentesco: <?= esc($guardian['relation_type'] ?? '-') ?></p>
    <p>Status: <?= esc(enum_label($guardian['status'], 'status')) ?></p>

    <div style="margin-top:20px;">
        <h2>Atletas vinculados</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Atleta</th>
                    <th>PrimÃ¡rio</th>
                    <th>ObservaÃ§Ãµes</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($athletes as $athlete): ?>
                <tr>
                    <td><?= esc(trim($athlete['first_name'] . ' ' . ($athlete['last_name'] ?? ''))) ?></td>
                    <td><?= $athlete['is_primary'] ? 'Sim'  : 'NÃ£o'  ?></td>
                    <td><?= esc($athlete['notes'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
