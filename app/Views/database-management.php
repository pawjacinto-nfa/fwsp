<?php
$tableCount = count($tables ?? []);
$columnCount = count($schema['columns'] ?? []);
$rowEstimate = isset($schema['table']['TABLE_ROWS']) ? (int) $schema['table']['TABLE_ROWS'] : 0;
$relationByColumn = [];
foreach (($schema['relations'] ?? []) as $relation) {
    $relationByColumn[$relation['COLUMN_NAME']] = $relation;
}
?>
<section class="workspace-section database-management-page<?= !empty($embeddedDatabaseManagement) ? ' pt-0' : '' ?>">
    <?php if (empty($embeddedDatabaseManagement)): ?>
    <div class="section-head compact no-print">
        <div>
            <p class="eyebrow">System Admin</p>
            <h3>Database Management</h3>
            <p class="mb-0 text-muted">Inspect and print table metadata, and manage notification storage.</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="panel no-print mb-3">
        <h4 class="h5">Notification cleanup</h4>
        <p>Delete stored notifications for all users to reduce retained data. This includes read, unread, and shared notifications and their read receipts. User notification preferences are preserved.</p>
        <p><strong><?= number_format($notificationTotal ?? 0) ?></strong> notifications currently stored.</p>
        <form method="get" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="system-maintenance">
            <input type="hidden" name="tab" value="database">
            <input type="hidden" name="table" value="<?= e($selectedTable) ?>">
            <div class="col-md-4">
                <label class="form-label" for="notificationCleanupScope">Date period</label>
                <select class="form-select" id="notificationCleanupScope" name="cleanup_scope">
                    <option value="range" <?= ($cleanupScope ?? 'range') === 'range' ? 'selected' : '' ?>>Selected date range</option>
                    <option value="all" <?= ($cleanupScope ?? '') === 'all' ? 'selected' : '' ?>>All dates</option>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label" for="notificationCleanupFrom">From</label><input class="form-control" type="date" id="notificationCleanupFrom" name="cleanup_from" value="<?= e($cleanupFrom ?? '') ?>"></div>
            <div class="col-md-3"><label class="form-label" for="notificationCleanupTo">Through</label><input class="form-control" type="date" id="notificationCleanupTo" name="cleanup_to" value="<?= e($cleanupTo ?? '') ?>"></div>
            <div class="col-md-2"><button class="btn btn-outline-success" type="submit" name="cleanup_preview" value="1">Preview cleanup</button></div>
            <div class="col-12"><small class="text-muted">Date ranges use the database's notification creation dates and include both selected dates. From and Through are ignored for All dates.</small></div>
        </form>
        <?php if (!empty($cleanupError)): ?><div class="alert alert-warning mt-3 mb-0" role="alert"><?= e($cleanupError) ?></div><?php endif; ?>
        <?php if (isset($cleanupCount)): ?>
            <?php $cleanupLabel = $cleanupScope === 'all' ? 'all dates' : $cleanupFrom . ' through ' . $cleanupTo; ?>
            <div class="alert alert-warning mt-3 mb-0">
                <p><strong><?= number_format($cleanupCount) ?> notification(s)</strong> match <?= e($cleanupLabel) ?> across all users. Deletion is permanent. New matching notifications created after this preview will also be deleted.</p>
                <?php if ($cleanupCount > 0): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="notifications-cleanup">
                        <input type="hidden" name="confirm_cleanup" value="1">
                        <input type="hidden" name="cleanup_scope" value="<?= e($cleanupScope) ?>">
                        <input type="hidden" name="cleanup_from" value="<?= e($cleanupFrom) ?>">
                        <input type="hidden" name="cleanup_to" value="<?= e($cleanupTo) ?>">
                        <button class="btn btn-danger" type="submit" data-confirm-title="Delete notifications for all users" data-confirm-message="Permanently delete notifications for <?= e($cleanupLabel) ?> across ALL users, including read and unread notifications? This cannot be undone." data-confirm-accept="Delete notifications">Delete matching notifications</button>
                    </form>
                <?php else: ?><p class="mb-0">There are no notifications to delete for this period.</p><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="database-toolbar panel no-print">
        <form method="get" class="database-table-picker">
            <input type="hidden" name="page" value="<?= !empty($embeddedDatabaseManagement) ? 'system-maintenance' : 'database-management' ?>">
            <?php if (!empty($embeddedDatabaseManagement)): ?><input type="hidden" name="tab" value="database"><?php endif; ?>
            <div>
                <label class="form-label" for="databaseTable">Database table</label>
                <select class="form-select" id="databaseTable" name="table" required>
                    <option value="">Select a table...</option>
                    <?php foreach (($tables ?? []) as $table): ?>
                        <option value="<?= e($table['TABLE_NAME']) ?>" <?= $selectedTable === $table['TABLE_NAME'] ? 'selected' : '' ?>>
                            <?= e($table['TABLE_NAME']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small><?= number_format($tableCount) ?> table<?= $tableCount === 1 ? '' : 's' ?> available</small>
            </div>
            <button class="btn btn-success" type="submit">View schema</button>
            <?php if ($schema): ?>
                <button class="btn btn-outline-success" type="button" onclick="window.print()">Print A4 schema</button>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($selectedTable !== '' && !$schema): ?>
        <div class="alert alert-warning no-print">The selected table is not available.</div>
    <?php elseif (!$schema): ?>
        <div class="database-empty panel no-print">
            <span aria-hidden="true">DB</span>
            <h4>Choose a table to begin</h4>
            <p class="mb-0">Its columns, data types, keys, relationships, and example values will appear here.</p>
        </div>
    <?php else: ?>
        <article class="database-schema-sheet">
            <header class="schema-document-head">
                <div>
                    <p class="schema-agency">National Food Authority · Farmer-Seller Registry</p>
                    <h1>Database Table Schema</h1>
                    <p class="schema-table-name"><?= e($schema['table']['TABLE_NAME']) ?></p>
                </div>
                <div class="schema-generated">
                    <strong>DATA DICTIONARY</strong>
                    <span>Generated <?= e(date('F j, Y · g:i A')) ?></span>
                </div>
            </header>

            <section class="schema-summary" aria-label="Table summary">
                <div><span>Table</span><strong><?= e($schema['table']['TABLE_NAME']) ?></strong></div>
                <div><span>Columns</span><strong><?= number_format($columnCount) ?></strong></div>
                <div><span>Estimated rows</span><strong><?= number_format($rowEstimate) ?></strong></div>
                <div><span>Relationships</span><strong><?= number_format(count($schema['relations'])) ?></strong></div>
            </section>

            <?php if (!empty($schema['table']['TABLE_COMMENT'])): ?>
                <p class="schema-description"><strong>Description:</strong> <?= e($schema['table']['TABLE_COMMENT']) ?></p>
            <?php endif; ?>

            <section class="schema-section">
                <h2>Entities and field metadata</h2>
                <div class="table-responsive">
                    <table class="table schema-columns-table">
                        <thead>
                            <tr><th>#</th><th>Entity / field</th><th>Type</th><th>Null</th><th>Key</th><th>Default / attributes</th><th>Example data</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($schema['columns'] as $column): ?>
                            <?php $relation = $relationByColumn[$column['COLUMN_NAME']] ?? null; ?>
                            <tr>
                                <td><?= e($column['ORDINAL_POSITION']) ?></td>
                                <td>
                                    <strong><?= e($column['COLUMN_NAME']) ?></strong>
                                    <?php if ($relation): ?><small>References <?= e($relation['REFERENCED_TABLE_NAME']) ?>.<?= e($relation['REFERENCED_COLUMN_NAME']) ?></small><?php endif; ?>
                                    <?php if ($column['COLUMN_COMMENT'] !== ''): ?><small><?= e($column['COLUMN_COMMENT']) ?></small><?php endif; ?>
                                </td>
                                <td><code><?= e($column['COLUMN_TYPE']) ?></code></td>
                                <td><?= $column['IS_NULLABLE'] === 'YES' ? 'Yes' : 'No' ?></td>
                                <td><?= e($column['COLUMN_KEY'] !== '' ? $column['COLUMN_KEY'] : '—') ?></td>
                                <td>
                                    <span><?= $column['COLUMN_DEFAULT'] === null ? 'NULL' : e((string) $column['COLUMN_DEFAULT']) ?></span>
                                    <?php if ($column['EXTRA'] !== ''): ?><small><?= e($column['EXTRA']) ?></small><?php endif; ?>
                                </td>
                                <td class="schema-example"><?= e($column['EXAMPLE']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="schema-detail-grid">
                <section class="schema-section">
                    <h2>Indexes</h2>
                    <?php if ($schema['indexes']): ?>
                        <table class="table schema-small-table">
                            <thead><tr><th>Name</th><th>Fields</th><th>Type</th></tr></thead>
                            <tbody><?php foreach ($schema['indexes'] as $index): ?><tr><td><?= e($index['INDEX_NAME']) ?></td><td><?= e($index['COLUMNS']) ?></td><td><?= (int) $index['NON_UNIQUE'] === 0 ? 'Unique' : 'Index' ?></td></tr><?php endforeach; ?></tbody>
                        </table>
                    <?php else: ?><p>No indexes defined.</p><?php endif; ?>
                </section>
                <section class="schema-section">
                    <h2>Relationships</h2>
                    <?php if ($schema['relations']): ?>
                        <table class="table schema-small-table">
                            <thead><tr><th>Field</th><th>References</th></tr></thead>
                            <tbody><?php foreach ($schema['relations'] as $relation): ?><tr><td><?= e($relation['COLUMN_NAME']) ?></td><td><?= e($relation['REFERENCED_TABLE_NAME']) ?>.<?= e($relation['REFERENCED_COLUMN_NAME']) ?></td></tr><?php endforeach; ?></tbody>
                        </table>
                    <?php else: ?><p>No foreign-key relationships defined.</p><?php endif; ?>
                </section>
            </div>

            <footer class="schema-document-foot">Read-only metadata report · Example values are sampled from the first available record; sensitive values are masked.</footer>
        </article>
    <?php endif; ?>
</section>
