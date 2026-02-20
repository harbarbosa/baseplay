<div class="toast-stack" id="toast-stack">
    <?php if (session('error')): ?>
        <div class="alert error toast-item"><?= esc(session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success')): ?>
        <div class="alert success toast-item"><?= esc(session('success')) ?></div>
    <?php endif; ?>
    <?php if (session('errors')): ?>
        <div class="alert error toast-item">
            <?php foreach (session('errors') as $error): ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script>
(() => {
    const stack = document.getElementById('toast-stack');
    if (!stack) return;
    const items = Array.from(stack.querySelectorAll('.toast-item'));
    items.forEach((item, index) => {
        setTimeout(() => item.classList.add('show'), 60 + index * 80);
        setTimeout(() => item.classList.remove('show'), 5600 + index * 80);
        setTimeout(() => item.remove(), 6200 + index * 80);
    });
})();
</script>
