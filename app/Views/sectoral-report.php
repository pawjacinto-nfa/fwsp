<section class="workspace-section report-page">
    <div class="section-head compact no-print">
        <div>
            <p class="eyebrow">Report Generation</p>
            <h3>Sex Disaggregated Data Analytics</h3>
        </div>
        <div class="quick-actions">
            <a class="btn btn-outline-success" href="index.php?page=reports">Summary Report</a>
        </div>
    </div>

    <?php
    $score = $sectoralScore ?? ['breakdown' => []];
    $breakdown = $score['breakdown'] ?? [];
    $selectedSddFilters = (array) ($score['selected_filters'] ?? ($filters['sdd_filter'] ?? []));
    $chartBoards = $score['chart_boards'] ?? ['sex' => [], 'sectoral' => [], 'sogie' => []];
    $sexChartTotal = array_sum(array_map('intval', $chartBoards['sex'] ?? []));
    $sectoralChartTotal = array_sum(array_map('intval', $chartBoards['sectoral'] ?? []));
    $sogieChartTotal = array_sum(array_map('intval', $chartBoards['sogie'] ?? []));
    $sexChartPayload = json_encode($chartBoards['sex'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $sectoralChartPayload = json_encode($chartBoards['sectoral'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $sogieChartPayload = json_encode($chartBoards['sogie'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $sddFilterOptions = [
        'male' => 'Male',
        'female' => 'Female',
        'young' => 'Young',
        'adult' => 'Adult',
        'senior' => 'Senior Citizen',
        'sogie' => 'SOGIE',
        'muslim' => 'Muslim',
        'ip' => 'Indigenous People',
    ];
    ?>
    <form method="get" class="panel filter-panel no-print">
        <input type="hidden" name="page" value="sectoral-report">
        <div class="row g-3 align-items-end">
            <?php
            $locationClass = 'col-md-2';
            $locationRequired = false;
            $locationIncludeAll = true;
            $locationValues = $filters ?? [];
            $locationLabelWarehouse = 'Facility';
            require BASE_PATH . '/app/Views/partials/location-selects.php';
            ?>
            <div class="col-md-2">
                <label class="form-label">Result Basis</label>
                <select name="source" class="form-select">
                    <option value="farmers" <?= ($filters['source'] ?? 'farmers') === 'farmers' ? 'selected' : '' ?>>Farmers</option>
                    <option value="sold_palay" <?= ($filters['source'] ?? '') === 'sold_palay' ? 'selected' : '' ?>>Farmers who sold palay</option>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">From</label><input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">To</label><input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="form-control"></div>
            <div class="col-md-1"><button class="btn btn-success w-100" type="submit">Apply</button></div>
            <div class="col-md-1"><a class="btn btn-outline-success w-100" href="index.php?page=sectoral-report">Reset</a></div>
        </div>
    </form>

    <section class="sector-scoreboard">
        <article class="sector-score-card headline">
            <span>Total Sectoral Farmers</span>
            <strong><?= number_format((int) ($score['total_sectoral_farmers'] ?? 0)) ?></strong>
            <p>Unique farmers counted once even when multiple sectoral tags apply.</p>
        </article>
        <article class="sector-score-card headline">
            <span>Inclusivity Rate</span>
            <strong><?= number_format((float) ($score['inclusivity_rate'] ?? 0), 2) ?>%</strong>
            <p>Sectoral farmers divided by the selected unique farmer basis.</p>
        </article>
        <article class="sector-score-card headline">
            <span>Total Unique Farmers</span>
            <strong><?= number_format((int) ($score['total_farmers'] ?? 0)) ?></strong>
            <p><?= ($filters['source'] ?? 'farmers') === 'sold_palay' ? 'Unique farmer IDs represented in filtered palay transactions.' : 'Unique farmer profiles in the selected location scope.' ?></p>
        </article>
    </section>

    <form method="get" class="sdd-check-filter-panel no-print">
        <input type="hidden" name="page" value="sectoral-report">
        <?php foreach (['region_id', 'branch_id', 'province_id', 'warehouse_id', 'source', 'date_from', 'date_to'] as $filterKey): ?>
            <?php if (($filters[$filterKey] ?? '') !== ''): ?>
                <input type="hidden" name="<?= e($filterKey) ?>" value="<?= e($filters[$filterKey]) ?>">
            <?php endif; ?>
        <?php endforeach; ?>
        <div class="sdd-check-filter-row" aria-label="SDD checkmark filters">
            <?php foreach ($sddFilterOptions as $value => $label): ?>
                <label class="sdd-check-filter <?= in_array($value, $selectedSddFilters, true) ? 'is-selected' : '' ?>">
                    <input type="checkbox" name="sdd_filter[]" value="<?= e($value) ?>" <?= in_array($value, $selectedSddFilters, true) ? 'checked' : '' ?>>
                    <span><?= e($label) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="sdd-check-actions">
            <button class="btn btn-success" type="submit">Apply</button>
            <a class="btn btn-outline-success" href="index.php?page=sectoral-report">Clear</a>
        </div>
    </form>

    <section class="sector-breakdown-grid">
        <?php foreach ($breakdown as $label => $count): ?>
            <article class="sector-score-card">
                <span><?= e($label) ?></span>
                <strong><?= number_format((int) $count) ?></strong>
                <p>Unique farmers tagged in this group.</p>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="sdd-chart-board-grid">
        <article class="panel sdd-chart-board">
            <div class="panel-head"><h2>Male and Female</h2><span class="sdd-chart-total">Total: <?= number_format($sexChartTotal) ?></span></div>
            <canvas class="sdd-pie-chart" data-pie-chart="<?= e($sexChartPayload ?: '{}') ?>" data-palette="sex" aria-label="Pie chart for male and female farmers" role="img"></canvas>
        </article>
        <article class="panel sdd-chart-board">
            <div class="panel-head"><h2>Sectoral</h2><span class="sdd-chart-total">Total: <?= number_format($sectoralChartTotal) ?></span></div>
            <canvas class="sdd-pie-chart" data-pie-chart="<?= e($sectoralChartPayload ?: '{}') ?>" data-palette="sectoral" aria-label="Pie chart for sectoral farmers" role="img"></canvas>
        </article>
        <article class="panel sdd-chart-board">
            <div class="panel-head"><h2>SOGIE</h2><span class="sdd-chart-total">Total: <?= number_format($sogieChartTotal) ?></span></div>
            <canvas class="sdd-pie-chart" data-pie-chart="<?= e($sogieChartPayload ?: '{}') ?>" data-palette="rainbow" aria-label="Rainbow pie chart for SOGIE identities" role="img"></canvas>
        </article>
    </section>
</section>
