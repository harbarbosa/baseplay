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

    <?php if (!empty($match)): ?>
        <hr style="margin:24px 0;">

        <h2>Detalhes do jogo</h2>
        <p><strong>Adversário:</strong> <?= esc($match['opponent_name'] ?? '-') ?></p>
        <p><strong>Competição:</strong> <?= esc($match['competition_name'] ?? '-') ?></p>
        <p><strong>Rodada:</strong> <?= esc($match['round_name'] ?? '-') ?></p>
        <p><strong>Data:</strong> <?= esc(format_date_br($match['match_date'] ?? '')) ?></p>
        <p><strong>Hora:</strong> <?= esc($match['start_time'] ?? '-') ?></p>
        <p><strong>Local:</strong> <?= esc($match['location'] ?? '-') ?></p>
        <p><strong>Status:</strong> <?= esc(enum_label($match['status'] ?? 'scheduled', 'status')) ?></p>
        <?php if (($match['status'] ?? '') === 'completed'): ?>
            <p><strong>Placar:</strong> <?= esc($match['score_for'] ?? '-') ?> x <?= esc($match['score_against'] ?? '-') ?></p>
        <?php endif; ?>

        <div style="margin-top:16px;">
            <h3>Relatório do jogo</h3>
            <?php if (!empty($matchReport)): ?>
                <p><strong>Resumo:</strong> <?= esc($matchReport['summary'] ?? '-') ?></p>
                <p><strong>Pontos fortes:</strong> <?= esc($matchReport['strengths'] ?? '-') ?></p>
                <p><strong>Notas do treinador:</strong> <?= esc($matchReport['coach_notes'] ?? '-') ?></p>
            <?php else: ?>
                <p style="color:var(--muted);">Sem relatório registrado.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top:16px;">
            <h3>Quadro tático</h3>
            <?php if (!empty($linkedBoards)): ?>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <?php foreach ($linkedBoards as $board): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; border:1px solid var(--border); border-radius:10px; padding:10px 12px;">
                            <div>
                                <strong><?= esc($board['board_title']) ?></strong>
                                <div style="color:var(--muted); font-size:12px;">
                                    <?= esc($board['team_name'] ?? '-') ?> · <?= esc($board['category_name'] ?? '-') ?>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="button secondary js-open-board" data-board-id="<?= esc($board['tactical_board_id']) ?>">Abrir</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color:var(--muted);">Nenhuma prancheta vinculada.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($linkedBoards)): ?>
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
            btn.addEventListener('click', () => openModal(btn.dataset.boardId));
        });
        closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
    })();
    </script>
<?php endif; ?>
<?= $this->endSection() ?>
