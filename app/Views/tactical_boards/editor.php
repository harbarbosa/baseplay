<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$decoded = json_decode($currentState['state_json'] ?? '', true);
if (!is_array($decoded)) {
    $decoded = [
        'field' => ['background' => 'soccer_field_v1', 'aspectRatio' => 1.6],
        'items' => [],
        'meta' => [],
    ];
}
?>
<?php $isTemplateMode = $templateMode ?? false; ?>
<div class="card tactical-board-page">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px;">
        <div>
            <h1><?= esc($board['title']) ?></h1>
            <p style="color:var(--muted); margin:0;">
                <?= esc($board['team_name'] ?? '-') ?> - <?= esc($board['category_name'] ?? '-') ?>
                <?php if (!empty($board['description'])): ?>
                    - <?= esc($board['description']) ?>
                <?php endif; ?>
            </p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php if ($canEdit): ?>
                <button type="button" id="presentation-toggle" class="secondary">Apresentacao</button>
                <button type="button" id="export-image" class="secondary">Exportar imagem</button>
                <?php if (!$isTemplateMode): ?>
                    <form method="post" action="<?= base_url('/tactical-boards/' . $board['id'] . '/duplicate') ?>" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="secondary">Duplicar prancheta</button>
                    </form>
                <?php endif; ?>
                <form method="post" action="<?= $isTemplateMode ? base_url('/tactical-boards/templates/' . (int) ($templateId ?? 0) . '/save') : base_url('/tactical-boards/' . $board['id'] . '/save') ?>" id="save-form" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="state_json" id="state_json">
                    <button type="submit"><?= $isTemplateMode ? 'Salvar modelo' : 'Salvar' ?></button>
                </form>
            <?php endif; ?>
            <a href="<?= $isTemplateMode ? base_url('/tactical-boards/templates') : base_url('/tactical-boards') ?>" class="button secondary">Voltar</a>
        </div>
    </div>

    <div class="tactical-board-layout">
        <div class="tactical-field-wrap">
            <div class="tactical-field-toolbar">
                <div class="tactical-field-toolbar-row">
                    <div class="form-group tactical-left-stack" style="margin:0; flex:1;">
                        <div class="tactical-left-section">
                            <label>Elementos Táticos</label>
                            <div class="tactical-quick-actions">
                                <button type="button" id="add-goal-toolbar" class="tactical-icon-btn icon-only-btn" title="Adicionar gol" aria-label="Adicionar gol" <?= !$canEdit ? 'disabled' : '' ?>><span class="btn-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#fff" d="M4 18V8h16v10h-2v-8H6v8H4zm4 0V12h8v6h-2v-4h-4v4H8z"/></svg></span></button>
                                <button type="button" id="add-player" class="tactical-icon-btn icon-only-btn" title="Adicionar jogador" aria-label="Adicionar jogador" <?= !$canEdit ? 'disabled' : '' ?>><span class="btn-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle fill="#fff" cx="12" cy="7" r="3"/><path fill="#fff" d="M7 20c0-3 2.2-5 5-5s5 2 5 5H7z"/></svg></span></button>
                                <button type="button" id="add-cone" class="tactical-icon-btn icon-only-btn" title="Adicionar cone" aria-label="Adicionar cone" <?= !$canEdit ? 'disabled' : '' ?>><span class="btn-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#fff" d="M12 4l7 14H5l7-14zm-4.2 16h8.4v2H7.8z"/></svg></span></button>
                                <button type="button" id="add-ball" class="tactical-icon-btn icon-only-btn" title="Adicionar bola" aria-label="Adicionar bola" <?= !$canEdit ? 'disabled' : '' ?>><span class="btn-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#fff" d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 2l2.8 2-1.1 3.2H10.3L9.2 6 12 4zm-5.6 4.1l2.2-.6 1.1 3.1-2.4 1.8-2.2-1.6a8.1 8.1 0 011.3-2.7zm-.4 6.9l2.1 1.5.2 3-2 .7A8 8 0 014 15zm12 5.2l-2-.7.2-3 2.1-1.5a8 8 0 01-2.3 5.2zM12 20l-2.2-1.6-.2-2.7 2.4-1.8 2.4 1.8-.2 2.7L12 20zm4.6-7.5l-2.4-1.8 1.1-3.1 2.2.6c.6.8 1 1.7 1.3 2.7l-2.2 1.6z"/></svg></span></button>
                                <button type="button" id="add-arrow" class="tactical-icon-btn icon-only-btn" title="Adicionar seta" aria-label="Adicionar seta" <?= !$canEdit ? 'disabled' : '' ?>><span class="btn-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#fff" d="M4 11h10V8l6 4-6 4v-3H4v-2z"/></svg></span></button>
                                <button type="button" id="apply-433" class="tactical-icon-btn icon-only-btn" title="2 times" aria-label="2 times" <?= !$canEdit ? 'disabled' : '' ?>><span class="btn-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle fill="#fff" cx="8" cy="7" r="2.1"/><circle fill="#fff" cx="16" cy="7" r="2.1"/><path fill="#fff" d="M4.5 16c0-2.5 1.8-4.2 3.9-4.2s3.9 1.7 3.9 4.2H4.5z"/><path fill="#fff" d="M11.7 16c0-2.5 1.8-4.2 3.9-4.2s3.9 1.7 3.9 4.2h-7.8z"/></svg></span></button>
                            </div>
                        </div>
                        <div class="tactical-left-divider"></div>
                        <div class="tactical-left-section">
                            <label>Tipo de campo</label>
                            <div class="field-type-icons" role="group" aria-label="Tipo de campo">
                                <button type="button" class="field-type-btn" data-field-bg="soccer_field_v1" title="Campo inteiro" aria-label="Campo inteiro" <?= !$canEdit ? 'disabled' : '' ?>>
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="2.5" y="4.5" width="19" height="15" rx="1.5"></rect>
                                        <line x1="12" y1="4.5" x2="12" y2="19.5"></line>
                                        <circle cx="12" cy="12" r="2.2"></circle>
                                    </svg>
                                </button>
                                <button type="button" class="field-type-btn" data-field-bg="soccer_field_half_vertical_down" title="Meio campo (gol embaixo)" aria-label="Meio campo (gol embaixo)" <?= !$canEdit ? 'disabled' : '' ?>>
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="2.5" y="4.5" width="19" height="15" rx="1.5"></rect>
                                        <line x1="2.5" y1="12" x2="21.5" y2="12"></line>
                                        <path d="M8 19.5v-3h8v3"></path>
                                    </svg>
                                </button>
                                <button type="button" class="field-type-btn" data-field-bg="soccer_field_half_vertical_up" title="Meio campo (gol em cima)" aria-label="Meio campo (gol em cima)" <?= !$canEdit ? 'disabled' : '' ?>>
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="2.5" y="4.5" width="19" height="15" rx="1.5"></rect>
                                        <line x1="2.5" y1="12" x2="21.5" y2="12"></line>
                                        <path d="M8 4.5v3h8v-3"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group tactical-step-toolbar" style="margin:0; align-self:flex-start;">
                        <label>Etapas</label>
                        <div class="tactical-step-toolbar-row">
                            <div class="tactical-actions step-actions-inline">
                                <button type="button" id="step-add" <?= !$canEdit ? 'disabled' : '' ?>>+ Etapa</button>
                                <button type="button" id="step-duplicate" <?= !$canEdit ? 'disabled' : '' ?>>Duplicar</button>
                                <button type="button" id="step-delete" <?= !$canEdit ? 'disabled' : '' ?>>Excluir</button>
                                <button type="button" id="step-prev">Prev</button>
                                <button type="button" id="step-next">Próximo</button>
                            </div>
                        </div>
                        <div id="step-timeline" class="frame-timeline"></div>
                    </div>
                </div>
            </div>
            <div id="tactical-field" class="tactical-field" aria-label="Campo tático"></div>
            <div class="viewer-controls">
                <div class="viewer-nav">
                    <button type="button" id="viewer-prev" class="viewer-nav-btn" aria-label="Etapa anterior">&larr;</button>
                    <button type="button" id="viewer-play-toggle" class="viewer-play-btn">Play</button>
                    <button type="button" id="viewer-next" class="viewer-nav-btn" aria-label="Proxima etapa">&rarr;</button>
                </div>
            </div>
        </div></div>
