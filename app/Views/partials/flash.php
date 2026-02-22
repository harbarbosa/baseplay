<?php
$success = session('success');
$error = session('error');
$errors = session('errors');
?>
<?php if ($success || $error || $errors): ?>
    <script>
    (() => {
        const enqueue = (type, message) => {
            window.__bpToastQueue = window.__bpToastQueue || [];
            window.__bpToastQueue.push({ type, message });
        };

        const push = (type, message) => {
            if (window.bpToast) {
                window.bpToast(type, message);
                return;
            }
            enqueue(type, message);
        };

        <?php if (!empty($success)): ?>
            push('success', <?= json_encode((string) $success, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            push('error', <?= json_encode((string) $error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
        <?php endif; ?>
        <?php if (is_array($errors) && $errors !== []): ?>
            <?php foreach ($errors as $item): ?>
                push('error', <?= json_encode((string) $item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
            <?php endforeach; ?>
        <?php endif; ?>
        if (window.bpToast && window.__bpToastQueue?.length) {
            const pending = window.__bpToastQueue.splice(0);
            pending.forEach((item) => window.bpToast(item.type, item.message));
        }
    })();
    </script>
<?php endif; ?>
