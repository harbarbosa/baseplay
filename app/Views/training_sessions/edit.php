<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:900px;">
    <h1>Editar sessão</h1>
    <form method="post" action="<?= base_url('/training-sessions/' . $session['id'] . '/update') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Equipe</label>
            <select name="team_id" id="team_id">
                <?php foreach ($teams as $team): ?>
                    <option value="<?= esc($team['id']) ?>" <?= old('team_id', $session['team_id']) == $team['id'] ? 'selected'  : ''  ?>>
                        <?= esc($team['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Categoria</label>
            <select name="category_id" id="category_id">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= esc($category['id']) ?>"
                        data-team-id="<?= esc($category['team_id'] ?? '') ?>"
                        <?= old('category_id', $session['category_id']) == $category['id'] ? 'selected'  : ''  ?>>
                        <?= esc($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Plano de treino (opcional)</label>
            <select name="training_plan_id">
                <option value="">Selecione</option>
                <?php foreach ($plans as $plan): ?>
                    <option value="<?= esc($plan['id']) ?>" <?= old('training_plan_id', $session['training_plan_id']) == $plan['id'] ? 'selected'  : ''  ?>>
                        <?= esc($plan['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="title" value="<?= esc(old('title', $session['title'])) ?>">
        </div>
        <div class="form-group">
            <label>Data</label>
            <input type="date" name="session_date" value="<?= esc(old('session_date', $session['session_date'])) ?>">
        </div>
        <div class="form-group">
            <label>Início</label>
            <input type="datetime-local" name="start_datetime" value="<?= esc(old('start_datetime', $session['start_datetime'] ? date('Y-m-d\TH:i', strtotime($session['start_datetime'])) : '')) ?>">
        </div>
        <div class="form-group">
            <label>Fim</label>
            <input type="datetime-local" name="end_datetime" value="<?= esc(old('end_datetime', $session['end_datetime'] ? date('Y-m-d\TH:i', strtotime($session['end_datetime'])) : '')) ?>">
        </div>
        <div class="form-group">
            <label>Local</label>
            <input type="text" id="training-location" name="location" autocomplete="off" value="<?= esc(old('location', $session['location'])) ?>">
        </div>
        <div class="form-group">
            <label>Observações gerais</label>
            <textarea name="general_notes" rows="4" style="padding:12px; border-radius:10px; border:1px solid var(--border);"><?= esc(old('general_notes', $session['general_notes'])) ?></textarea>
        </div>
        <div class="form-group" style="margin-top:6px;">
            <label>
                <input type="checkbox" id="travel_required" name="travel_required" value="1" <?= old('travel_required', $session['travel_required'] ?? 0) ? 'checked' : '' ?>>
                Haverá viagem para este treino?
            </label>
        </div>
        <div class="travel-fields" id="travel-fields" style="display:none;">
            <div class="form-group">
                <label>Saída (data/hora)</label>
                <input type="datetime-local" name="travel_departure_datetime" value="<?= esc(old('travel_departure_datetime', !empty($session['travel_departure_datetime']) ? date('Y-m-d\TH:i', strtotime($session['travel_departure_datetime'])) : '')) ?>">
            </div>
            <div class="form-group">
                <label>Retorno (data/hora)</label>
                <input type="datetime-local" name="travel_return_datetime" value="<?= esc(old('travel_return_datetime', !empty($session['travel_return_datetime']) ? date('Y-m-d\TH:i', strtotime($session['travel_return_datetime'])) : '')) ?>">
            </div>
            <div class="form-group">
                <label>Local de saída</label>
                <input type="text" name="travel_location" value="<?= esc(old('travel_location', $session['travel_location'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label>Observações da viagem</label>
                <textarea name="travel_notes" rows="3" style="padding:12px; border-radius:10px; border:1px solid var(--border);"><?= esc(old('travel_notes', $session['travel_notes'] ?? '')) ?></textarea>
            </div>
        </div>

        <button type="submit">Salvar</button>
        <a href="<?= base_url('/training-sessions/' . $session['id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<script>
(() => {
    const teamSelect = document.getElementById('team_id');
    const categorySelect = document.getElementById('category_id');
    if (!teamSelect || !categorySelect) return;
    const filterCategories = () => {
        const teamId = teamSelect.value;
        Array.from(categorySelect.options).forEach((opt) => {
            if (!opt.value) return;
            const optTeam = opt.getAttribute('data-team-id');
            opt.hidden = teamId && optTeam !== teamId;
        });
        if (categorySelect.selectedOptions[0].hidden) {
            categorySelect.value = '';
        }
    };
    teamSelect.addEventListener('change', filterCategories);
    filterCategories();
})();
</script>
<script>
(() => {
    const checkbox = document.getElementById('travel_required');
    const fields = document.getElementById('travel-fields');
    if (!checkbox || !fields) return;
    const toggle = () => {
        fields.style.display = checkbox.checked ? 'block' : 'none';
    };
    checkbox.addEventListener('change', toggle);
    toggle();
})();
</script>
<?php $mapsKey = getenv('GOOGLE_MAPS_API_KEY'); ?>
<?php if (!empty($mapsKey)): ?>
<script>
function initTrainingLocationAutocomplete() {
    const input = document.getElementById('training-location');
    if (!input || !window.google || !google.maps || !google.maps.places) return;

    const autocomplete = new google.maps.places.Autocomplete(input, {
        fields: ['formatted_address', 'name'],
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        const formatted = place && place.formatted_address ? place.formatted_address : '';
        const name = place && place.name ? place.name : '';
        input.value = formatted || name || input.value;
    });
}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= esc($mapsKey) ?>&libraries=places&callback=initTrainingLocationAutocomplete"></script>
<?php endif; ?>
<?= $this->endSection() ?>
