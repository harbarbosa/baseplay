<?php

/**
 * Pager template: BasePlay full
 * Variables available: $pager, $pagerGroup
 */
?>
<?php if ($pager->hasPreviousPage() || $pager->hasNextPage()): ?>
<nav class="bp-pagination" aria-label="Pagination">
    <ul>
        <?php if ($pager->hasPreviousPage()): ?>
            <li class="bp-page-item">
                <a class="bp-page-link" href="<?= $pager->getFirst() ?>" aria-label="Primeira">«</a>
            </li>
            <li class="bp-page-item">
                <a class="bp-page-link" href="<?= $pager->getPreviousPage() ?>" aria-label="Anterior">‹</a>
            </li>
        <?php else: ?>
            <li class="bp-page-item disabled"><span class="bp-page-link">«</span></li>
            <li class="bp-page-item disabled"><span class="bp-page-link">‹</span></li>
        <?php endif; ?>

        <?php foreach ($pager->links() as $link): ?>
            <li class="bp-page-item <?= $link['active'] ? 'active' : '' ?>">
                <?php if ($link['active']): ?>
                    <span class="bp-page-link" aria-current="page"><?= $link['title'] ?></span>
                <?php else: ?>
                    <a class="bp-page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>

        <?php if ($pager->hasNextPage()): ?>
            <li class="bp-page-item">
                <a class="bp-page-link" href="<?= $pager->getNextPage() ?>" aria-label="Próxima">›</a>
            </li>
            <li class="bp-page-item">
                <a class="bp-page-link" href="<?= $pager->getLast() ?>" aria-label="Última">»</a>
            </li>
        <?php else: ?>
            <li class="bp-page-item disabled"><span class="bp-page-link">›</span></li>
            <li class="bp-page-item disabled"><span class="bp-page-link">»</span></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
