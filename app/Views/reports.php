<?php
$view = $view ?? 'summary';
$reportFormat = $reportFormat ?? (($filters['report_format'] ?? '') === 'branch_region' ? 'branch_region' : 'default');
$reportSwitchParams = ['page' => 'reports'];
foreach (['region_id', 'branch_id', 'province_id', 'warehouse_id', 'date_from', 'date_to'] as $filterKey) {
    if (($filters[$filterKey] ?? '') !== '') {
        $reportSwitchParams[$filterKey] = $filters[$filterKey];
    }
}
$defaultReportUrl = 'index.php?' . http_build_query($reportSwitchParams);
$branchRegionReportUrl = 'index.php?' . http_build_query($reportSwitchParams + ['report_format' => 'branch_region']);
?>
<section class="workspace-section report-page">
    <div class="section-head compact no-print">
        <div>
            <p class="eyebrow">Report Generation</p>
            <h3><?= $view === 'sectoral' ? 'Sex Disaggregated Data Analytics' : 'Summary Reports on Farmers Who Sold Palay' ?></h3>
        </div>
        <?php if ($view === 'sectoral'): ?>
            <div class="quick-actions">
                <a class="btn btn-outline-success" href="index.php?page=reports">Summary Report</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($view === 'sectoral'): ?>
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
    <?php else: ?>
        <div class="report-options-bar no-print">
            <div class="quick-actions">
                <a class="btn <?= $reportFormat === 'default' ? 'btn-success' : 'btn-outline-success' ?>" href="<?= e($defaultReportUrl) ?>">National Summary</a>
                <a class="btn <?= $reportFormat === 'branch_region' ? 'btn-success' : 'btn-outline-success' ?>" href="<?= e($branchRegionReportUrl) ?>">Regional Summary</a>
            </div>
            <button class="floating-print-button" type="button" onclick="window.print()" aria-label="Print or save PDF">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M7 8V3h10v5"></path>
                    <path d="M7 17H5a3 3 0 0 1-3-3v-3a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v3a3 3 0 0 1-3 3h-2"></path>
                    <path d="M7 14h10v7H7z"></path>
                    <path d="M17 11h.01"></path>
                </svg>
            </button>
        </div>
        <form method="get" class="panel filter-panel no-print">
            <input type="hidden" name="page" value="reports">
            <input type="hidden" name="scope" value="<?= e($scope ?? 'region') ?>">
            <input type="hidden" name="report_format" value="<?= e($reportFormat) ?>">
            <div class="row g-3 align-items-end">
                <?php
                $locationClass = 'col-md-2';
                $locationRequired = false;
                $locationIncludeAll = true;
                $locationValues = $filters ?? [];
                $locationLabelWarehouse = 'Facility';
                require BASE_PATH . '/app/Views/partials/location-selects.php';
                ?>
            </div>
            <div class="row g-3 align-items-end mt-1">
                <div class="col-md-2"><label class="form-label">From</label><input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">To</label><input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="form-control"></div>
                <div class="col-md-1"><button class="btn btn-success w-100" type="submit">Apply</button></div>
                <div class="col-md-1"><a class="btn btn-outline-success w-100" href="index.php?page=reports">Reset</a></div>
            </div>
        </form>

        <?php
        $metricKeys = \App\Models\Report::summaryMetricKeys();
        $grandTotals = array_fill_keys($metricKeys, 0);
        $totalSourceRows = $reportFormat === 'branch_region'
            ? array_filter($rows, fn (array $row): bool => ($row['row_type'] ?? '') === 'region_total')
            : $rows;
        foreach ($totalSourceRows as $row) {
            foreach ($grandTotals as $key => $value) {
                $grandTotals[$key] += (float) ($row[$key] ?? 0);
            }
        }
        $formatReportValue = function (float|int|string|null $value, int $decimals = 0) use ($reportFormat): string {
            $numeric = (float) ($value ?? 0);
            if ($reportFormat === 'branch_region' && abs($numeric) < 0.00001) {
                return '-';
            }

            return number_format($numeric, $decimals);
        };
        ?>
        <article class="report-sheet">
            <header class="report-title">
                <h2>SUMMARY REPORTS ON FARMERS WHO SOLD PALAY</h2>
                <?php
                $dateLabel = 'AS OF ' . strtoupper(date('F Y'));
                if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
                    $fromLabel = !empty($filters['date_from']) ? date('F d, Y', strtotime($filters['date_from'])) : 'Start';
                    $toLabel = !empty($filters['date_to']) ? date('F d, Y', strtotime($filters['date_to'])) : 'Present';
                    $dateLabel = strtoupper($fromLabel . ' - ' . $toLabel);
                }
                ?>
                <p><?= $reportFormat === 'branch_region' ? 'BY BRANCH, BY REGION / ' . e($dateLabel) : e($dateLabel) ?></p>
            </header>
            <p class="report-unit">(IN BAGS)</p>
            <div class="table-responsive">
                <table class="report-table <?= $reportFormat === 'branch_region' ? 'branch-region-report' : '' ?>">
                    <thead>
                        <tr>
                            <th rowspan="2"><?= $reportFormat === 'branch_region' ? 'REGION / BRANCH' : 'REGION' ?></th>
                            <th colspan="3">INDIVIDUAL FARMERS</th>
                            <th colspan="3">WALK-IN FARMERS</th>
                            <th colspan="4">FARMERS ORGANIZATION</th>
                            <th colspan="3">TOTAL</th>
                        </tr>
                        <tr>
                            <th>No. of Farmers</th><th>Qty Sold (50kg/Bag)</th><th>Amount Paid (NFA)</th>
                            <th>No. of Farmers</th><th>Qty Sold (50kg/Bag)</th><th>Amount Paid (NFA)</th>
                            <th>No. of FOS</th><th>No. of Members</th><th>Qty Sold (50kg/Bag)</th><th>Amount Paid (NFA)</th>
                            <th>No. of Farmers</th><th>Qty Sold (50kg/Bag)</th><th>Amount Paid (NFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($reportFormat === 'default'): ?>
                            <tr class="report-grand-total-row">
                                <th>Philippines</th>
                                <td><?= number_format($grandTotals['individual_farmers']) ?></td>
                                <td><?= number_format($grandTotals['individual_qty']) ?></td>
                                <td><?= number_format($grandTotals['individual_amount'], 2) ?></td>
                                <td><?= number_format($grandTotals['walkin_farmers']) ?></td>
                                <td><?= number_format($grandTotals['walkin_qty']) ?></td>
                                <td><?= number_format($grandTotals['walkin_amount'], 2) ?></td>
                                <td><?= number_format($grandTotals['fo_count']) ?></td>
                                <td><?= number_format($grandTotals['fo_members']) ?></td>
                                <td><?= number_format($grandTotals['fo_qty']) ?></td>
                                <td><?= number_format($grandTotals['fo_amount'], 2) ?></td>
                                <td><?= number_format($grandTotals['total_farmers']) ?></td>
                                <td><?= number_format($grandTotals['total_qty']) ?></td>
                                <td><?= number_format($grandTotals['total_amount'], 2) ?></td>
                            </tr>
                            <tr class="report-spacer-row" aria-hidden="true"><td colspan="14">&nbsp;</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $rowType = $row['row_type'] ?? '';
                            $label = $reportFormat === 'branch_region'
                                ? ($rowType === 'branch' ? ($row['branch'] ?? 'Unassigned Branch') : ($row['region'] ?? 'Unassigned'))
                                : ($row[$scope === 'branch' ? 'region_branch' : 'region'] ?? 'Unassigned');
                            ?>
                            <tr class="<?= $rowType === 'region_total' ? 'report-region-total' : ($rowType === 'branch' ? 'report-branch-row' : '') ?>">
                                <th><?= e($label ?: 'Unassigned') ?></th>
                                <td><?= $formatReportValue($row['individual_farmers']) ?></td>
                                <td><?= $formatReportValue($row['individual_qty']) ?></td>
                                <td><?= $formatReportValue($row['individual_amount'], 2) ?></td>
                                <td><?= $formatReportValue($row['walkin_farmers']) ?></td>
                                <td><?= $formatReportValue($row['walkin_qty']) ?></td>
                                <td><?= $formatReportValue($row['walkin_amount'], 2) ?></td>
                                <td><?= $formatReportValue($row['fo_count']) ?></td>
                                <td><?= $formatReportValue($row['fo_members']) ?></td>
                                <td><?= $formatReportValue($row['fo_qty']) ?></td>
                                <td><?= $formatReportValue($row['fo_amount'], 2) ?></td>
                                <td><?= $formatReportValue($row['total_farmers']) ?></td>
                                <td><?= $formatReportValue($row['total_qty']) ?></td>
                                <td><?= $formatReportValue($row['total_amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if ($reportFormat !== 'default'): ?>
                        <tfoot>
                            <tr>
                                <th>GRAND TOTAL</th>
                                <td><?= number_format($grandTotals['individual_farmers']) ?></td>
                                <td><?= number_format($grandTotals['individual_qty']) ?></td>
                                <td><?= number_format($grandTotals['individual_amount'], 2) ?></td>
                                <td><?= number_format($grandTotals['walkin_farmers']) ?></td>
                                <td><?= number_format($grandTotals['walkin_qty']) ?></td>
                                <td><?= number_format($grandTotals['walkin_amount'], 2) ?></td>
                                <td><?= number_format($grandTotals['fo_count']) ?></td>
                                <td><?= number_format($grandTotals['fo_members']) ?></td>
                                <td><?= number_format($grandTotals['fo_qty']) ?></td>
                                <td><?= number_format($grandTotals['fo_amount'], 2) ?></td>
                                <td><?= number_format($grandTotals['total_farmers']) ?></td>
                                <td><?= number_format($grandTotals['total_qty']) ?></td>
                                <td><?= number_format($grandTotals['total_amount'], 2) ?></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </article>
    <?php endif; ?>
</section>
