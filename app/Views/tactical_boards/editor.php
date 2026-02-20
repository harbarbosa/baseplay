<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$decoded = json_decode($currentState['state_json'] ?? '', true);
if (!is_array($decoded)) {
    $decoded = [
        'field' => ['background' => 'soccer_field_v1', 'aspectRatio' => 1.6],
        'items' => [],
        'meta' => ['notes' => '', 'formation' => ''],
    ];
}
?>
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
                <button type="button" id="presentation-toggle" class="secondary">Apresentação</button>
                <button type="button" id="export-image" class="secondary">Exportar imagem</button>
                <form method="post" action="<?= base_url('/tactical-boards/' . $board['id'] . '/duplicate') ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="secondary">Duplicar prancheta</button>
                </form>
            <?php endif; ?>
            <a href="<?= base_url('/tactical-boards') ?>" class="button secondary">Voltar</a>
        </div>
    </div>

    <div class="tactical-board-layout">
        <div class="tactical-field-wrap">
            <div class="tactical-field-toolbar">
                <div class="tactical-field-toolbar-row">
                    <div class="form-group" style="margin:0; flex:1;">
                        <label for="field-background">Tipo de campo</label>
                        <select id="field-background" <?= !$canEdit ? 'disabled' : '' ?>>
                            <option value="soccer_field_v1">Campo inteiro</option>
                            <option value="soccer_field_half_vertical_down">Meio campo (gol embaixo)</option>
                            <option value="soccer_field_half_vertical_up">Meio campo (gol em cima)</option>
                        </select>
                    </div>
                    <button type="button" id="add-goal-toolbar" class="secondary" style="align-self:flex-end;" <?= !$canEdit ? 'disabled' : '' ?>>+ Adicionar gol</button>
                </div>
            </div>
            <div id="tactical-field" class="tactical-field" aria-label="Campo tatico"></div>
            <div class="viewer-controls">
                <div class="viewer-nav">
                    <button type="button" id="viewer-prev" class="viewer-nav-btn" aria-label="Etapa anterior">&larr;</button>
                    <button type="button" id="viewer-play-toggle" class="viewer-play-btn">Play</button>
                    <button type="button" id="viewer-next" class="viewer-nav-btn" aria-label="Proxima etapa">&rarr;</button>
                </div>
            </div>
        </div>

        <aside class="tactical-sidebar"<?= !$canEdit ? ' style="display:none;"' : '' ?>>
            <div class="tactical-panel">
                <h3>Itens</h3>
                <div class="tactical-actions">
                    <button type="button" id="add-player" <?= !$canEdit ? 'disabled' : '' ?>>+ Jogador</button>
                    <button type="button" id="add-cone" <?= !$canEdit ? 'disabled' : '' ?>>+ Cone</button>
                    <button type="button" id="add-ball" <?= !$canEdit ? 'disabled' : '' ?>>+ Bola</button>
                    <button type="button" id="add-arrow" <?= !$canEdit ? 'disabled' : '' ?>>+ Seta</button>
                </div>

                <div class="form-group" style="margin-top:12px; display:none;">
                    <label for="items-list">Selecionado</label>
                    <select id="items-list" size="6"></select>
                </div>

                <div class="form-group">
                    <label for="prop-type">Tipo</label>
                    <input id="prop-type" type="text" readonly>
                </div>
                <div class="form-group" id="group-prop-number">
                    <label for="prop-number">Numero (jogador)</label>
                    <input id="prop-number" type="number" min="0" max="99" <?= !$canEdit ? 'disabled' : '' ?>>
                </div>
                <div class="form-group">
                    <label for="prop-label">Label</label>
                    <input id="prop-label" type="text" <?= !$canEdit ? 'disabled' : '' ?>>
                </div>
                <div class="form-group" id="group-prop-color">
                    <label for="prop-color">Cor</label>
                    <select id="prop-color" <?= !$canEdit ? 'disabled' : '' ?>>
                        <option value="wine">Vinho</option>
                        <option value="white">Branco</option>
                    </select>
                </div>
                <div class="form-group">
                <div class="form-group" id="group-prop-goal-direction">
                    <label for="prop-goal-direction">Direcao do gol</label>
                    <select id="prop-goal-direction" <?= !$canEdit ? 'disabled' : '' ?>>
                        <option value="0">Baixo</option>
                        <option value="90">Esquerda</option>
                        <option value="180">Cima</option>
                        <option value="270">Direita</option>
                    </select>
                </div>

                <div class="tactical-actions">
                    <button type="button" id="remove-selected" <?= !$canEdit ? 'disabled' : '' ?>>Remover</button>
                    <button type="button" id="reset-board" class="secondary" <?= !$canEdit ? 'disabled' : '' ?>>Resetar</button>
                </div>
            </div>

            <div class="tactical-panel">
                <h3>Metadados</h3>
                <div class="form-group">
                    <label for="meta-formation">Formacao</label>
                    <input id="meta-formation" type="text" placeholder="Ex: 4-3-3" <?= !$canEdit ? 'disabled' : '' ?>>
                </div>
                <div class="form-group">
                    <label for="meta-notes">Notas</label>
                    <textarea id="meta-notes" rows="4" <?= !$canEdit ? 'disabled' : '' ?>></textarea>
                </div>

                <?php if ($canEdit): ?>
                    <form method="post" action="<?= base_url('/tactical-boards/' . $board['id'] . '/save') ?>" id="save-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="state_json" id="state_json">
                        <button type="submit">Salvar</button>
                    </form>
                <?php else: ?>
                    <p style="color:var(--muted); margin-top:8px;">Somente leitura.</p>
                <?php endif; ?>
            </div>

            <div class="tactical-panel">
                <h3>Etapas</h3>
                <div style="margin-bottom:8px;">
                    <strong id="step-counter">Etapa atual: 1/1</strong>
                </div>
                <div id="step-timeline" class="frame-timeline"></div>
                <div class="tactical-actions step-actions">
                    <button type="button" id="step-add" <?= !$canEdit ? 'disabled' : '' ?>>+ Etapa</button>
                    <button type="button" id="step-duplicate" <?= !$canEdit ? 'disabled' : '' ?>>Duplicar etapa</button>
                    <button type="button" id="step-delete" <?= !$canEdit ? 'disabled' : '' ?>>Excluir etapa</button>
                </div>
                <div class="tactical-actions step-nav">
                    <button type="button" id="step-prev">Prev</button>
                    <button type="button" id="step-next">Next</button>
                </div>
            </div>
        </aside>
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
    const listEl = document.getElementById('items-list');
    const propType = document.getElementById('prop-type');
    const propNumber = document.getElementById('prop-number');
    const propLabel = document.getElementById('prop-label');
    const propColor = document.getElementById('prop-color');
    const propGoalDirection = document.getElementById('prop-goal-direction');
    const groupPropNumber = document.getElementById('group-prop-number');
    const groupPropColor = document.getElementById('group-prop-color');
    const groupPropGoalDirection = document.getElementById('group-prop-goal-direction');
    const fieldBackground = document.getElementById('field-background');
    const metaFormation = document.getElementById('meta-formation');
    const metaNotes = document.getElementById('meta-notes');
    const stateInput = document.getElementById('state_json');
    const stepCounter = document.getElementById('step-counter');
    const stepTimeline = document.getElementById('step-timeline');

    const initialState = <?= json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const state = JSON.parse(JSON.stringify(initialState));
    state.items = Array.isArray(state.items) ? state.items : [];
    state.meta = state.meta || {notes: '', formation: ''};
    state.field = state.field || {background: 'soccer_field_v1', aspectRatio: 1.6};

    let selectedId = null;
    let dragging = {id: null, pointerId: null, offsetX: 0, offsetY: 0};
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
    }

    function itemText(item) {
        if (item.type === 'player') return `Jogador #${item.number || ''}`;
        if (item.type === 'cone') return 'Cone';
        if (item.type === 'ball') return 'Bola';
        if (item.type === 'arrow') return 'Seta';
        if (item.type === 'goal') return 'Gol';
        return item.type;
    }

    function ensureMeta() {
        state.meta.formation = metaFormation.value || '';
        state.meta.notes = metaNotes.value || '';
    }

    function getStepSnapshot() {
        ensureMeta();
        return {
            items: JSON.parse(JSON.stringify(state.items || [])),
            formation: state.meta.formation || '',
            notes: state.meta.notes || '',
        };
    }

    function applyStep(stepIndex, withTransition = false) {
        if (!steps[stepIndex]) return;
        currentStepIndex = stepIndex;
        const step = steps[stepIndex];
        state.items = JSON.parse(JSON.stringify(step.items || []));
        state.meta.formation = step.formation || '';
        state.meta.notes = step.notes || '';
        metaFormation.value = state.meta.formation;
        metaNotes.value = state.meta.notes;
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
                formation: step.formation || '',
                notes: step.notes || '',
            }));
        }
        return [getStepSnapshot()];
    }

    function renderSteps() {
        stepCounter.textContent = `Etapa atual: ${currentStepIndex + 1}/${steps.length}`;
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
            el.setPointerCapture(event.pointerId);
        });

        el.addEventListener('click', () => {
            const id = el.dataset.id;
            const item = byId(id);
            if (!item) return;
            selectItem(id);
            if (canEdit && item.type === 'goal') {
                item.rotation = (Number(item.rotation || 0) + 90) % 360;
                renderAll();
            }
        });
        return el;
    }

    function renderList() {
        const prev = listEl.value;
        listEl.innerHTML = '';
        state.items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${itemText(item)} (${Math.round(item.x)}%, ${Math.round(item.y)}%)`;
            if (item.id === selectedId || item.id === prev) option.selected = true;
            listEl.appendChild(option);
        });
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
            propType.value = '';
            propNumber.value = '';
            propLabel.value = '';
            propColor.value = 'wine';
            propGoalDirection.value = '0';
            propGoalDirection.disabled = true;
            groupPropNumber.style.display = '';
            groupPropColor.style.display = '';
            groupPropGoalDirection.style.display = 'none';
            return;
        }
        propType.value = item.type;
        propNumber.value = item.type === 'player' ? (item.number ?? '') : '';
        propLabel.value = item.label ?? '';
        propColor.value = item.color ?? 'wine';
        groupPropNumber.style.display = item.type === 'goal' ? 'none' : '';
        groupPropColor.style.display = item.type === 'goal' ? 'none' : '';
        groupPropGoalDirection.style.display = item.type === 'goal' ? '' : 'none';
        if (item.type === 'goal') {
            propGoalDirection.disabled = !canEdit;
            const rotation = Number(item.rotation || 0);
            const nearest = [0, 90, 180, 270].reduce((prev, cur) =>
                Math.abs(cur - rotation) < Math.abs(prev - rotation) ? cur : prev
            , 0);
            propGoalDirection.value = String(nearest);
        } else {
            propGoalDirection.value = '0';
            propGoalDirection.disabled = true;
        }
    }

    function renderAll() {
        applyFieldBackground();
        renderField();
        renderList();
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
        state.meta = initialState.meta ? JSON.parse(JSON.stringify(initialState.meta)) : {formation: '', notes: ''};
        metaFormation.value = state.meta.formation || '';
        metaNotes.value = state.meta.notes || '';
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

        const rect = fieldEl.getBoundingClientRect();
        const xPx = event.clientX - rect.left - dragging.offsetX;
        const yPx = event.clientY - rect.top - dragging.offsetY;

        const x = clamp((xPx / rect.width) * 100, 0, 100);
        const y = clamp((yPx / rect.height) * 100, 0, 100);

        item.x = Number(x.toFixed(2));
        item.y = Number(y.toFixed(2));
        renderAll();
    });

    fieldEl.addEventListener('pointerup', () => {
        dragging.id = null;
        dragging.pointerId = null;
        if (drawingArrow) {
            drawingArrow = null;
            drawArrowMode = false;
            renderAll();
        }
    });

    fieldEl.addEventListener('pointercancel', () => {
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

    listEl.addEventListener('change', () => listEl.value && selectItem(listEl.value));

    propNumber.addEventListener('input', () => {
        const item = byId(selectedId);
        if (!item || item.type !== 'player' || !canEdit) return;
        item.number = Number(propNumber.value || 0);
        renderAll();
    });

    propLabel.addEventListener('input', () => {
        const item = byId(selectedId);
        if (!item || !canEdit) return;
        item.label = propLabel.value || '';
        renderAll();
    });

    propColor.addEventListener('change', () => {
        const item = byId(selectedId);
        if (!item || !canEdit) return;
        item.color = propColor.value || 'wine';
        renderAll();
    });
    propGoalDirection.addEventListener('change', () => {
        const item = byId(selectedId);
        if (!item || item.type !== 'goal' || !canEdit) return;
        const value = Number(propGoalDirection.value || 0);
        item.rotation = value;
        renderAll();
    });
    fieldBackground?.addEventListener('change', () => {
        if (!canEdit) return;
        state.field.background = fieldBackground.value || 'soccer_field_v1';
        applyFieldBackground();
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
    document.getElementById('remove-selected')?.addEventListener('click', () => canEdit && removeSelected());
    document.getElementById('reset-board')?.addEventListener('click', () => canEdit && resetState());
    document.getElementById('step-add')?.addEventListener('click', () => {
        if (!canEdit) return;
        captureCurrentStep();
        const insertAt = currentStepIndex + 1;
        steps.splice(insertAt, 0, getStepSnapshot());
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
        ensureMeta();
        captureCurrentStep();
        state.meta.steps = steps;
        state.meta.current_step = currentStepIndex;
        state.items = JSON.parse(JSON.stringify(steps[currentStepIndex].items || []));
        state.meta.formation = steps[currentStepIndex].formation || '';
        state.meta.notes = steps[currentStepIndex].notes || '';
        const payload = JSON.stringify(state);
        if (!payload) {
            event.preventDefault();
            return;
        }
        stateInput.value = payload;
    });

    metaFormation.value = state.meta.formation || '';
    metaNotes.value = state.meta.notes || '';
    fieldBackground.value = state.field.background || 'soccer_field_v1';
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
