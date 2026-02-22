<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:1100px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1><?= esc($match['team_name'] ?? 'Equipe') ?> x <?= esc($match['opponent_name']) ?></h1>
            <p style="color:var(--muted);">
                <?= esc(format_date_br($match['match_date'])) ?>
                <?= esc($match['start_time'] ?? '') ?>
                Â· <?= esc($match['category_name'] ?? '') ?>
            </p>
        </div>
        <div>
            <?php if (has_permission('matches.update')): ?>
                <a href="<?= base_url('/matches/' . $match['id'] . '/edit') ?>" class="button secondary">Editar</a>
            <?php endif; ?>
            <a href="<?= base_url('/matches') ?>" class="button secondary">Voltar</a>
        </div>
    </div>

    <div style="margin-top:16px;">
        <strong>Status:</strong> <?= esc(enum_label($match['status'], 'status')) ?>
        <?php if ($match['status'] === 'completed'): ?>
            <span style="margin-left:8px;"><strong>Placar:</strong> <?= esc($match['score_for'] ?? '-') ?> x <?= esc($match['score_against'] ?? '-') ?></span>
        <?php endif; ?>
    </div>

    <hr style="margin:24px 0;">

    <h2>Quadros táticos</h2>
    <?php if (!empty($linkedBoards)): ?>
        <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:12px;">
            <?php foreach ($linkedBoards as $board): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; border:1px solid var(--border); border-radius:10px; padding:10px 12px;">
                    <div>
                        <strong><?= esc($board['board_title']) ?></strong>
                        <div style="color:var(--muted); font-size:12px;">
                            <?= esc($board['team_name'] ?? '-') ?> · <?= esc($board['category_name'] ?? '-') ?>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <button type="button" class="button secondary js-open-board" data-board-id="<?= esc($board['tactical_board_id']) ?>">Abrir</button>
                        <?php if (has_permission('matches.update')): ?>
                            <form method="post" action="<?= base_url('/matches/' . $match['id'] . '/tactical-boards/' . $board['tactical_board_id'] . '/delete') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="secondary">Remover</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color:var(--muted);">Nenhuma prancheta vinculada.</p>
    <?php endif; ?>

    <?php if (has_permission('matches.update')): ?>
        <form method="post" action="<?= base_url('/matches/' . $match['id'] . '/tactical-boards') ?>" style="margin-bottom:16px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <?= csrf_field() ?>
            <select name="tactical_board_ids[]" multiple size="5">
                <option value="">Selecionar pranchetas</option>
                <?php foreach ($boardOptions as $boardOption): ?>
                    <option value="<?= esc($boardOption['id']) ?>">
                        <?= esc($boardOption['title']) ?> (<?= esc($boardOption['team_name'] ?? '-') ?> / <?= esc($boardOption['category_name'] ?? '-') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Vincular</button>
            <small style="color:var(--muted);">Use Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplas.</small>
        </form>
    <?php endif; ?>

    <hr style="margin:24px 0;">

    <h2>Convocação</h2>
    <?php if (has_permission('matches.update')): ?>
        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:12px;">
            <form method="post" action="<?= base_url('/matches/' . $match['id'] . '/callups/add-category') ?>">
                <?= csrf_field() ?>
                <button type="submit">Adicionar atletas da categoria</button>
            </form>
            <?php if (!empty($match['event_id'])): ?>
                <form method="post" action="<?= base_url('/matches/' . $match['id'] . '/callups/import') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="secondary">Importar do evento</button>
                </form>
            <?php endif; ?>
            <form method="post" action="<?= base_url('/matches/' . $match['id'] . '/callups/add') ?>" style="display:flex; gap:8px; align-items:center;">
                <?= csrf_field() ?>
                <select name="athlete_id">
                    <option value="">Selecionar atleta</option>
                    <?php foreach ($athletes as $athlete): ?>
                        <option value="<?= esc($athlete['id']) ?>"><?= esc(trim($athlete['first_name'] . ' ' . ($athlete['last_name'] ?? ''))) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Adicionar</button>
            </form>
        </div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>Atleta</th>
                <th>Status</th>
                <th>Titular</th>
                <th>AÃ§Ãµes</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($callups as $callup): ?>
            <tr>
                <td><?= esc(trim($callup['first_name'] . ' ' . ($callup['last_name'] ?? ''))) ?></td>
                <td><?= esc(enum_label($callup['callup_status'], 'invitation')) ?></td>
                <td><?= (int) $callup['is_starting'] === 1 ? 'Sim' : 'NÃ£o' ?></td>
                <td>
                    <?php if (has_permission('matches.update')): ?>
                        <form method="post" action="<?= base_url('/match-callups/' . $callup['id'] . '/update') ?>" style="display:inline-flex; gap:6px; align-items:center;">
                            <?= csrf_field() ?>
                            <select name="callup_status">
                                <?php foreach (['invited', 'confirmed', 'declined', 'pending'] as $status): ?>
                                    <option value="<?= esc($status) ?>" <?= $callup['callup_status'] === $status ? 'selected' : '' ?>><?= esc(enum_label($status, 'invitation')) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="is_starting">
                                <option value="0" <?= (int) $callup['is_starting'] === 0 ? 'selected' : '' ?>>Banco</option>
                                <option value="1" <?= (int) $callup['is_starting'] === 1 ? 'selected' : '' ?>>Titular</option>
                            </select>
                            <button type="submit" class="secondary">Salvar</button>
                        </form>
                        <form method="post" action="<?= base_url('/match-callups/' . $callup['id'] . '/delete') ?>" style="display:inline-block;">
                            <?= csrf_field() ?>
                            <button type="submit" class="secondary">Remover</button>
                        </form>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <hr style="margin:24px 0;">

    <h2>EscalaÃ§Ã£o</h2>
    <?php
    $lineupMap = [];
    foreach ($lineups as $lineup) {
        $lineupMap[$lineup['athlete_id']] = $lineup;
    }
    ?>
    <table class="table">
        <thead>
            <tr>
                <th>Atleta</th>
                <th>FunÃ§Ã£o</th>
                <th>PosiÃ§Ã£o</th>
                <th>Camisa</th>
                <th>AÃ§Ãµes</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($callups as $callup): ?>
            <?php
            $lineup = $lineupMap[$callup['athlete_id']] ?? null;
            $lineupRole = $lineup['lineup_role'] ?? ((int) $callup['is_starting'] === 1 ? 'starting' : 'bench');
            ?>
            <tr>
                <td><?= esc(trim($callup['first_name'] . ' ' . ($callup['last_name'] ?? ''))) ?></td>
                <td><?= $lineupRole === 'starting' ? 'Titular' : 'Banco' ?></td>
                <td><?= esc($lineup['position_code'] ?? '-') ?></td>
                <td><?= esc($lineup['shirt_number'] ?? '-') ?></td>
                <td>
                    <?php if (has_permission('match_lineup.manage')): ?>
                        <form method="post" action="<?= base_url('/matches/' . $match['id'] . '/lineup') ?>" style="display:inline-flex; gap:6px; align-items:center;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="athlete_id" value="<?= esc($callup['athlete_id']) ?>">
                            <select name="lineup_role">
                                <option value="starting" <?= $lineupRole === 'starting' ? 'selected' : '' ?>>Titular</option>
                                <option value="bench" <?= $lineupRole === 'bench' ? 'selected' : '' ?>>Banco</option>
                            </select>
                            <input type="text" name="position_code" placeholder="PosiÃ§Ã£o" value="<?= esc($lineup['position_code'] ?? '') ?>" style="width:90px;">
                            <input type="number" name="shirt_number" placeholder="#" value="<?= esc($lineup['shirt_number'] ?? '') ?>" style="width:70px;">
                            <button type="submit" class="secondary">Salvar</button>
                        </form>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <hr style="margin:24px 0;">

    <h2>EstatÃ­sticas</h2>
    <?php if (has_permission('match_stats.manage')): ?>
        <form method="post" action="<?= base_url('/matches/' . $match['id'] . '/events') ?>" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px;">
            <?= csrf_field() ?>
            <select name="event_type">
                <?php foreach (['goal', 'assist', 'yellow_card', 'red_card', 'sub_in', 'sub_out', 'injury', 'other'] as $type): ?>
                    <option value="<?= esc($type) ?>"><?= esc(enum_label($type, 'match_event')) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="athlete_id">
                <option value="">Atleta</option>
                <?php foreach ($athletes as $athlete): ?>
                    <option value="<?= esc($athlete['id']) ?>"><?= esc(trim($athlete['first_name'] . ' ' . ($athlete['last_name'] ?? ''))) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="related_athlete_id">
                <option value="">Relacionado</option>
                <?php foreach ($athletes as $athlete): ?>
                    <option value="<?= esc($athlete['id']) ?>"><?= esc(trim($athlete['first_name'] . ' ' . ($athlete['last_name'] ?? ''))) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="minute" placeholder="Min" style="width:80px;">
            <input type="text" name="notes" placeholder="ObservaÃ§Ã£o" style="min-width:200px;">
            <button type="submit">Adicionar</button>
        </form>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>Min</th>
                <th>Tipo</th>
                <th>Atleta</th>
                <th>Relacionado</th>
                <th>Notas</th>
                <th>AÃ§Ãµes</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($events as $event): ?>
            <tr>
                <td><?= esc($event['minute'] ?? '-') ?></td>
                <td><?= esc(enum_label($event['event_type'], 'match_event')) ?></td>
                <td><?= esc(trim(($event['first_name'] ?? '') . ' ' . ($event['last_name'] ?? ''))) ?></td>
                <td><?= esc(trim(($event['related_first_name'] ?? '') . ' ' . ($event['related_last_name'] ?? ''))) ?></td>
                <td><?= esc($event['notes'] ?? '-') ?></td>
                <td>
                    <?php if (has_permission('match_stats.manage')): ?>
                        <form method="post" action="<?= base_url('/match-events/' . $event['id'] . '/delete') ?>" style="display:inline-block;">
                            <?= csrf_field() ?>
                            <button type="submit" class="secondary">Remover</button>
                        </form>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <hr style="margin:24px 0;">

    <h2>RelatÃ³rio</h2>
    <?php if (has_permission('match_reports.manage')): ?>
        <form method="post" action="<?= base_url('/matches/' . $match['id'] . '/report') ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Resumo</label>
                <textarea name="summary" rows="4" style="padding:12px; border-radius:10px; border:1px solid var(--border);"><?= esc($report['summary'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Pontos fortes</label>
                <textarea name="strengths" rows="3" style="padding:12px; border-radius:10px; border:1px solid var(--border);"><?= esc($report['strengths'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Pontos fracos</label>
                <textarea name="weaknesses" rows="3" style="padding:12px; border-radius:10px; border:1px solid var(--border);"><?= esc($report['weaknesses'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>AÃ§Ãµes para treino</label>
                <textarea name="next_actions" rows="3" style="padding:12px; border-radius:10px; border:1px solid var(--border);"><?= esc($report['next_actions'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Notas do treinador</label>
                <textarea name="coach_notes" rows="3" style="padding:12px; border-radius:10px; border:1px solid var(--border);"><?= esc($report['coach_notes'] ?? '') ?></textarea>
            </div>
            <button type="submit">Salvar relatÃ³rio</button>
        </form>
    <?php else: ?>
        <p style="color:var(--muted);">Sem permissÃ£o para editar o relatÃ³rio.</p>
    <?php endif; ?>

    <hr style="margin:24px 0;">

    <h2>Anexos</h2>
    <?php if (has_permission('match_reports.manage')): ?>
        <form method="post" action="<?= base_url('/matches/' . $match['id'] . '/attachments') ?>" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px;">
            <?= csrf_field() ?>
            <input type="text" name="original_name" placeholder="DescriÃ§Ã£o (opcional)" style="min-width:220px;">
            <input type="text" name="url" placeholder="Link" style="min-width:260px;">
            <button type="submit">Adicionar</button>
        </form>
    <?php endif; ?>

    <ul>
        <?php foreach ($attachments as $attachment): ?>
            <li style="margin-bottom:6px;">
                <a href="<?= esc($attachment['url']) ?>" target="_blank" rel="noopener"><?= esc($attachment['original_name'] ?: $attachment['url']) ?></a>
                <?php if (has_permission('match_reports.manage')): ?>
                    <form method="post" action="<?= base_url('/match-attachments/' . $attachment['id'] . '/delete') ?>" style="display:inline-block; margin-left:8px;">
                        <?= csrf_field() ?>
                        <button type="submit" class="secondary">Remover</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<div id="bp-board-modal" class="bp-modal" aria-hidden="true">
    <div class="bp-modal-backdrop" data-bp-modal-close="1"></div>
    <div class="bp-modal-dialog" role="dialog" aria-modal="true">
        <div class="bp-modal-header">
            <strong>Quadro tático</strong>
            <button type="button" class="button secondary" data-bp-modal-close="1">Fechar</button>
        </div>
        <div class="bp-modal-body">
            <iframe id="bp-board-frame" title="Quadro tático" src="" loading="lazy"></iframe>
        </div>
    </div>
</div>

<style>
.bp-modal {position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:1000;}
.bp-modal.open {display:flex;}
.bp-modal-backdrop {position:absolute; inset:0; background:rgba(0,0,0,0.45);}
.bp-modal-dialog {position:relative; background:#fff; width:min(1100px, 95vw); height:min(720px, 90vh); border-radius:12px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.25); display:flex; flex-direction:column;}
.bp-modal-header {display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid var(--border);}
.bp-modal-body {flex:1;}
.bp-modal-body iframe {width:100%; height:100%; border:0;}
</style>

<script>
(() => {
    const modal = document.getElementById('bp-board-modal');
    if (!modal) return;
    const frame = document.getElementById('bp-board-frame');
    const openButtons = document.querySelectorAll('.js-open-board');
    const closeButtons = modal.querySelectorAll('[data-bp-modal-close]');
    const baseUrl = <?= json_encode(base_url(), JSON_UNESCAPED_SLASHES) ?>;

    const openModal = (boardId) => {
        frame.src = `${baseUrl}/tactical-boards/${boardId}?viewer=1&embed=1`;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        frame.src = '';
    };

    openButtons.forEach((btn) => {
        btn.addEventListener('click', () => openModal(btn.getAttribute('data-board-id')));
    });
    closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
})();
</script>
<?= $this->endSection() ?>


