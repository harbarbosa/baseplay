<?php
$items = $breadcrumbs ?? null;
if (!is_array($items) || $items === []) {
    $segments = service('uri')->getSegments();
    $items = [];
    $path = '';
    foreach ($segments as $segment) {
        if (is_numeric($segment)) {
            continue;
        }
        $path .= ($path === '' ? '' : '/') . $segment;
        $items[] = [
            'label' => ucwords(str_replace(['-', '_'], ' ', $segment)),
            'url' => base_url($path),
        ];
    }
}
?>
<?php if (!empty($items)): ?>
    <nav class="bp-breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= base_url('/') ?>">Inicio</a>
        <?php foreach ($items as $index => $item): ?>
            <span class="sep">/</span>
            <?php if ($index === count($items) - 1 || empty($item['url'])): ?>
                <span class="current"><?= esc($item['label'] ?? '-') ?></span>
            <?php else: ?>
                <a href="<?= esc($item['url']) ?>"><?= esc($item['label'] ?? '-') ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
<?php endif; ?>
