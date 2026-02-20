<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1><?= esc($event['title']) ?></h1>
            <p style="color:var(--muted);"><?= esc($types[$event['type']] ?? $event['type']) ?> | <?= esc(format_datetime_br($event['start_datetime'])) ?></p>
        </div>
        <div>
            <?php if (has_permission('events.update')): ?>
                <a href="<?= base_url('/events/' . $event['id'] . '/edit') ?>" class="button">Editar evento</a>
            <?php endif; ?>
            <?php if (has_permission('training_sessions.create')): ?>
                <a href="<?= base_url('/training-sessions/create-from-event/' . $event['id']) ?>" class="button secondary">Criar sessão</a>
            <?php endif; ?>
            <?php if ($event['type'] === 'MATCH' && has_permission('matches.create')): ?>
                <a href="<?= base_url('/matches/create-from-event/' . $event['id']) ?>" class="button secondary">Criar partida</a>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top:16px;">
        <h2>Dados do evento</h2>
        <p>Equipe: <?= esc($event['team_name'] ?? '-') ?></p>
        <p>Categoria: <?= esc($event['category_name'] ?? '-') ?></p>
        <p>Status: <strong><?= esc(enum_label($event['status'], 'status')) ?></strong></p>
        <p>Local: <?= esc($event['location'] ?? '-') ?></p>
        <p>Descrição: <?= esc($event['description'] ?? '-') ?></p>
    </div>

    <div style="margin-top:24px;">
        <h2>Convocados</h2>
        <?php if (has_permission('invitations.manage')): ?>
            <form method="post" action="<?= base_url('/events/' . $event['id'] . '/participants/add-category') ?>" style="margin-bottom:12px;">
                <?= csrf_field() ?>
                <button type="submit">Adicionar atletas da categoria</button>
            </form>
            <form method="post" action="<?= base_url('/events/' . $event['id'] . '/participants/add') ?>" style="margin-bottom:12px;">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="athlete_id">Adicionar atleta individual</label>
                    <select id="athlete_id" name="athlete_id">
                        <option value="">Selecione</option>
                        <?php foreach ($athletes as $athlete): ?>
                            <option value="<?= esc($athlete['id']) ?>">
                                <?= esc(trim($athlete['first_name'] . ' ' . ($athlete['last_name'] ?? ''))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Adicionar</button>
            </form>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>Atleta</th>
                    <th>Status convite</th>
                    <th>Observações</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($participants as $participant): ?>
                <tr>
                    <td><?= esc(trim($participant['first_name'] . ' ' . ($participant['last_name'] ?? ''))) ?></td>
                    <td><?= esc(enum_label($participant['invitation_status'], 'invitation')) ?></td>
                    <td><?= esc($participant['notes'] ?? '-') ?></td>
                    <td>
                        <?php if (has_permission('invitations.manage')): ?>
                            <form method="post" action="<?= base_url('/event-participants/' . $participant['id'] . '/update') ?>" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <select name="invitation_status">
                                    <?php foreach (['invited','pending','confirmed','declined'] as $status): ?>
                                        <option value="<?= $status ?>" <?= $participant['invitation_status'] === $status ? 'selected'  : ''  ?>><?= esc(enum_label($status, 'invitation')) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="secondary">Atualizar</button>
                            </form>
                            <form method="post" action="<?= base_url('/event-participants/' . $participant['id'] . '/delete') ?>" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <button type="submit" class="secondary">Remover</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top:24px;">
        <h2>Presença</h2>
        <div style="display:grid; gap:8px;">
            <?php foreach ($participants as $participant): ?>
                <?php $attendance = $attendanceMap[$participant['athlete_id']] ?? null; ?>
                <div class="card" style="padding:12px;">
                    <strong><?= esc(trim($participant['first_name'] . ' ' . ($participant['last_name'] ?? ''))) ?></strong>
                    <span style="margin-left:8px; color:var(--muted);">Status: <?= esc(!empty($attendance['status']) ? enum_label($attendance['status'], 'attendance') : '-') ?></span>
                    <?php if (has_permission('attendance.manage')): ?>
                        <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
                            <?php foreach (['present' => 'Presente', 'late' => 'Atrasado', 'absent' => 'Faltou', 'justified' => 'Justificou'] as $key => $label): ?>
                                <form method="post" action="<?= base_url('/events/' . $event['id'] . '/attendance') ?>" style="display:inline-block;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="athlete_id" value="<?= esc($participant['athlete_id']) ?>">
                                    <input type="hidden" name="status" value="<?= esc($key) ?>">
                                    <button type="submit" class="secondary"><?= esc($label) ?></button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="margin-top:24px;">
        <h2>Observações</h2>
        <p style="color:var(--muted);">Em breve: comentários e registros do evento.</p>
    </div>
</div>
<?= $this->endSection() ?>