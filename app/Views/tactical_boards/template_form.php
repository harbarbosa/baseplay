<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$isEdit = ($mode ?? '') === 'edit';
$template = $template ?? [];
$action = $isEdit ? base_url('/tactical-boards/templates/' . (int) ($template['id'] ?? 0)) : base_url('/tactical-boards/templates');
$activeValue = old('is_active', $template['is_active'] ?? 1);
$isActive = (string) $activeValue === '1' || $activeValue === 1;
$selectedBoardId = (int) (old('source_board_id') ?: 0);
?>
<div class="card" style="max-width:900px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px;">
        <div>
            <h1 style="margin:0;"><?= esc($isEdit ? 'Editar modelo de prancheta' : 'Novo modelo de prancheta') ?></h1>
            <p style="color:var(--muted); margin:6px 0 0;">Defina o modelo e como ele deve iniciar a prancheta.</p>
        </div>
        <a href="<?= base_url('/tactical-boards/templates') ?>" class="button secondary">Voltar</a>
    </div>

    <form method="post" action="<?= $action ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="title">Título</label>
            <input id="title" name="title" type="text" value="<?= esc(old('title', $template['title'] ?? '')) ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea id="description" name="description" rows="3"><?= esc(old('description', $template['description'] ?? '')) ?></textarea>
        </div>

        <div class="form-group">
            <label for="field_type">Tipo de campo</label>
            <select id="field_type" name="field_type" required>
                <?php foreach (['full' => 'Campo inteiro', 'half_bottom_goal' => 'Meio campo (gol embaixo)', 'half_top_goal' => 'Meio campo (gol em cima)'] as $value => $label): ?>
                    <option value="<?= esc($value) ?>" <?= old('field_type', $template['field_type'] ?? 'full') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="tags">Tags</label>
            <input id="tags" name="tags" type="text" value="<?= esc(old('tags', $template['tags'] ?? '')) ?>" placeholder="ex: formacao,bola-parada">
        </div>

        <div class="form-group">
            <label for="source_board_id">Criar a partir de prancheta</label>
            <select id="source_board_id" name="source_board_id">
                <option value="">Não usar</option>
                <?php foreach ($boards as $board): ?>
                    <?php
                    $label = trim(($board['team_name'] ?? '') . ' - ' . ($board['category_name'] ?? '') . ' - ' . ($board['title'] ?? ''));
                    ?>
                    <option value="<?= esc($board['id']) ?>" <?= (int) old('source_board_id', $selectedBoardId) === (int) $board['id'] ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color:var(--muted); display:block; margin-top:6px;">Se selecionar uma prancheta, o JSON abaixo será substituído no salvamento.</small>
        </div>

        <div class="form-group">
            <label for="template_json">JSON do modelo</label>
            <textarea id="template_json" name="template_json" rows="14" style="font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;"><?= esc(old('template_json', $templateJson ?? '')) ?></textarea>
            <small style="color:var(--muted); display:block; margin-top:6px;">Deixe vazio para usar a prancheta selecionada acima ou o padrão em branco.</small>
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:10px;">
            <input id="is_active" name="is_active" type="checkbox" value="1" <?= $isActive ? 'checked' : '' ?>>
            <label for="is_active" style="margin:0;">Modelo ativo (visível para uso)</label>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <button type="submit"><?= $isEdit ? 'Salvar alterações' : 'Criar modelo' ?></button>
            <a href="<?= base_url('/tactical-boards/templates') ?>" class="button secondary">Cancelar</a>
        </div>
    </form>

    <?php if ($isEdit): ?>
        <form method="post" action="<?= base_url('/tactical-boards/templates/' . (int) ($template['id'] ?? 0) . '/delete') ?>" onsubmit="return confirm('Deseja remover este modelo?');" style="margin-top:12px;">
            <?= csrf_field() ?>
            <button type="submit" class="bp-btn-danger">Excluir modelo</button>
        </form>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
