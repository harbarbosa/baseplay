<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1><?= esc(trim($athlete['first_name'] . ' ' . ($athlete['last_name'] ?? ''))) ?></h1>
            <p style="color:var(--muted);">Equipe: <?= esc($athlete['team_name'] ?? '-') ?> | Categoria: <?= esc($athlete['category_name'] ?? '-') ?></p>
        </div>
        <div>
            <?php if (has_permission('athletes.update')): ?>
                <a href="<?= base_url('/athletes/' . $athlete['id'] . '/edit') ?>" class="button">Editar atleta</a>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top:20px;">
        <h2>Ãltima atividade</h2>
        <div style="display:grid; gap:12px; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); margin-bottom:16px;">
            <div class="card" style="margin:0; border:1px solid var(--line);">
                <p style="margin:0 0 6px 0; color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.04em;">Ãltimo treino</p>
                <?php if (!empty($lastActivity['last_training'])): ?>
                    <p style="margin:0 0 4px 0;"><strong><?= esc(format_date_br($lastActivity['last_training']['date'])) ?></strong></p>
                    <p style="margin:0 0 4px 0;"><?= esc($lastActivity['last_training']['title']) ?></p>
                    <p style="margin:0; color:var(--muted); font-size:12px;">hÃ¡ <?= esc((string) $lastActivity['last_training']['days_ago']) ?> dias</p>
                <?php else: ?>
                    <p style="margin:0; color:var(--muted);">Sem registros</p>
                <?php endif; ?>
            </div>
            <div class="card" style="margin:0; border:1px solid var(--line);">
                <p style="margin:0 0 6px 0; color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.04em;">Ãltimo jogo</p>
                <?php if (!empty($lastActivity['last_match'])): ?>
                    <p style="margin:0 0 4px 0;"><strong><?= esc(format_date_br($lastActivity['last_match']['date'])) ?></strong></p>
                    <p style="margin:0 0 4px 0;"><?= esc($lastActivity['last_match']['title']) ?></p>
                    <p style="margin:0; color:var(--muted); font-size:12px;">hÃ¡ <?= esc((string) $lastActivity['last_match']['days_ago']) ?> dias</p>
                <?php else: ?>
                    <p style="margin:0; color:var(--muted);">Sem registros</p>
                <?php endif; ?>
            </div>
        </div>

        <h2>Dados</h2>
        <p>Status: <strong><?= esc(enum_label($athlete['status'], 'status')) ?></strong></p>
        <p>Nascimento: <?= esc(format_date_br($athlete['birth_date'])) ?></p>
        <p>Documento: <?= esc($athlete['document_id'] ?? '-') ?></p>
        <p>PosiÃ§Ã£o: <?= esc($athlete['position'] ?? '-') ?></p>
        <p>PÃ© dominante: <?= esc($athlete['dominant_foot'] ?? '-') ?></p>
        <p>Altura/Peso: <?= esc($athlete['height_cm'] ?? '-') ?> / <?= esc($athlete['weight_kg'] ?? '-') ?></p>
        <p>ObservaÃ§Ãµes internas: <?= esc($athlete['internal_notes'] ?? '-') ?></p>
        <p>ObservaÃ§Ãµes de saÃºde: <?= esc($athlete['medical_notes'] ?? '-') ?></p>
    </div>

    <div style="margin-top:24px;">
        <h2>ResponsÃ¡veis</h2>
        <?php if (has_permission('guardians.create')): ?>
            <form method="post" action="<?= base_url('/athletes/' . $athlete['id'] . '/guardians/link') ?>" style="margin-bottom:12px;">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="guardian_id">Vincular responsÃ¡vel existente</label>
                    <select id="guardian_id" name="guardian_id">
                        <option value="">Selecione</option>
                        <?php foreach ($guardiansList as $guardian): ?>
                            <option value="<?= esc($guardian['id']) ?>"><?= esc($guardian['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_primary" value="1"> Marcar como primÃ¡rio</label>
                </div>
                <div class="form-group">
                    <label for="notes">ObservaÃ§Ãµes</label>
                    <input id="notes" name="notes" type="text">
                </div>
                <button type="submit">Vincular</button>
            </form>

            <form method="post" action="<?= base_url('/athletes/' . $athlete['id'] . '/guardians/create-link') ?>">
                <?= csrf_field() ?>
                <h3>Criar novo responsÃ¡vel e vincular</h3>
                <div class="form-group">
                    <label for="full_name">Nome completo</label>
                    <input id="full_name" name="full_name" type="text" required>
                </div>
                <div class="form-group">
                    <label for="phone">Telefone</label>
                    <input id="phone" name="phone" type="text">
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input id="email" name="email" type="email">
                </div>
                <div class="form-group">
                    <label for="relation_type">Parentesco</label>
                    <input id="relation_type" name="relation_type" type="text">
                </div>
                <button type="submit">Criar e vincular</button>
            </form>
        <?php endif; ?>

        <table class="table" style="margin-top:16px;">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Contato</th>
                    <th>PrimÃ¡rio</th>
                    <th>ObservaÃ§Ãµes</th>
                    <th>AÃ§Ãµes</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($guardians as $guardian): ?>
                <tr>
                    <td><?= esc($guardian['full_name']) ?></td>
                    <td><?= esc($guardian['phone'] ?? '-') ?> | <?= esc($guardian['email'] ?? '-') ?></td>
                    <td><?= $guardian['is_primary'] ? 'Sim'  : 'NÃ£o'  ?></td>
                    <td><?= esc($guardian['notes'] ?? '-') ?></td>
                    <td>
                        <?php if (has_permission('guardians.update')): ?>
                            <form method="post" action="<?= base_url('/athlete-guardians/' . $guardian['id'] . '/update') ?>" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="is_primary" value="<?= $guardian['is_primary'] ? 0  : 1  ?>">
                                <button type="submit" class="secondary"><?= $guardian['is_primary'] ? 'Desmarcar'  : 'Tornar primÃ¡rio'  ?></button>
                            </form>
                        <?php endif; ?>
                        <?php if (has_permission('guardians.delete')): ?>
                            <form method="post" action="<?= base_url('/athlete-guardians/' . $guardian['id'] . '/delete') ?>" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <button type="submit" class="secondary">Remover vÃ­nculo</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top:24px;">
        <h2>HistÃ³rico</h2>
        <p style="color:var(--muted);">Em breve: treinos, presenÃ§as e jogos.</p>
    </div>
</div>
<?= $this->endSection() ?>