</div>

<div id="item-modal" class="tactical-item-modal" aria-hidden="true">
    <div class="tactical-item-modal-backdrop" data-close-modal="1"></div>
    <div class="tactical-item-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="item-modal-title">
        <div class="tactical-item-modal-header">
            <h3 id="item-modal-title">Item selecionado</h3>
            <button type="button" id="item-modal-close" class="secondary">Fechar</button>
        </div>
        <div class="tactical-item-modal-body">
            <div class="form-group">
                <label for="m-prop-type">Tipo</label>
                <input id="m-prop-type" type="text" readonly>
            </div>
            <div class="form-group" id="m-group-prop-number">
                <label for="m-prop-number">Numero (jogador)</label>
                <input id="m-prop-number" type="number" min="0" max="99" <?= !$canEdit ? 'disabled' : '' ?>>
            </div>
            <div class="form-group">
                <label for="m-prop-label">Label</label>
                <input id="m-prop-label" type="text" <?= !$canEdit ? 'disabled' : '' ?>>
            </div>
            <div class="form-group" id="m-group-prop-color">
                <label for="m-prop-color">Cor</label>
                <select id="m-prop-color" <?= !$canEdit ? 'disabled' : '' ?>>
                    <option value="wine">Vinho</option>
                    <option value="white">Branco</option>
                </select>
            </div>
            <div class="form-group" id="m-group-prop-goal-direction">
                <label for="m-prop-goal-direction">Direcao do gol</label>
                <select id="m-prop-goal-direction" <?= !$canEdit ? 'disabled' : '' ?>>
                    <option value="0">Baixo</option>
                    <option value="90">Esquerda</option>
                    <option value="180">Cima</option>
                    <option value="270">Direita</option>
                </select>
            </div>
        </div>
        <div class="tactical-item-modal-footer">
            <button type="button" id="m-save-item" <?= !$canEdit ? 'disabled' : '' ?>>Salvar alteração</button>
            <button type="button" id="m-remove-selected" <?= !$canEdit ? 'disabled' : '' ?>>Remover</button>
            <button type="button" id="m-reset-board" <?= !$canEdit ? 'disabled' : '' ?>>Resetar</button>
        </div>
    </div>
