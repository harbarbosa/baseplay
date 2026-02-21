<?php
$success = session('success');
$error = session('error');
$errors = session('errors');
?>
<?php if ($success || $error || $errors): ?>
    <script>
    (() => {
        const push = (type, message) => {
            if (window.bpToast) {
                window.bpToast(type, message);
                return;
            }
            alert(message);
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
    })();
    </script>
<?php endif; ?>
