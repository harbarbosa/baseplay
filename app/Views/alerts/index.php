<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Alertas</h1>
    <p>Pendentes: <strong><?= esc($unreadCount ?? 0) ?></strong></p>

    <form method="get" action="<?= base_url('/alerts') ?>" style="margin:16px 0; display:flex; gap:12px; flex-wrap:wrap;">
        <select name="is_read">
            <option value="">Todos</option>
            <option value="0" <?= ((string)($filters['is_read'] ?? '') === '0') ? 'selected' : '' ?>>Nao lidos</option>
            <option value="1" <?= ((string)($filters['is_read'] ?? '') === '1') ? 'selected' : '' ?>>Lidos</option>
        </select>
        <select name="severity">
            <option value="">Severidade</option>
            <option value="info" <?= (($filters['severity'] ?? '') === 'info') ? 'selected' : '' ?>>Info</option>
            <option value="warning" <?= (($filters['severity'] ?? '') === 'warning') ? 'selected' : '' ?>>Warning</option>
            <option value="critical" <?= (($filters['severity'] ?? '') === 'critical') ? 'selected' : '' ?>>Critical</option>
        </select>
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/alerts') ?>" class="button secondary">Limpar</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Titulo</th>
                <th>Tipo</th>
                <th>Severidade</th>
                <th>Criado em</th>
                <th>Status</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($items)): ?>
            <tr><td colspan="6">Nenhum alerta encontrado.</td></tr>
        <?php else: ?>
            <?php foreach ($items as $alert): ?>
                <tr>
                    <td>
                        <strong><?= esc($alert['title']) ?></strong>
                        <div style="font-size:12px;color:#64748b; margin-top:4px;"><?= esc($alert['description']) ?></div>
                    </td>
                    <td><?= esc($alert['type']) ?></td>
                    <td><span class="badge badge-<?= esc($alert['severity']) ?>"><?= esc($alert['severity']) ?></span></td>
                    <td><?= esc(format_datetime_br($alert['created_at'])) ?></td>
                    <td><?= !empty($alert['is_read']) ? 'Lido' : 'Pendente' ?></td>
                    <td>
                        <?php if (empty($alert['is_read'])): ?>
                            <form method="post" action="<?= base_url('/alerts/' . (int) $alert['id'] . '/read') ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit">Marcar como lido</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top:16px;">
        <?= $pager->links('alerts') ?>
    </div>
</div>
<?= $this->endSection() ?>