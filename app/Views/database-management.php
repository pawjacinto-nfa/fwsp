<?php
$tableCount = count($tables ?? []);
$columnCount = count($schema['columns'] ?? []);
$rowEstimate = isset($schema['table']['TABLE_ROWS']) ? (int) $schema['table']['TABLE_ROWS'] : 0;
$relationByColumn = [];
foreach (($schema['relations'] ?? []) as $relation) {
    $relationByColumn[$relation['COLUMN_NAME']] = $relation;
}
?>
<section class="workspace-section database-management-page">
    <div class="section-head compact no-print">
        <div>
            <p class="eyebrow">System Admin</p>
            <h3>Database Management</h3>
            <p class="mb-0 text-muted">Inspect and print a clear, read-only data dictionary for any database table.</p>
        </div>
    </div>

    <div class="database-toolbar panel no-print">
        <form method="get" class="database-table-picker">
            <input type="hidden" name="page" value="database-management">
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
