<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:800px;">
    <h1>Documento</h1>
    <p><strong>Arquivo:</strong> <?= esc($document['original_name'] ?? '-') ?></p>
    <p><strong>Tipo:</strong> <?= esc($document['type_name'] ?? '-') ?></p>
    <p><strong>Atleta:</strong>
        <?php
        $fullName = trim(($document['first_name'] ?? '') . ' ' . ($document['last_name'] ?? ''));
        echo esc($fullName !== '' ? $fullName : '-');
        ?>
    </p>
    <p><strong>Responsável:</strong> <?= esc($document['guardian_name'] ?? '-') ?></p>
    <p><strong>Equipe:</strong> <?= esc($document['team_name'] ?? '-') ?></p>
    <p><strong>Emissão:</strong> <?= esc(format_date_br($document['issued_at'] ?? null)) ?></p>
    <p><strong>Vencimento:</strong> <?= esc(format_date_br($document['expires_at'] ?? null)) ?></p>
    <p><strong>Status:</strong> <?= esc(enum_label($document['status'], 'status')) ?></p>
    <p><strong>Observações:</strong> <?= esc($document['notes'] ?? '-') ?></p>

    <div style="margin-top:16px;">
        <a href="<?= base_url('/documents/' . $document['id'] . '/download') ?>" class="button">Baixar</a>
        <?php if (has_permission('documents.update')): ?>
            <a href="<?= base_url('/documents/' . $document['id'] . '/edit') ?>" class="button secondary">Editar</a>
        <?php endif; ?>
        <a href="<?= base_url('/documents') ?>" class="button secondary">Voltar</a>
    </div>
</div>
<?= $this->endSection() ?>
