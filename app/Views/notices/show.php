<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:900px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1><?= esc($notice['title']) ?></h1>
            <div style="color:var(--muted);">
                <?= esc($notice['team_name'] ?? 'Geral') ?>
                <?php if (!empty($notice['category_name'])): ?>
                    • <?= esc($notice['category_name']) ?>
                <?php endif; ?>
                • <?= esc(ucfirst($notice['priority'])) ?>
                • <?= esc(enum_label($notice['status'], 'status')) ?>
            </div>
        </div>
        <div>
            <?php if (has_permission('notices.update')): ?>
                <a href="<?= base_url('/notices/' . $notice['id'] . '/edit') ?>" class="button secondary">Editar</a>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top:16px; white-space:pre-wrap; line-height:1.5;">
        <?= esc($notice['message']) ?>
    </div>

    <div style="margin-top:16px; display:flex; gap:12px; align-items:center;">
        <?php if (!$read): ?>
            <form method="post" action="<?= base_url('/notices/' . $notice['id'] . '/read') ?>">
                <?= csrf_field() ?>
                <button type="submit">Marcar como lido</button>
            </form>
        <?php else: ?>
            <span class="badge badge-normal">Lido</span>
        <?php endif; ?>
    </div>

    <?php if (!empty($readers)): ?>
        <div style="margin-top:24px;">
            <h3>Leituras</h3>
            <ul style="padding-left:18px;">
                <?php foreach ($readers as $reader): ?>
                    <li><?= esc($reader['user_name'] ?? 'Usuário') ?> - <?= esc(format_datetime_br($reader['read_at'])) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div style="margin-top:24px;">
        <h3>Respostas</h3>
        <form method="post" action="<?= base_url('/notices/' . $notice['id'] . '/reply') ?>" style="margin-bottom:12px;">
            <?= csrf_field() ?>
            <div class="form-group">
                <textarea name="message" rows="3" placeholder="Escreva uma resposta" style="padding:12px; border-radius:10px; border:1px solid var(--border);"></textarea>
            </div>
            <button type="submit">Enviar resposta</button>
        </form>

        <?php if (empty($replies)): ?>
            <p style="color:var(--muted);">Nenhuma resposta ainda.</p>
        <?php else: ?>
            <ul style="padding-left:18px;">
                <?php foreach ($replies as $reply): ?>
                    <li>
                        <strong><?= esc($reply['user_name'] ?? 'Usuário') ?>:</strong>
                        <?= esc($reply['message']) ?>
                        <span style="color:var(--muted);">(<?= esc(format_datetime_br($reply['created_at'])) ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
