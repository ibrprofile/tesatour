<?php
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');
$warningMessage = Session::getFlash('warning');
$infoMessage = Session::getFlash('info');
?>

<?php if ($successMessage || $errorMessage || $warningMessage || $infoMessage): ?>
<div id="flashMessages" style="display: none;">
    <?php if ($successMessage): ?>
        <span data-flash="success"><?= e($successMessage) ?></span>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <span data-flash="error"><?= e($errorMessage) ?></span>
    <?php endif; ?>
    <?php if ($warningMessage): ?>
        <span data-flash="warning"><?= e($warningMessage) ?></span>
    <?php endif; ?>
    <?php if ($infoMessage): ?>
        <span data-flash="info"><?= e($infoMessage) ?></span>
    <?php endif; ?>
</div>
<?php endif; ?>