</div>

<script>
(() => {
    const canEdit = <?= $canEdit ? 'true' : 'false' ?>;
    const PLAYER_SIZE = 34;
    const CONE_SIZE = 20;
    const BALL_SIZE = 28;
    const ARROW_SIZE = 34;
    const GOAL_WIDTH = 44;
    const GOAL_HEIGHT = 24;
    const coneIconUrl = "<?= base_url('assets/img/cone-icon.svg') ?>";
    const fieldEl = document.getElementById('tactical-field');
    const itemModal = document.getElementById('item-modal');
    const itemModalClose = document.getElementById('item-modal-close');
    const mPropType = document.getElementById('m-prop-type');
    const mPropNumber = document.getElementById('m-prop-number');
    const mPropLabel = document.getElementById('m-prop-label');
    const mPropColor = document.getElementById('m-prop-color');
    const mPropGoalDirection = document.getElementById('m-prop-goal-direction');
    const mGroupPropNumber = document.getElementById('m-group-prop-number');
    const mGroupPropColor = document.getElementById('m-group-prop-color');
    const mGroupPropGoalDirection = document.getElementById('m-group-prop-goal-direction');
    const fieldTypeButtons = Array.from(document.querySelectorAll('.field-type-btn'));
    const stateInput = document.getElementById('state_json');
    const stepCounter = document.getElementById('step-counter');
    const stepTimeline = document.getElementById('step-timeline');

    const initialState = <?= json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const state = JSON.parse(JSON.stringify(initialState));
    state.items = Array.isArray(state.items) ? state.items : [];
    state.meta = state.meta || {};
    state.field = state.field || {background: 'soccer_field_v1', aspectRatio: 1.6};

    let selectedId = null;
    let dragging = {id: null, pointerId: null, offsetX: 0, offsetY: 0};
    let pointerState = {id: null, pointerId: null, startX: 0, startY: 0, moved: false};
    let drawArrowMode = false;
    let drawingArrow = null;
    let steps = [];
    let currentStepIndex = 0;
    let isPlaying = false;
    let playTimer = null;

    const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
    const uuid = () => 'i_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
    const byId = (id) => state.items.find(i => i.id === id) || null;

    function getFieldConfig(background) {
        const normalized = background || 'soccer_field_v1';
        const map = {
            soccer_field_v1: {file: 'field-soccer.svg', aspectRatio: 1.6},
            soccer_field_half_vertical_down: {file: 'field-soccer-half-vertical-down.svg', aspectRatio: 1.6},
            soccer_field_half_vertical_up: {file: 'field-soccer-half-vertical-up.svg', aspectRatio: 1.6},
        };
        return map[normalized] || map.soccer_field_v1;
    }

    function applyFieldBackground() {
        const config = getFieldConfig(state.field.background);
        fieldEl.style.backgroundImage = `url("<?= base_url('assets/img') ?>/${config.file}")`;
        fieldEl.style.aspectRatio = String(config.aspectRatio);
        fieldTypeButtons.forEach((button) => {
            const isActive = button.dataset.fieldBg === state.field.background;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function openItemModal() {
        if (!itemModal || !selectedId) return;
        itemModal.classList.add('open');
        itemModal.setAttribute('aria-hidden', 'false');
    }

    function closeItemModal() {
        if (!itemModal) return;
        itemModal.classList.remove('open');
        itemModal.setAttribute('aria-hidden', 'true');
    }

    function itemText(item) {
        if (item.type === 'player') return `Jogador #${item.number || ''}`;
        if (item.type === 'cone') return 'Cone';
        if (item.type === 'ball') return 'Bola';
        if (item.type === 'arrow') return 'Seta';
        if (item.type === 'goal') return 'Gol';
        return item.type;
    }

    function getStepSnapshot() {
        return {
            items: JSON.parse(JSON.stringify(state.items || [])),
        };
    }

    function applyStep(stepIndex, withTransition = false) {
        if (!steps[stepIndex]) return;
        currentStepIndex = stepIndex;
        const step = steps[stepIndex];
        state.items = JSON.parse(JSON.stringify(step.items || []));
        selectedId = state.items.length ? state.items[0].id : null;
        fieldEl.classList.toggle('playback-transition', withTransition);
        renderAll();
        renderSteps();
    }

    function captureCurrentStep() {
        if (!steps.length) return;
        steps[currentStepIndex] = getStepSnapshot();
    }

    function normalizeSteps() {
        const raw = state.meta.steps;
        if (Array.isArray(raw) && raw.length) {
            return raw.map((step) => ({
                items: Array.isArray(step.items) ? step.items : [],
            }));
        }
        return [getStepSnapshot()];
    }

    function renderSteps() {
        if (stepCounter) {
            stepCounter.textContent = `Etapa atual: ${currentStepIndex + 1}/${steps.length}`;
        }
        stepTimeline.innerHTML = '';
        steps.forEach((_, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'frame-chip' + (idx === currentStepIndex ? ' active' : '');
            btn.textContent = `Etapa ${idx + 1}`;
            btn.addEventListener('click', () => {
                captureCurrentStep();
                applyStep(idx);
            });
            stepTimeline.appendChild(btn);
        });
    }

    function updatePlayButton() {
        const btn = document.getElementById('viewer-play-toggle');
        if (!btn) return;
        btn.textContent = isPlaying ? 'Pause' : 'Play';
    }

    function stopPlayback() {
        isPlaying = false;
        if (playTimer) {
            clearTimeout(playTimer);
            playTimer = null;
        }
        fieldEl.classList.remove('playback-transition');
        updatePlayButton();
    }

    function playTick() {
        if (!isPlaying) return;
        const next = (currentStepIndex + 1) % steps.length;
        applyStep(next, true);
        playTimer = setTimeout(playTick, 1200);
    }

    function togglePlayback() {
        if (!steps.length) return;
        if (isPlaying) {
            stopPlayback();
            return;
        }
        // Persist current step edits before starting playback.
        captureCurrentStep();
        isPlaying = true;
        updatePlayButton();
        playTick();
    }

    function goPrevStep() {
        stopPlayback();
        captureCurrentStep();
        const prev = currentStepIndex - 1 < 0 ? steps.length - 1 : currentStepIndex - 1;
        applyStep(prev);
    }

    function goNextStep() {
        stopPlayback();
        captureCurrentStep();
        const next = (currentStepIndex + 1) % steps.length;
        applyStep(next);
    }

    function colorClass(item) {
        return item.color === 'white' ? 'piece-white' : 'piece-wine';
    }

    function applyPieceVisual(el, item) {
        if (item.type === 'player') item.size = PLAYER_SIZE;
        else if (item.type === 'cone') item.size = CONE_SIZE;
        else if (item.type === 'ball') item.size = BALL_SIZE;
        else if (item.type === 'arrow') item.size = ARROW_SIZE;
        else if (item.type === 'goal') item.size = GOAL_WIDTH;

        el.className = `tactical-piece piece-${item.type} ${colorClass(item)}` + (item.id === selectedId ? ' selected' : '');
        el.dataset.id = item.id;
        el.style.left = `${item.x}%`;
        el.style.top = `${item.y}%`;

        if (item.type === 'arrow') {
            const length = Number(item.length || 60);
            el.style.width = `${length}px`;
            el.style.height = '16px';
            el.style.transform = `rotate(${item.angle || 0}deg)`;
            el.style.transformOrigin = '0 50%';
        } else if (item.type === 'goal') {
            el.style.width = `${GOAL_WIDTH}px`;
            el.style.height = `${GOAL_HEIGHT}px`;
            el.style.transform = `translate(-50%, -50%) rotate(${Number(item.rotation || 0)}deg)`;
            el.style.transformOrigin = '50% 50%';
        } else {
            el.style.width = `${item.size}px`;
            el.style.height = `${item.size}px`;
            el.style.transform = 'translate(-50%, -50%)';
            el.style.transformOrigin = '50% 50%';
        }

        if (item.type === 'player') el.textContent = item.number ? String(item.number) : '';
        else if (item.type === 'cone') el.innerHTML = `<img src="${coneIconUrl}" alt="Cone">`;
        else if (item.type === 'ball') el.innerHTML = '⚽';
        else if (item.type === 'arrow') el.innerHTML = '<span class="arrow-shaft"></span><span class="arrow-head"></span>';
        else if (item.type === 'goal') el.innerHTML = '<span class="goal-net"></span>';
    }

    function pieceElement(item) {
        const el = document.createElement('div');
        applyPieceVisual(el, item);

        el.addEventListener('pointerdown', (event) => {
            const itemId = el.dataset.id;
            const current = byId(itemId);
            if (!current) return;

            selectItem(itemId);
            if (!canEdit) return;
            if (drawArrowMode) return;

            dragging.id = itemId;
            dragging.pointerId = event.pointerId;
            const rect = fieldEl.getBoundingClientRect();
            const px = (current.x / 100) * rect.width;
            const py = (current.y / 100) * rect.height;
            dragging.offsetX = event.clientX - (rect.left + px);
            dragging.offsetY = event.clientY - (rect.top + py);
            pointerState.id = itemId;
            pointerState.pointerId = event.pointerId;
            pointerState.startX = event.clientX;
            pointerState.startY = event.clientY;
            pointerState.moved = false;
            el.setPointerCapture(event.pointerId);
        });
        return el;
    }

    function renderField() {
        const existing = new Map();
        Array.from(fieldEl.querySelectorAll('.tactical-piece')).forEach((el) => existing.set(el.dataset.id, el));

        state.items.forEach((item) => {
            let el = existing.get(item.id);
            if (!el) {
                el = pieceElement(item);
                fieldEl.appendChild(el);
            } else {
                applyPieceVisual(el, item);
            }
            existing.delete(item.id);
        });

        existing.forEach((el) => el.remove());
    }

    function renderProperties() {
        const item = byId(selectedId);
        if (!item) {
            mPropType.value = '';
            mPropNumber.value = '';
            mPropLabel.value = '';
            mPropColor.value = 'wine';
            mPropGoalDirection.value = '0';
            mPropGoalDirection.disabled = true;
            mGroupPropNumber.style.display = '';
            mGroupPropColor.style.display = '';
            mGroupPropGoalDirection.style.display = 'none';
            return;
        }
        mPropType.value = item.type;
        mPropNumber.value = item.type === 'player' ? (item.number ?? '') : '';
        mPropLabel.value = item.label ?? '';
        mPropColor.value = item.color ?? 'wine';
        mGroupPropNumber.style.display = item.type === 'goal' ? 'none' : '';
        mGroupPropColor.style.display = item.type === 'goal' ? 'none' : '';
        mGroupPropGoalDirection.style.display = item.type === 'goal' ? '' : 'none';
        if (item.type === 'goal') {
            mPropGoalDirection.disabled = !canEdit;
            const rotation = Number(item.rotation || 0);
            const nearest = [0, 90, 180, 270].reduce((prev, cur) =>
                Math.abs(cur - rotation) < Math.abs(prev - rotation) ? cur : prev
            , 0);
            mPropGoalDirection.value = String(nearest);
        } else {
            mPropGoalDirection.value = '0';
            mPropGoalDirection.disabled = true;
        }
    }

    function renderAll() {
        applyFieldBackground();
        renderField();
        renderProperties();
        fieldEl.classList.toggle('drawing-arrow', drawArrowMode);
    }

    function selectItem(id) {
        selectedId = id;
        renderAll();
    }

    function addItem(type) {
        const size = type === 'cone' ? CONE_SIZE : (type === 'ball' ? BALL_SIZE : (type === 'arrow' ? ARROW_SIZE : PLAYER_SIZE));
        const item = {
            id: uuid(),
            type,
            x: 50,
            y: 50,
            number: type === 'player' ? 1 : null,
            label: '',
            color: 'wine',
            size: type === 'goal' ? GOAL_WIDTH : size,
            angle: type === 'arrow' ? 0 : null,
            length: type === 'arrow' ? 60 : null,
            rotation: type === 'goal' ? 0 : null,
        };
        state.items.push(item);
        selectItem(item.id);
    }

    function buildTeam433(teamName, color, side) {
        const isLeft = side === 'left';
        const xGK = isLeft ? 10 : 90;
        const xDEF = isLeft ? 22 : 78;
        const xMID = isLeft ? 38 : 62;
        const xFWD = isLeft ? 46 : 54;
        const players = [];

        const pushPlayer = (number, x, y) => {
            players.push({
                id: uuid(),
                type: 'player',
                x,
                y,
                number,
                label: teamName,
                color,
                size: PLAYER_SIZE,
                angle: null,
                length: null,
                rotation: null,
            });
        };

        pushPlayer(1, xGK, 50);
        [20, 40, 60, 80].forEach((y, idx) => pushPlayer(idx + 2, xDEF, y));
        [30, 50, 70].forEach((y, idx) => pushPlayer(idx + 6, xMID, y));
        [25, 50, 75].forEach((y, idx) => pushPlayer(idx + 9, xFWD, y));

        return players;
    }

    function applyTwoTeams433() {
        if (!canEdit) return;
        if (!confirm('Aplicar 2 times em 4-3-3 na etapa atual? Isso substitui os itens desta etapa.')) return;

        const leftTeam = buildTeam433('Time A', 'wine', 'left');
        const rightTeam = buildTeam433('Time B', 'white', 'right');
        state.items = [...leftTeam, ...rightTeam];
        selectedId = state.items.length ? state.items[0].id : null;
        if (steps.length) {
            steps[currentStepIndex] = { items: JSON.parse(JSON.stringify(state.items)) };
            renderSteps();
        }
        renderAll();
    }

    function toPercent(clientX, clientY) {
        const rect = fieldEl.getBoundingClientRect();
        return {
            x: clamp(((clientX - rect.left) / rect.width) * 100, 0, 100),
            y: clamp(((clientY - rect.top) / rect.height) * 100, 0, 100),
            rect,
        };
    }

    function removeSelected() {
        if (!selectedId) return;
        state.items = state.items.filter(item => item.id !== selectedId);
        selectedId = state.items.length ? state.items[0].id : null;
        renderAll();
    }

    function resetState() {
        state.items = Array.isArray(initialState.items) ? JSON.parse(JSON.stringify(initialState.items)) : [];
        state.meta = initialState.meta ? JSON.parse(JSON.stringify(initialState.meta)) : {};
        selectedId = state.items.length ? state.items[0].id : null;
        renderAll();
    }

    fieldEl.addEventListener('pointermove', (event) => {
        if (drawingArrow && drawingArrow.pointerId === event.pointerId) {
            const item = byId(drawingArrow.id);
            if (!item) return;
            const pt = toPercent(event.clientX, event.clientY);
            const dxPx = (pt.x - drawingArrow.startX) * (pt.rect.width / 100);
            const dyPx = (pt.y - drawingArrow.startY) * (pt.rect.height / 100);
            const length = Math.sqrt(dxPx * dxPx + dyPx * dyPx);
            const angle = Math.atan2(dyPx, dxPx) * (180 / Math.PI);
            const maxLen = Math.sqrt((pt.rect.width * pt.rect.width) + (pt.rect.height * pt.rect.height));
            item.length = Number(clamp(length, 16, maxLen).toFixed(1));
            item.angle = Number(angle.toFixed(1));
            renderAll();
            return;
        }

        if (!canEdit || !dragging.id) return;
        const item = byId(dragging.id);
        if (!item) return;
        if (pointerState.id === dragging.id && pointerState.pointerId === event.pointerId) {
            const distance = Math.hypot(event.clientX - pointerState.startX, event.clientY - pointerState.startY);
            if (distance > 4) {
                pointerState.moved = true;
            }
        }

        const rect = fieldEl.getBoundingClientRect();
        const xPx = event.clientX - rect.left - dragging.offsetX;
        const yPx = event.clientY - rect.top - dragging.offsetY;

        const x = clamp((xPx / rect.width) * 100, 0, 100);
        const y = clamp((yPx / rect.height) * 100, 0, 100);

        item.x = Number(x.toFixed(2));
        item.y = Number(y.toFixed(2));
        renderAll();
    });

    fieldEl.addEventListener('pointerup', (event) => {
        if (pointerState.id && pointerState.pointerId === event.pointerId) {
            if (!pointerState.moved) {
                selectItem(pointerState.id);
                openItemModal();
            }
            pointerState.id = null;
            pointerState.pointerId = null;
            pointerState.moved = false;
        }
        dragging.id = null;
        dragging.pointerId = null;
        if (drawingArrow) {
            drawingArrow = null;
            drawArrowMode = false;
            renderAll();
        }
    });

    fieldEl.addEventListener('pointercancel', () => {
        pointerState.id = null;
        pointerState.pointerId = null;
        pointerState.moved = false;
        dragging.id = null;
        dragging.pointerId = null;
        if (drawingArrow) {
            drawingArrow = null;
            drawArrowMode = false;
            renderAll();
        }
    });

    fieldEl.addEventListener('pointerdown', (event) => {
        if (!canEdit || !drawArrowMode) return;
        const isMouseLeft = event.pointerType !== 'mouse' || event.button === 0 || (event.buttons & 1) === 1;
        if (!isMouseLeft) return;

        const pt = toPercent(event.clientX, event.clientY);
        const arrowId = uuid();
        state.items.push({
            id: arrowId,
            type: 'arrow',
            x: Number(pt.x.toFixed(2)),
            y: Number(pt.y.toFixed(2)),
            number: null,
            label: '',
            color: 'wine',
            size: ARROW_SIZE,
            angle: 0,
            length: 16,
        });
        drawingArrow = {
            id: arrowId,
            startX: pt.x,
            startY: pt.y,
            pointerId: event.pointerId,
        };
        selectedId = arrowId;
        fieldEl.setPointerCapture(event.pointerId);
        renderAll();
    });

    mPropNumber?.addEventListener('input', () => {
        const item = byId(selectedId);
        if (!item || item.type !== 'player' || !canEdit) return;
        item.number = Number(mPropNumber.value || 0);
        renderAll();
    });
    mPropLabel?.addEventListener('input', () => {
        const item = byId(selectedId);
        if (!item || !canEdit) return;
        item.label = mPropLabel.value || '';
        renderAll();
    });
    mPropColor?.addEventListener('change', () => {
        const item = byId(selectedId);
        if (!item || !canEdit) return;
        item.color = mPropColor.value || 'wine';
        renderAll();
    });
    mPropGoalDirection?.addEventListener('change', () => {
        const item = byId(selectedId);
        if (!item || item.type !== 'goal' || !canEdit) return;
        const value = Number(mPropGoalDirection.value || 0);
        item.rotation = value;
        renderAll();
    });
    fieldTypeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (!canEdit) return;
            const nextBackground = button.dataset.fieldBg || 'soccer_field_v1';
            state.field.background = nextBackground;
            applyFieldBackground();
        });
    });

    document.getElementById('add-player')?.addEventListener('click', () => canEdit && addItem('player'));
    document.getElementById('add-cone')?.addEventListener('click', () => canEdit && addItem('cone'));
    document.getElementById('add-ball')?.addEventListener('click', () => canEdit && addItem('ball'));
    document.getElementById('add-goal-toolbar')?.addEventListener('click', () => canEdit && addItem('goal'));
    document.getElementById('add-arrow')?.addEventListener('click', () => {
        if (!canEdit) return;
        drawArrowMode = !drawArrowMode;
        renderAll();
    });
    document.getElementById('apply-433')?.addEventListener('click', applyTwoTeams433);
    document.getElementById('remove-selected')?.addEventListener('click', () => canEdit && removeSelected());
    document.getElementById('reset-board')?.addEventListener('click', () => canEdit && resetState());
    document.getElementById('m-remove-selected')?.addEventListener('click', () => {
        if (!canEdit) return;
        removeSelected();
        closeItemModal();
    });
    document.getElementById('m-reset-board')?.addEventListener('click', () => {
        if (!canEdit) return;
        resetState();
    });
    document.getElementById('m-save-item')?.addEventListener('click', () => {
        renderAll();
        closeItemModal();
    });
    itemModalClose?.addEventListener('click', closeItemModal);
    itemModal?.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        if (target.dataset.closeModal === '1') {
            closeItemModal();
        }
    });
    document.getElementById('step-add')?.addEventListener('click', () => {
        if (!canEdit) return;
        captureCurrentStep();
        const insertAt = currentStepIndex + 1;
        steps.splice(insertAt, 0, { items: [] });
        applyStep(insertAt);
    });
    document.getElementById('step-duplicate')?.addEventListener('click', () => {
        if (!canEdit) return;
        captureCurrentStep();
        const insertAt = currentStepIndex + 1;
        steps.splice(insertAt, 0, JSON.parse(JSON.stringify(steps[currentStepIndex])));
        applyStep(insertAt);
    });
    document.getElementById('step-delete')?.addEventListener('click', () => {
        if (!canEdit || steps.length <= 1) return;
        steps.splice(currentStepIndex, 1);
        const nextIndex = Math.max(0, Math.min(currentStepIndex, steps.length - 1));
        applyStep(nextIndex);
    });
    document.getElementById('step-prev')?.addEventListener('click', () => {
        goPrevStep();
    });
    document.getElementById('step-next')?.addEventListener('click', () => {
        goNextStep();
    });
    document.getElementById('viewer-prev')?.addEventListener('click', () => {
        goPrevStep();
    });
    document.getElementById('viewer-next')?.addEventListener('click', () => {
        goNextStep();
    });
    document.getElementById('viewer-play-toggle')?.addEventListener('click', togglePlayback);
    document.getElementById('presentation-toggle')?.addEventListener('click', async () => {
        if (!document.fullscreenElement) {
            await fieldEl.requestFullscreen?.();
            return;
        }
        await document.exitFullscreen?.();
    });
    document.getElementById('export-image')?.addEventListener('click', async () => {
        try {
            if (!window.html2canvas) {
                await new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            const canvas = await window.html2canvas(fieldEl, {backgroundColor: null, scale: 2});
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = `prancheta-<?= (int) $board['id'] ?>.png`;
            link.click();
        } catch (error) {
            alert('Falha ao exportar imagem.');
        }
    });

    document.getElementById('save-form')?.addEventListener('submit', (event) => {
        captureCurrentStep();
        state.meta.steps = steps;
        state.meta.current_step = currentStepIndex;
        state.items = JSON.parse(JSON.stringify(steps[currentStepIndex].items || []));
        const payload = JSON.stringify(state);
        if (!payload) {
            event.preventDefault();
            return;
        }
        stateInput.value = payload;
    });

    selectedId = state.items.length ? state.items[0].id : null;
    steps = normalizeSteps();
    if (!canEdit) {
        currentStepIndex = 0;
    } else {
        currentStepIndex = Number.isInteger(state.meta.current_step) ? Math.max(0, Math.min(state.meta.current_step, steps.length - 1)) : 0;
    }
    applyStep(currentStepIndex);
    updatePlayButton();
})();
</script>
<?= $this->endSection() ?>




