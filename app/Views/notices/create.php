<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:800px;">
    <h1>Novo aviso</h1>
    <form method="post" action="<?= base_url('/notices') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Equipe (opcional)</label>
            <select name="team_id">
                <option value="">Geral</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= esc($team['id']) ?>" <?= ($team_id ?? '') == $team['id'] ? 'selected' : '' ?>>
                        <?= esc($team['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Categoria (opcional)</label>
            <select name="category_id">
                <option value="">Todas</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= esc($category['id']) ?>" <?= old('category_id') == $category['id'] ? 'selected' : '' ?>>
                        <?= esc($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Título</label>
            <input type="text" name="title" value="<?= esc(old('title')) ?>">
        </div>

        <div class="form-group">
            <label>Mensagem</label>
            <textarea name="message" rows="6" style="padding:12px; border-radius:10px; border:1px solid var(--border);"><?= esc(old('message')) ?></textarea>
        </div>

        <div class="form-group">
            <label>Prioridade</label>
            <select name="priority">
                <option value="normal" <?= old('priority', 'normal') === 'normal' ? 'selected' : '' ?>>Normal</option>
                <option value="important" <?= old('priority') === 'important' ? 'selected' : '' ?>>Importante</option>
                <option value="urgent" <?= old('priority') === 'urgent' ? 'selected' : '' ?>>Urgente</option>
            </select>
        </div>

        <div class="form-group">
            <label>Publicar em</label>
            <input type="datetime-local" name="publish_at" value="<?= esc(old('publish_at')) ?>">
        </div>

        <div class="form-group">
            <label>Expira em</label>
            <input type="datetime-local" name="expires_at" value="<?= esc(old('expires_at')) ?>">
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="draft" <?= old('status', 'draft') === 'draft' ? 'selected' : '' ?>>Rascunho</option>
                <option value="published" <?= old('status') === 'published' ? 'selected' : '' ?>>Publicado</option>
                <option value="archived" <?= old('status') === 'archived' ? 'selected' : '' ?>>Arquivado</option>
            </select>
        </div>

        <button type="submit">Salvar</button>
        <a href="<?= base_url('/notices') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
