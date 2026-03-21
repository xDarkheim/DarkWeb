<?php
/**
 * AdminCP modules manager view.
 *
 * Variables:
 * - array<int,array{0:string,1:string}> $globalModules
 * - array<int,array{0:string,1:string}> $usercpModules
 * - string|null $selectedConfigKey
 * - string|null $selectedConfigFilePath
 */
?>
<h1 class="page-header"><i class="bi bi-grid me-2"></i>Module Manager</h1>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="acp-card">
            <div class="acp-card-header">Global Modules</div>
            <div class="p-2">
                <?php foreach ($globalModules as $m): ?>
                <a href="<?php echo htmlspecialchars(admincp_base('modules_manager&config=' . $m[1]), ENT_QUOTES, 'UTF-8'); ?>" class="acp-module-link">
                    <?php echo htmlspecialchars($m[0], ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="acp-card">
            <div class="acp-card-header">UserCP Modules</div>
            <div class="p-2">
                <?php foreach ($usercpModules as $m): ?>
                <a href="<?php echo htmlspecialchars(admincp_base('modules_manager&config=' . $m[1]), ENT_QUOTES, 'UTF-8'); ?>" class="acp-module-link">
                    <?php echo htmlspecialchars($m[0], ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php if (is_string($selectedConfigFilePath) && $selectedConfigFilePath !== ''): ?>
<div class="acp-card">
    <div class="acp-card-header">
        <i class="bi bi-sliders me-1"></i>Configuration: <?php echo htmlspecialchars((string) $selectedConfigKey, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <div class="p-3">
        <?php include $selectedConfigFilePath; ?>
    </div>
</div>
<?php endif; ?>

