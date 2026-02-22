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
            <a href="<?= base_url('/athletes') ?>" class="card stat-card bp-stat-link">
                <strong>Total de atletas</strong>
                <div class="stat-value"><?= esc($data['kpis']['totalAthletes'] ?? 0) ?></div>
            </a>
            <a href="<?= base_url('/reports/attendance') ?>" class="card stat-card bp-stat-link">
                <strong>Presença média (mês)</strong>
                <div class="stat-value"><?= esc($data['kpis']['attendancePct'] ?? 0) ?>%</div>
            </a>
            <a href="<?= base_url('/documents?status=expired') ?>" class="card stat-card bp-stat-link">
                <strong>Documentos vencidos</strong>
                <div class="stat-value"><?= esc($data['kpis']['docsExpired'] ?? 0) ?></div>
            </a>
            <a href="<?= base_url('/pending-center?type=missing_required_documents') ?>" class="card stat-card bp-stat-link">
                <strong>Pendências obrigatórias</strong>
                <div class="stat-value"><?= esc($data['kpis']['missingRequiredDocuments'] ?? 0) ?></div>
            </a>
            <a href="<?= base_url('/events?status=scheduled') ?>" class="card stat-card bp-stat-link">
                <strong>Próximos eventos</strong>
                <div class="stat-value"><?= esc($data['kpis']['upcomingEventsCount'] ?? 0) ?></div>
            </a>
            <a href="<?= base_url('/reports/attendance') ?>" class="card stat-card bp-stat-link">
                <strong>Baixa presença</strong>
                <div class="stat-value"><?= esc($data['kpis']['lowAttendanceCount'] ?? 0) ?></div>
            </a>
            <a href="<?= base_url('/alerts') ?>" class="card stat-card bp-stat-link">
                <strong>Alertas pendentes</strong>
                <div class="stat-value"><?= esc($data['kpis']['systemAlertUnread'] ?? 0) ?></div>
            </a>
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
            <a href="<?= base_url('/reports/attendance') ?>" class="card stat-card bp-stat-link">
                <strong>Presença da categoria</strong>
                <div class="stat-value"><?= esc($data['kpis']['attendancePct'] ?? 0) ?>%</div>
            </a>
            <a href="<?= base_url('/events?type=TRAINING&status=scheduled') ?>" class="card stat-card bp-stat-link">
                <strong>Próximo treino</strong>
                <div class="stat-value"><?= esc(format_datetime_br($data['kpis']['nextTraining']['start_datetime'] ?? null)) ?></div>
            </a>
            <a href="<?= base_url('/events?type=MATCH&status=scheduled') ?>" class="card stat-card bp-stat-link">
                <strong>Próximo jogo</strong>
                <div class="stat-value"><?= esc(format_datetime_br($data['kpis']['nextMatch']['start_datetime'] ?? null)) ?></div>
            </a>
            <a href="<?= base_url('/documents?status=expired') ?>" class="card stat-card bp-stat-link">
                <strong>Documentos vencidos</strong>
                <div class="stat-value"><?= esc($data['kpis']['documentsPending'] ?? 0) ?></div>
            </a>
            <a href="<?= base_url('/pending-center?type=missing_required_documents') ?>" class="card stat-card bp-stat-link">
                <strong>Pendências obrigatórias</strong>
                <div class="stat-value"><?= esc($data['kpis']['missingRequiredDocuments'] ?? 0) ?></div>
            </a>
            <a href="<?= base_url('/reports/attendance') ?>" class="card stat-card bp-stat-link">
                <strong>Atletas com baixa presença</strong>
                <div class="stat-value"><?= esc($data['kpis']['lowAttendanceCount'] ?? 0) ?></div>
            </a>
            <a href="<?= base_url('/alerts') ?>" class="card stat-card bp-stat-link">
                <strong>Alertas pendentes</strong>
                <div class="stat-value"><?= esc($data['kpis']['systemAlertUnread'] ?? 0) ?></div>
            </a>
        </div>

    <?php elseif ($role === 'auxiliar'): ?>
        <div class="stat-grid">
            <a href="<?= base_url('/events?status=scheduled') ?>" class="card stat-card bp-stat-link">
                <strong>Eventos próximos</strong>
                <div class="stat-value"><?= count($data['kpis']['eventsWindow'] ?? []) ?></div>
            </a>
            <a href="<?= base_url('/alerts') ?>" class="card stat-card bp-stat-link">
                <strong>Alertas pendentes</strong>
                <div class="stat-value"><?= esc($data['kpis']['systemAlertUnread'] ?? 0) ?></div>
            </a>
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
            <a href="<?= base_url('/events?status=scheduled') ?>" class="card stat-card bp-stat-link">
                <strong>Próximos eventos</strong>
                <div class="stat-value"><?= count($data['kpis']['upcomingEvents'] ?? []) ?></div>
            </a>
            <a href="<?= base_url('/notices') ?>" class="card stat-card bp-stat-link">
                <strong>Avisos recentes</strong>
                <div class="stat-value"><?= count($data['kpis']['notices'] ?? []) ?></div>
            </a>
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
    const rootStyles = getComputedStyle(document.documentElement);
    const primary = rootStyles.getPropertyValue('--primary').trim() || '#1E3A8A';
    const primaryDark = rootStyles.getPropertyValue('--primary-dark').trim() || '#172554';
    const accent = rootStyles.getPropertyValue('--accent').trim() || '#2563EB';
    const muted = rootStyles.getPropertyValue('--muted').trim() || '#64748B';

    const weekly = <?= json_encode($data['charts']['weeklyAttendance'] ?? []) ?>;
    const weekLabels = weekly.map((r) => r.week);
    const weekValues = weekly.map((r) => r.total > 0 ? Math.round((r.present_count / r.total) * 100) : 0);

    new Chart(document.getElementById('attendanceChart'), {
        type: 'line',
        data: {
            labels: weekLabels,
            datasets: [{
                label: 'Presença %',
                data: weekValues,
                borderColor: accent,
                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                pointBackgroundColor: primary,
                tension: 0.35,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { ticks: { color: muted }, grid: { color: 'rgba(226, 232, 240, 0.6)' } },
                y: { ticks: { color: muted }, grid: { color: 'rgba(226, 232, 240, 0.6)' }, beginAtZero: true, max: 100 }
            },
            plugins: {
                legend: { labels: { color: muted } }
            }
        }
    });

    const trainings = <?= json_encode($data['charts']['trainingsByCategory'] ?? []) ?>;
    new Chart(document.getElementById('trainingChart'), {
        type: 'bar',
        data: {
            labels: trainings.map((r) => r.category_name),
            datasets: [{
                label: 'Treinos',
                data: trainings.map((r) => r.total),
                backgroundColor: primary,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { ticks: { color: muted }, grid: { display: false } },
                y: { ticks: { color: muted }, grid: { color: 'rgba(226, 232, 240, 0.6)' }, beginAtZero: true }
            },
            plugins: {
                legend: { labels: { color: muted } }
            }
        }
    });

    const results = <?= json_encode($data['charts']['matchResults'] ?? []) ?>;
    new Chart(document.getElementById('matchChart'), {
        type: 'doughnut',
        data: {
            labels: ['Vitórias', 'Empates', 'Derrotas'],
            datasets: [{
                data: [results.wins || 0, results.draws || 0, results.losses || 0],
                backgroundColor: ['#22c55e', accent, '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: muted } }
            }
        }
    });
</script>
<?php endif; ?>
<?= $this->endSection() ?>
