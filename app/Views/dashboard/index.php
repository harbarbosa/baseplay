<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <?php
    $roleLabels = [
        'cordenador' => 'Coordenador',
        'admin' => 'Administrador',
        'treinador' => 'Treinador',
        'auxiliar' => 'Auxiliar técnico',
        'atleta' => 'Atleta',
        'responsavel' => 'Responsável',
    ];
    $roleLabel = $roleLabels[$role] ?? $role;
    ?>

    <h1>Painel</h1>
    <p style="color:var(--muted);">Perfil: <strong><?= esc($roleLabel) ?></strong></p>

    <?php if (in_array($role, ['cordenador', 'admin'], true)): ?>
        <div class="stat-grid">
            <div class="card stat-card"><strong>Total de atletas</strong><div class="stat-value"><?= esc($data['kpis']['totalAthletes'] ?? 0) ?></div></div>
            <div class="card stat-card"><strong>Presença média (mês)</strong><div class="stat-value"><?= esc($data['kpis']['attendancePct'] ?? 0) ?>%</div></div>
            <div class="card stat-card"><strong>Documentos vencidos</strong><div class="stat-value"><?= esc($data['kpis']['docsExpired'] ?? 0) ?></div></div>
            <div class="card stat-card"><strong>Próximos eventos</strong><div class="stat-value"><?= esc($data['kpis']['upcomingEventsCount'] ?? 0) ?></div></div>
            <div class="card stat-card"><strong>Baixa presença</strong><div class="stat-value"><?= esc($data['kpis']['lowAttendanceCount'] ?? 0) ?></div></div>
            <div class="card stat-card"><strong>Alertas pendentes</strong><div class="stat-value"><?= esc($data['kpis']['systemAlertUnread'] ?? 0) ?></div></div>
        </div>

        <div class="card" style="margin-top:16px;">
            <h2>Próximos eventos</h2>
            <ul>
                <?php foreach (($data['kpis']['upcomingEvents'] ?? []) as $event): ?>
                    <li><?= esc(format_datetime_br($event['start_datetime'] ?? null)) ?> - <?= esc($event['title'] ?? '-') ?></li>
                <?php endforeach; ?>
                <?php if (empty($data['kpis']['upcomingEvents'])): ?>
                    <li>Sem eventos próximos.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="card" style="margin-top:16px;">
            <h2>Gráficos</h2>
            <canvas id="attendanceChart" height="120"></canvas>
            <canvas id="trainingChart" height="120" style="margin-top:16px;"></canvas>
            <canvas id="matchChart" height="120" style="margin-top:16px;"></canvas>
        </div>

    <?php elseif ($role === 'treinador'): ?>
        <div class="stat-grid">
            <div class="card stat-card"><strong>Presença da categoria</strong><div class="stat-value"><?= esc($data['kpis']['attendancePct'] ?? 0) ?>%</div></div>
            <div class="card stat-card"><strong>Próximo treino</strong><div class="stat-value"><?= esc(format_datetime_br($data['kpis']['nextTraining']['start_datetime'] ?? null)) ?></div></div>
            <div class="card stat-card"><strong>Próximo jogo</strong><div class="stat-value"><?= esc(format_datetime_br($data['kpis']['nextMatch']['start_datetime'] ?? null)) ?></div></div>
            <div class="card stat-card"><strong>Documentos pendentes</strong><div class="stat-value"><?= esc($data['kpis']['documentsPending'] ?? 0) ?></div></div>
            <div class="card stat-card"><strong>Atletas com baixa presença</strong><div class="stat-value"><?= esc($data['kpis']['lowAttendanceCount'] ?? 0) ?></div></div>
            <div class="card stat-card"><strong>Alertas pendentes</strong><div class="stat-value"><?= esc($data['kpis']['systemAlertUnread'] ?? 0) ?></div></div>
        </div>

    <?php elseif ($role === 'auxiliar'): ?>
        <div class="stat-grid">
            <div class="card stat-card"><strong>Eventos próximos</strong><div class="stat-value"><?= count($data['kpis']['eventsWindow'] ?? []) ?></div></div>
            <div class="card stat-card"><strong>Alertas pendentes</strong><div class="stat-value"><?= esc($data['kpis']['systemAlertUnread'] ?? 0) ?></div></div>
        </div>

        <div class="card" style="margin-top:16px;">
            <h2>Janela de eventos (72h)</h2>
            <ul>
                <?php foreach (($data['kpis']['eventsWindow'] ?? []) as $event): ?>
                    <li><?= esc(format_datetime_br($event['start_datetime'] ?? null)) ?> - <?= esc($event['title'] ?? '-') ?></li>
                <?php endforeach; ?>
                <?php if (empty($data['kpis']['eventsWindow'])): ?>
                    <li>Sem eventos programados.</li>
                <?php endif; ?>
            </ul>
        </div>

    <?php else: ?>
        <div class="stat-grid">
            <div class="card stat-card"><strong>Próximos eventos</strong><div class="stat-value"><?= count($data['kpis']['upcomingEvents'] ?? []) ?></div></div>
            <div class="card stat-card"><strong>Avisos recentes</strong><div class="stat-value"><?= count($data['kpis']['notices'] ?? []) ?></div></div>
        </div>

        <div class="card" style="margin-top:16px;">
            <h2>Próximos eventos</h2>
            <ul>
                <?php foreach (($data['kpis']['upcomingEvents'] ?? []) as $event): ?>
                    <li><?= esc(format_datetime_br($event['start_datetime'] ?? null)) ?> - <?= esc($event['title'] ?? '-') ?></li>
                <?php endforeach; ?>
                <?php if (empty($data['kpis']['upcomingEvents'])): ?>
                    <li>Sem eventos próximos.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="card" style="margin-top:16px;">
            <h2>Avisos recentes</h2>
            <ul>
                <?php foreach (($data['kpis']['notices'] ?? []) as $notice): ?>
                    <li><?= esc($notice['title'] ?? '-') ?></li>
                <?php endforeach; ?>
                <?php if (empty($data['kpis']['notices'])): ?>
                    <li>Sem avisos.</li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php if (in_array($role, ['cordenador', 'admin'], true)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const weekly = <?= json_encode($data['charts']['weeklyAttendance'] ?? []) ?>;
    const weekLabels = weekly.map((r) => r.week);
    const weekValues = weekly.map((r) => r.total > 0 ? Math.round((r.present_count / r.total) * 100) : 0);

    new Chart(document.getElementById('attendanceChart'), {
        type: 'line',
        data: { labels: weekLabels, datasets: [{ label: 'Presença %', data: weekValues, borderColor: '#7A1126' }] },
        options: { responsive: true }
    });

    const trainings = <?= json_encode($data['charts']['trainingsByCategory'] ?? []) ?>;
    new Chart(document.getElementById('trainingChart'), {
        type: 'bar',
        data: { labels: trainings.map((r) => r.category_name), datasets: [{ label: 'Treinos', data: trainings.map((r) => r.total), backgroundColor: '#5E0D1D' }] },
        options: { responsive: true }
    });

    const results = <?= json_encode($data['charts']['matchResults'] ?? []) ?>;
    new Chart(document.getElementById('matchChart'), {
        type: 'doughnut',
        data: {
            labels: ['Vitórias', 'Empates', 'Derrotas'],
            datasets: [{ data: [results.wins || 0, results.draws || 0, results.losses || 0], backgroundColor: ['#16a34a', '#f59e0b', '#dc2626'] }]
        },
        options: { responsive: true }
    });
</script>
<?php endif; ?>
<?= $this->endSection() ?>
