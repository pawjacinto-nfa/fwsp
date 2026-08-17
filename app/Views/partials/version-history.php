<?php
$formatVersionValue = static function (mixed $value) use (&$formatVersionValue): string {
    if ($value === null || $value === '') {
        return 'Not set';
    }

    if (is_string($value)) {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $value = $decoded;
        }
    }

    if (!is_array($value)) {
        return (string) $value;
    }

    if ($value === []) {
        return 'Not set';
    }

    $isList = array_keys($value) === range(0, count($value) - 1);
    if ($isList) {
        return implode(' · ', array_map($formatVersionValue, $value));
    }

    $parts = [];
    foreach ($value as $key => $item) {
        $label = ucwords(str_replace('_', ' ', (string) $key));
        $parts[] = $label . ': ' . $formatVersionValue($item);
    }

    return implode(' · ', $parts);
};
?>
<?php if (!empty($versions ?? [])): ?>
<section class="panel table-section version-history-panel mt-4">
    <div class="panel-head"><div><p class="eyebrow">Record trail</p><h2>Version History</h2></div></div>
    <div class="version-history-list">
        <?php foreach ($versions as $version): ?>
            <?php $versionCollapseId = 'version-history-' . (int) $version['id']; ?>
            <article class="version-history-entry">
                <header class="version-history-entry-head">
                    <div class="version-history-meta">
                        <time datetime="<?= e(date('c', strtotime((string) $version['created_at']))) ?>"><?= e(date('F j, Y · g:i A', strtotime((string) $version['created_at']))) ?></time>
                        <span>Updated by <?= e($version['changed_by_name']) ?></span>
                    </div>
                    <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($versionCollapseId) ?>" aria-expanded="false" aria-controls="<?= e($versionCollapseId) ?>">View changes</button>
                </header>
                <div id="<?= e($versionCollapseId) ?>" class="collapse">
                    <div class="version-change-list">
                        <?php foreach ($version['changes'] as $field => $change): ?>
                            <article class="version-change">
                                <h3><?= e(ucwords(str_replace('_', ' ', $field))) ?></h3>
                                <div class="version-change-values">
                                    <div class="version-change-value is-before"><span>Previous</span><p><?= e($formatVersionValue($change['from'] ?? null)) ?></p></div>
                                    <span class="version-change-arrow" aria-hidden="true">→</span>
                                    <div class="version-change-value is-after"><span>Updated</span><p><?= e($formatVersionValue($change['to'] ?? null)) ?></p></div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
