<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1><?= esc($team['name']) ?></h1>
            <p style="color:var(--muted);"><?= esc($team['description'] ?? '') ?></p>
        </div>
        <div>
            <?php if (has_permission('teams.update')): ?>
                <a href="<?= base_url('/teams/' . $team['id'] . '/edit') ?>" class="button">Editar equipe</a>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top:20px;">
        <h2>Dados adicionais</h2>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
            <div><small style="color:var(--muted);">RazÃ£o social</small><div><?= esc($team['legal_name'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">Nome fantasia</small><div><?= esc($team['trade_name'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">CNPJ</small><div><?= esc($team['cnpj'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">FundaÃ§Ã£o</small><div><?= esc($team['foundation_date'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">Presidente</small><div><?= esc($team['president_name'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">Vice-presidente</small><div><?= esc($team['vice_president_name'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">Telefone</small><div><?= esc($team['phone'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">Email</small><div><?= esc($team['email'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">Site</small><div><?= esc($team['website'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">EndereÃ§o</small><div><?= esc($team['address_street'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">NÃºmero</small><div><?= esc($team['address_number'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">Complemento</small><div><?= esc($team['address_complement'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">Bairro</small><div><?= esc($team['address_neighborhood'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">Cidade</small><div><?= esc($team['address_city'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">UF</small><div><?= esc($team['address_state'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">CEP</small><div><?= esc($team['address_zip'] ?? '-') ?></div></div>
            <div><small style="color:var(--muted);">PaÃ­s</small><div><?= esc($team['address_country'] ?? '-') ?></div></div>
            <div style="grid-column: 1 / -1;"><small style="color:var(--muted);">ObservaÃ§Ãµes</small><div><?= esc($team['notes'] ?? '-') ?></div></div>
        </div>
    </div>

    <div style="margin-top:24px;">
        <?php
        $genderLabels = [
            'male' => 'Masculino',
            'female' => 'Feminino',
            'mixed' => 'Misto',
            'other' => 'Outro',
        ];
        ?>
        <h2>Categorias</h2>
        <?php if (has_permission('categories.create')): ?>
            <a href="<?= base_url('/teams/' . $team['id'] . '/categories/create') ?>" class="button" style="margin:12px 0;">Nova categoria</a>
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Ano</th>
                    <th>GÃªnero</th>
                    <th>Dias</th>
                    <th>Status</th>
                    <th>AÃ§Ãµes</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= esc($category['name']) ?></td>
                    <td><?= esc(($category['year_from'] ?? '-') . ' / ' . ($category['year_to'] ?? '-')) ?></td>
                    <td><?= esc($genderLabels[$category['gender']] ?? $category['gender']) ?></td>
                    <td><?= esc($category['training_days'] ?? '-') ?></td>
                    <td><?= esc(enum_label($category['status'], 'status')) ?></td>
                    <td>
                        <div class="bp-action-buttons">
                            <?php if (has_permission('categories.update')): ?>
                                <a href="<?= base_url('/categories/' . $category['id'] . '/edit') ?>" class="bp-icon-btn" title="Editar" aria-label="Editar">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4l10-10-4-4L4 16v4z"/><path d="M14 6l4 4"/></svg>
                                </a>
                            <?php endif; ?>
                            <?php if (has_permission('categories.delete')): ?>
                                <form method="post" action="<?= base_url('/categories/' . $category['id'] . '/delete') ?>" class="bp-inline-form" onsubmit="return confirm('Excluir esta categoria?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="bp-icon-btn bp-icon-danger" title="Excluir" aria-label="Excluir">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/></svg>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>