<?php
$view = $view ?? 'summary';
$allowedReportFormats = ['default', 'branch_region', 'province_summary', 'sdd_summary', 'monthly_sdd_summary', 'full_list_fsr', 'ip_group_delivery'];
$reportFormat = in_array(($reportFormat ?? ($filters['report_format'] ?? 'default')), $allowedReportFormats, true)
    ? ($reportFormat ?? ($filters['report_format'] ?? 'default'))
    : 'default';
$reportSwitchParams = ['page' => 'reports'];
foreach (['region_id', 'branch_id', 'province_id', 'warehouse_id', 'date_from', 'date_to'] as $filterKey) {
    if (($filters[$filterKey] ?? '') !== '') {
        $reportSwitchParams[$filterKey] = $filters[$filterKey];
    }
}
$defaultReportUrl = 'index.php?' . http_build_query($reportSwitchParams);
$branchRegionReportUrl = 'index.php?' . http_build_query($reportSwitchParams + ['report_format' => 'branch_region']);
$provinceSummaryReportUrl = 'index.php?' . http_build_query($reportSwitchParams + ['report_format' => 'province_summary']);
$sddSummaryReportUrl = 'index.php?' . http_build_query($reportSwitchParams + ['report_format' => 'sdd_summary']);
$monthlySddSummaryReportUrl = 'index.php?' . http_build_query($reportSwitchParams + ['report_format' => 'monthly_sdd_summary']);
$fullListReportUrl = 'index.php?' . http_build_query($reportSwitchParams + ['report_format' => 'full_list_fsr']);
$ipGroupReportUrl = 'index.php?' . http_build_query($reportSwitchParams + ['report_format' => 'ip_group_delivery']);
$reportPageTitles = [
    'default' => 'Summary Reports on Farmers Who Sold Palay',
    'branch_region' => 'Summary Reports on Farmers Who Sold Palay',
    'province_summary' => 'Provincial Summary',
    'sdd_summary' => 'SDD Summary Report',
    'monthly_sdd_summary' => 'SDD Report (Monthly)',
    'full_list_fsr' => 'Full List (FSR)',
    'ip_group_delivery' => 'IP Group Delivery',
];
?>
<section class="workspace-section report-page">
    <div class="section-head compact no-print">
        <div>
            <p class="eyebrow">Report Generation</p>
            <h3><?= e($view === 'sectoral' ? 'Sex Disaggregated Data Analytics' : ($reportPageTitles[$reportFormat] ?? $reportPageTitles['default'])) ?></h3>
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
            <p class="sdd-check-filter-intro">You may select one or more groups to filter the results.</p>
            <div class="sdd-check-filter-row" aria-label="SDD checkmark filters">
                <?php foreach ($sddFilterOptions as $value => $label): ?>
                    <label class="sdd-check-filter sdd-check-filter--<?= e($value) ?> <?= in_array($value, ['male', 'female', 'young', 'adult', 'senior', 'sogie', 'muslim', 'ip'], true) ? 'has-image' : '' ?> <?= in_array($value, $selectedSddFilters, true) ? 'is-selected' : '' ?>">
                        <input type="checkbox" name="sdd_filter[]" value="<?= e($value) ?>" <?= in_array($value, $selectedSddFilters, true) ? 'checked' : '' ?>>
                        <span class="sdd-check-filter-visual" aria-hidden="true">
                        <?php if (!in_array($value, ['male', 'female', 'young', 'adult', 'senior', 'sogie', 'muslim', 'ip'], true)): ?>
                                <span class="sdd-check-filter-initial"><?= e(strtoupper($label[0] ?? '')) ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="sdd-check-filter-label"><?= e($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="sdd-check-actions">
                <button class="btn btn-success" type="submit">Apply</button>
                <a class="btn btn-outline-success" href="index.php?page=sectoral-report">Clear</a>
            </div>
            <p class="small text-muted mb-0">Selections within the same category are matched as alternatives; selections across categories are combined.</p>
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
                <a class="btn <?= $reportFormat === 'province_summary' ? 'btn-success' : 'btn-outline-success' ?>" href="<?= e($provinceSummaryReportUrl) ?>">Provincial Summary</a>
                <a class="btn <?= $reportFormat === 'sdd_summary' ? 'btn-success' : 'btn-outline-success' ?>" href="<?= e($sddSummaryReportUrl) ?>">SDD Summary Report</a>
                <a class="btn <?= $reportFormat === 'monthly_sdd_summary' ? 'btn-success' : 'btn-outline-success' ?>" href="<?= e($monthlySddSummaryReportUrl) ?>">SDD Report (Monthly)</a>
                <a class="btn <?= $reportFormat === 'full_list_fsr' ? 'btn-success' : 'btn-outline-success' ?>" href="<?= e($fullListReportUrl) ?>">Full List (FSR)</a>
                <a class="btn <?= $reportFormat === 'ip_group_delivery' ? 'btn-success' : 'btn-outline-success' ?>" href="<?= e($ipGroupReportUrl) ?>">IP Group Delivery</a>
            </div>
            <div class="report-action-stack">
                <button class="floating-print-button" type="button" onclick="window.print()" aria-label="Print or save PDF">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M7 8V3h10v5"></path>
                        <path d="M7 17H5a3 3 0 0 1-3-3v-3a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v3a3 3 0 0 1-3 3h-2"></path>
                        <path d="M7 14h10v7H7z"></path>
                        <path d="M17 11h.01"></path>
                    </svg>
                </button>
                <button class="floating-export-button" type="button" data-report-export data-report-title="<?= e($reportPageTitles[$reportFormat] ?? 'Reports') ?>" aria-label="Export spreadsheet" title="Export spreadsheet">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                        <path d="M14 3v6h6"></path>
                        <path d="M8 13h8"></path>
                        <path d="M8 17h8"></path>
                        <path d="M8 9h2"></path>
                    </svg>
                </button>
            </div>
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
                <div class="col-md-1"><a class="btn btn-outline-success w-100" href="index.php?page=reports&amp;report_format=<?= e($reportFormat) ?>">Reset</a></div>
            </div>
        </form>

        <?php if ($reportFormat === 'monthly_sdd_summary'): ?>
            <?php
            $rangeStart = !empty($filters['date_from']) ? new DateTimeImmutable($filters['date_from']) : new DateTimeImmutable(date('Y-01-01'));
            $rangeEnd = !empty($filters['date_to']) ? new DateTimeImmutable($filters['date_to']) : new DateTimeImmutable(date('Y-12-31'));
            if ($rangeStart > $rangeEnd) {
                [$rangeStart, $rangeEnd] = [$rangeEnd, $rangeStart];
            }
            $monthCursor = $rangeStart->modify('first day of this month');
            $monthEnd = $rangeEnd->modify('first day of this month');
            $reportMonths = [];
            while ($monthCursor <= $monthEnd) {
                $periodKey = $monthCursor->format('Y-m');
                $reportMonths[$periodKey] = $rangeStart->format('Y') === $rangeEnd->format('Y')
                    ? $monthCursor->format('M')
                    : $monthCursor->format('M Y');
                $monthCursor = $monthCursor->modify('+1 month');
            }

            $monthlyMatrix = [];
            foreach ($rows as $row) {
                $periodKey = (string) ($row['period'] ?? '');
                if (!isset($reportMonths[$periodKey])) {
                    continue;
                }
                $region = (string) ($row['region'] ?? 'Unassigned');
                $seller = (string) ($row['seller_classification'] ?? 'Individual');
                $sex = (string) ($row['sex'] ?? 'Male');
                $values = [
                    'people_count' => (float) ($row['people_count'] ?? 0),
                    'qty_bags' => (float) ($row['qty_bags'] ?? 0),
                    'qty_net_kg' => (float) ($row['qty_net_kg'] ?? 0),
                    'amount_paid' => (float) ($row['amount_paid'] ?? 0),
                ];
                $monthlyMatrix[$region][$seller][$sex][$periodKey] = $values;
            }
            $reportRegions = array_keys($monthlyMatrix);
            $sellerRows = ['Individual', 'Farmer Organization'];
            $sexRows = ['Male', 'Female'];
            $emptyMonthlyValues = ['people_count' => 0.0, 'qty_bags' => 0.0, 'qty_net_kg' => 0.0, 'amount_paid' => 0.0];
            $formatMonthlyValue = static function (string $metric, array $values): string {
                if ($metric === 'people_count') {
                    return number_format((float) ($values['people_count'] ?? 0));
                }
                if ($metric === 'qty_bags') {
                    $bags = (float) ($values['qty_bags'] ?? 0);
                    $netKg = (float) ($values['qty_net_kg'] ?? 0);
                    return number_format($bags, 3) . ' / ' . number_format($netKg, 3) . ' / ' . number_format($netKg / 1000, 3);
                }

                return number_format((float) ($values['amount_paid'] ?? 0), 3);
            };
            ?>
            <article class="report-sheet monthly-sdd-report-sheet">
                <header class="report-title">
                    <h2>SDD REPORT (MONTHLY)</h2>
                    <p><?= e(strtoupper($rangeStart->format('F d, Y') . ' - ' . $rangeEnd->format('F d, Y'))) ?></p>
                </header>
                <div class="table-responsive">
                    <table class="report-table monthly-sdd-report" style="min-width: <?= e(370 + count($reportMonths) * 96) ?>px">
                        <thead>
                            <tr>
                                <th>Particulars</th>
                                <?php foreach ($reportMonths as $monthLabel): ?>
                                    <th><?= e($monthLabel) ?></th>
                                <?php endforeach; ?>
                                <th>Cumulative Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportRegions as $region): ?>
                                <tr class="monthly-sdd-region-row">
                                    <th><?= e($region) ?></th>
                                    <?php foreach ($reportMonths as $_periodKey => $_monthLabel): ?><td></td><?php endforeach; ?>
                                    <td></td>
                                </tr>
                                <?php foreach ($sellerRows as $seller): ?>
                                    <tr class="monthly-sdd-seller-row">
                                        <th><?= e($seller === 'Individual' ? 'Individual Farmers' : 'Farmer Organization') ?></th>
                                        <?php foreach ($reportMonths as $_periodKey => $_monthLabel): ?><td></td><?php endforeach; ?>
                                        <td></td>
                                    </tr>
                                    <?php foreach ($sexRows as $sex): ?>
                                        <?php
                                        $rowCumulative = $emptyMonthlyValues;
                                        foreach ($reportMonths as $periodKey => $_monthLabel) {
                                            $monthValues = $monthlyMatrix[$region][$seller][$sex][$periodKey] ?? $emptyMonthlyValues;
                                            foreach ($rowCumulative as $metric => $_value) {
                                                $rowCumulative[$metric] += (float) ($monthValues[$metric] ?? 0);
                                            }
                                        }
                                        ?>
                                        <tr class="monthly-sdd-sex-row">
                                            <th><?= e($sex) ?></th>
                                            <?php foreach ($reportMonths as $periodKey => $_monthLabel): ?>
                                                <?php $values = $monthlyMatrix[$region][$seller][$sex][$periodKey] ?? $emptyMonthlyValues; ?>
                                                <td><?= $formatMonthlyValue('people_count', $values) ?></td>
                                            <?php endforeach; ?>
                                            <td class="monthly-sdd-cumulative-cell"><?= $formatMonthlyValue('people_count', $rowCumulative) ?></td>
                                        </tr>
                                        <tr class="monthly-sdd-metric-row">
                                            <th>Qty Sold (50kg Bags / Nkg / MT)</th>
                                            <?php foreach ($reportMonths as $periodKey => $_monthLabel): ?>
                                                <?php $values = $monthlyMatrix[$region][$seller][$sex][$periodKey] ?? $emptyMonthlyValues; ?>
                                                <td><?= $formatMonthlyValue('qty_bags', $values) ?></td>
                                            <?php endforeach; ?>
                                            <td class="monthly-sdd-cumulative-cell"><?= $formatMonthlyValue('qty_bags', $rowCumulative) ?></td>
                                        </tr>
                                        <tr class="monthly-sdd-metric-row">
                                            <th>Amount Paid</th>
                                            <?php foreach ($reportMonths as $periodKey => $_monthLabel): ?>
                                                <?php $values = $monthlyMatrix[$region][$seller][$sex][$periodKey] ?? $emptyMonthlyValues; ?>
                                                <td><?= $formatMonthlyValue('amount_paid', $values) ?></td>
                                            <?php endforeach; ?>
                                            <td class="monthly-sdd-cumulative-cell"><?= $formatMonthlyValue('amount_paid', $rowCumulative) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <?php if ($reportRegions === []): ?>
                                <tr><td colspan="<?= e(2 + count($reportMonths)) ?>">No transactions found for the selected filters.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        <?php elseif ($reportFormat === 'ip_group_delivery'): ?>
            <?php
            $dateLabel = 'AS OF ' . strtoupper(date('F Y'));
            if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
                $fromLabel = !empty($filters['date_from']) ? date('F d, Y', strtotime($filters['date_from'])) : 'Start';
                $toLabel = !empty($filters['date_to']) ? date('F d, Y', strtotime($filters['date_to'])) : 'Present';
                $dateLabel = strtoupper($fromLabel . ' - ' . $toLabel);
            }
            $formatIpLocation = fn (array $row): string => trim(implode(' / ', array_filter([
                $row['region_name'] ?? '',
                $row['branch_name'] ?? '',
                $row['province_name'] ?? '',
                $row['warehouse_name'] ?? '',
            ])));
            ?>
            <article class="report-sheet full-list-report-sheet ip-group-report-sheet">
                <header class="report-title">
                    <h2>IP GROUP DELIVERY</h2>
                    <p><?= e($dateLabel) ?></p>
                </header>
                <div class="table-responsive">
                    <table class="report-table full-list-report ip-group-report-table">
                        <colgroup>
                            <col style="width: 3%">
                            <col style="width: 9%">
                            <col style="width: 10%">
                            <col style="width: 14%">
                            <col style="width: 5%">
                            <col style="width: 14%">
                            <col style="width: 7%">
                            <col style="width: 9%">
                            <col style="width: 8%">
                            <col style="width: 21%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Farmer Key</th>
                                <th>RSBSA No.</th>
                                <th>Name of Farmer</th>
                                <th>Sex</th>
                                <th>Indigenous Sector Group</th>
                                <th>IP Group Member</th>
                                <th>Delivery Date</th>
                                <th>WSR No.</th>
                                <th>Delivery Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($rows ?? []) as $index => $row): ?>
                                <tr>
                                    <td><?= number_format($index + 1) ?></td>
                                    <td><?= e($row['farmer_key'] ?? '') ?></td>
                                    <td><?= e($row['rsbsa'] ?? '') ?></td>
                                    <td><?= e(trim((string) ($row['farmer_name'] ?? '')) ?: 'N/A') ?></td>
                                    <td><?= e($row['sex'] ?? '') ?></td>
                                    <td><?= e($row['organization_name'] ?? '') ?></td>
                                    <td><?= !empty($row['is_ip_group_member']) ? 'Yes' : 'No' ?></td>
                                    <td><?= e($row['delivery_date'] ?? '') ?></td>
                                    <td><?= e($row['wsr'] ?? '') ?></td>
                                    <td class="report-address-cell"><?= e($formatIpLocation($row)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (($rows ?? []) === []): ?>
                                <tr><td colspan="10">No IP Group Delivery farmers found for the selected filters.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        <?php elseif ($reportFormat === 'full_list_fsr'): ?>
            <?php
            $dateLabel = 'AS OF ' . strtoupper(date('F Y'));
            if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
                $fromLabel = !empty($filters['date_from']) ? date('F d, Y', strtotime($filters['date_from'])) : 'Start';
                $toLabel = !empty($filters['date_to']) ? date('F d, Y', strtotime($filters['date_to'])) : 'Present';
                $dateLabel = strtoupper($fromLabel . ' - ' . $toLabel);
            }
            $individualRows = $rows['individual'] ?? [];
            $organizationRows = $rows['organizations'] ?? [];
            $organizationSections = [
                [
                    'title' => 'II.A FARMERS ORGANIZATION',
                    'empty_label' => 'No farmer organization transactions found.',
                    'rows' => array_values(array_filter(
                        $organizationRows,
                        fn (array $row): bool => ($row['classification_type'] ?? 'Farmer Organization') === 'Farmer Organization'
                    )),
                ],
                [
                    'title' => 'II.B INDIGENOUS PEOPLE GROUP',
                    'empty_label' => 'No Indigenous People Group transactions found.',
                    'rows' => array_values(array_filter(
                        $organizationRows,
                        fn (array $row): bool => ($row['classification_type'] ?? '') === 'Indigenous People Group'
                    )),
                ],
            ];
            $formatLocation = fn (array $row): string => trim(implode(' / ', array_filter([
                $row['region_name'] ?? '',
                $row['branch_name'] ?? '',
                $row['province_name'] ?? '',
                $row['warehouse_name'] ?? '',
            ])));
            $formatAmount = fn (array $row): string => number_format((float) ($row['total_amount'] ?? 0), 3);
            $individualTotals = ['bags_50kg' => 0, 'net_kg' => 0.0, 'amount' => 0.0];
            foreach ($individualRows as $row) {
                $individualTotals['bags_50kg'] += (float) ($row['bags_50kg'] ?? 0);
                $individualTotals['net_kg'] += (float) ($row['net_kilogram'] ?? 0);
                $individualTotals['amount'] += (float) ($row['total_amount'] ?? 0);
            }
            foreach ($organizationSections as &$organizationSection) {
                $organizationSection['totals'] = ['bags_50kg' => 0, 'net_kg' => 0.0, 'amount' => 0.0];
                $countedClassificationTransactions = [];
                foreach ($organizationSection['rows'] as $row) {
                    $transactionId = (int) ($row['id'] ?? 0);
                    if ($transactionId > 0 && isset($countedClassificationTransactions[$transactionId])) {
                        continue;
                    }

                    $countedClassificationTransactions[$transactionId] = true;
                    $organizationSection['totals']['bags_50kg'] += (float) ($row['bags_50kg'] ?? 0);
                    $organizationSection['totals']['net_kg'] += (float) ($row['net_kilogram'] ?? 0);
                    $organizationSection['totals']['amount'] += (float) ($row['total_amount'] ?? 0);
                }
            }
            unset($organizationSection);
            ?>
            <article class="report-sheet full-list-report-sheet">
                <header class="report-title">
                    <h2>FULL LIST (FSR)</h2>
                    <p><?= e($dateLabel) ?></p>
                </header>

                <section class="full-list-report-section">
                    <h3>I. INDIVIDUAL FARMERS</h3>
                    <div class="table-responsive">
                        <table class="report-table full-list-report full-list-individual-table">
                            <colgroup>
                                <col style="width: 3%">
                                <col style="width: 11%">
                                <col style="width: 9%">
                                <col style="width: 4%">
                                <col style="width: 14%">
                                <col style="width: 6%">
                                <col style="width: 9%">
                                <col style="width: 7%">
                                <col style="width: 7%">
                                <col style="width: 6%">
                                <col style="width: 6%">
                                <col style="width: 8%">
                                <col style="width: 10%">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name of Farmer</th>
                                    <th>RSBSA No.</th>
                                    <th>Sex</th>
                                    <th>Farm Location</th>
                                    <th>Verified Farm Area (ha)</th>
                                    <th>Mode of Procurement</th>
                                    <th>Delivery Date</th>
                                    <th>WSR No.</th>
                                    <th>Buying Price/kg</th>
                                    <th>No. of Bags</th>
                                    <th>In MT</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $currentMode = null; ?>
                                <?php foreach ($individualRows as $index => $row): ?>
                                    <?php if ($currentMode !== ($row['procurement_type'] ?? '')): ?>
                                        <?php $currentMode = $row['procurement_type'] ?? ''; ?>
                                        <tr class="report-group-row"><th colspan="13"><?= e($currentMode ?: 'Unspecified Procurement') ?></th></tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><?= number_format($index + 1) ?></td>
                                        <td><?= e(trim((string) ($row['farmer_name'] ?? '')) ?: 'N/A') ?></td>
                                        <td><?= e($row['rsbsa'] ?? '') ?></td>
                                        <td><?= e($row['sex'] ?? '') ?></td>
                                        <td class="report-address-cell"><?= e($formatLocation($row)) ?></td>
                                        <td><?= number_format((float) ($row['verified_farm_area'] ?? 0), 3) ?></td>
                                        <td><?= e($row['procurement_type'] ?? '') ?></td>
                                        <td><?= e($row['delivery_date'] ?? '') ?></td>
                                        <td><?= e($row['wsr'] ?? '') ?></td>
                                        <td><?= number_format((float) ($row['price_per_kilogram'] ?? 0), 3) ?></td>
                                        <td><?= number_format((float) ($row['bags_50kg'] ?? 0), 3) ?></td>
                                        <td><?= number_format((float) ($row['net_kilogram'] ?? 0) / 1000, 3) ?></td>
                                        <td><?= $formatAmount($row) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($individualRows === []): ?>
                                    <tr><td colspan="13">No individual farmer transactions found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="10">TOTAL</th>
                                    <td><?= number_format($individualTotals['bags_50kg'], 3) ?></td>
                                    <td><?= number_format($individualTotals['net_kg'] / 1000, 3) ?></td>
                                    <td><?= number_format($individualTotals['amount'], 3) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                <section class="full-list-report-section full-list-classification-title">
                    <h3>II. FARMER GROUPS</h3>
                </section>

                <?php foreach ($organizationSections as $organizationSection): ?>
                <?php
                $organizationRows = $organizationSection['rows'];
                $organizationTotals = $organizationSection['totals'];
                ?>
                <section class="full-list-report-section">
                    <h3><?= e($organizationSection['title']) ?></h3>
                    <div class="table-responsive">
                        <table class="report-table full-list-report full-list-organization-table">
                            <colgroup>
                                <col style="width: 2.5%">
                                <col style="width: 9%">
                                <col style="width: 8%">
                                <col style="width: 8%">
                                <col style="width: 4%">
                                <col style="width: 3.5%">
                                <col style="width: 7%">
                                <col style="width: 10%">
                                <col style="width: 5%">
                                <col style="width: 7%">
                                <col style="width: 6%">
                                <col style="width: 5%">
                                <col style="width: 5%">
                                <col style="width: 5%">
                                <col style="width: 6%">
                                <col style="width: 9.5%">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name of Farmers Organization</th>
                                    <th>Member Name</th>
                                    <th>Authorized Representative</th>
                                    <th>No. of Members</th>
                                    <th>Sex</th>
                                    <th>RSBSA No.</th>
                                    <th>Farm Location</th>
                                    <th>Verified Farm Area (ha)</th>
                                    <th>Mode of Procurement</th>
                                    <th>Delivery Date</th>
                                    <th>WSR No.</th>
                                    <th>Buying Price/kg</th>
                                    <th>No. of Bags</th>
                                    <th>In MT</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $currentMode = null;
                                $currentOrganizationKey = null;
                                $organizationNumber = 0;
                                ?>
                                <?php foreach ($organizationRows as $row): ?>
                                    <?php if ($currentMode !== ($row['procurement_type'] ?? '')): ?>
                                        <?php $currentMode = $row['procurement_type'] ?? ''; ?>
                                        <?php $currentOrganizationKey = null; ?>
                                        <tr class="report-group-row"><th colspan="16"><?= e($currentMode ?: 'Unspecified Procurement') ?></th></tr>
                                    <?php endif; ?>
                                    <?php
                                    $organizationKey = (string) ($row['farmer_organization_id'] ?? '') . '|' . (string) ($row['organization_name'] ?? '');
                                    if ($currentOrganizationKey !== $organizationKey):
                                        $currentOrganizationKey = $organizationKey;
                                        $organizationNumber++;
                                    ?>
                                        <tr class="report-organization-row">
                                            <td><?= number_format($organizationNumber) ?></td>
                                            <th><?= e($row['organization_name'] ?? '') ?></th>
                                            <td></td>
                                            <td><?= e($row['representative_name'] ?? '') ?></td>
                                            <td><?= number_format((float) ($row['total_members'] ?? 0)) ?></td>
                                            <td colspan="11"></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="report-organization-member-row">
                                        <td></td>
                                        <td></td>
                                        <td class="report-member-name"><?= e(trim((string) ($row['member_name'] ?? '')) ?: 'N/A') ?></td>
                                        <td></td>
                                        <td></td>
                                        <td><?= e($row['member_sex'] ?? '') ?></td>
                                        <td><?= e($row['member_rsbsa'] ?? '') ?></td>
                                        <td class="report-address-cell"><?= e($formatLocation($row)) ?></td>
                                        <td><?= number_format((float) ($row['verified_farm_area'] ?? 0), 3) ?></td>
                                        <td><?= e($row['procurement_type'] ?? '') ?></td>
                                        <td><?= e($row['delivery_date'] ?? '') ?></td>
                                        <td><?= e($row['wsr'] ?? '') ?></td>
                                        <td><?= number_format((float) ($row['price_per_kilogram'] ?? 0), 3) ?></td>
                                        <td><?= number_format((float) ($row['bags_50kg'] ?? 0), 3) ?></td>
                                        <td><?= number_format((float) ($row['net_kilogram'] ?? 0) / 1000, 3) ?></td>
                                        <td><?= $formatAmount($row) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($organizationRows === []): ?>
                                    <tr><td colspan="16"><?= e($organizationSection['empty_label']) ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="13">TOTAL</th>
                                    <td><?= number_format($organizationTotals['bags_50kg'], 3) ?></td>
                                    <td><?= number_format($organizationTotals['net_kg'] / 1000, 3) ?></td>
                                    <td><?= number_format($organizationTotals['amount'], 3) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
                <?php endforeach; ?>
            </article>
        <?php else: ?>
        <?php
        $metricKeys = $reportFormat === 'sdd_summary'
            ? \App\Models\Report::sddSummaryMetricKeys()
            : \App\Models\Report::summaryMetricKeys();
        $grandTotals = array_fill_keys($metricKeys, 0);
        $isHierarchicalSummary = in_array($reportFormat, ['branch_region', 'province_summary'], true);
        $totalSourceRows = $isHierarchicalSummary
            ? array_filter($rows, fn (array $row): bool => ($row['row_type'] ?? '') === 'region_total')
            : $rows;
        foreach ($totalSourceRows as $row) {
            foreach ($grandTotals as $key => $value) {
                $grandTotals[$key] += (float) ($row[$key] ?? 0);
            }
        }
        $formatReportValue = function (float|int|string|null $value, int $decimals = 0) use ($isHierarchicalSummary): string {
            $numeric = (float) ($value ?? 0);
            if ($isHierarchicalSummary && abs($numeric) < 0.00001) {
                return '-';
            }

            return number_format($numeric, $decimals);
        };
        $formatMetricTons = fn (float|int|string|null $netKg): string => $formatReportValue((float) ($netKg ?? 0) / 1000, 3);
        $dateLabel = 'AS OF ' . strtoupper(date('F Y'));
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $fromLabel = !empty($filters['date_from']) ? date('F d, Y', strtotime($filters['date_from'])) : 'Start';
            $toLabel = !empty($filters['date_to']) ? date('F d, Y', strtotime($filters['date_to'])) : 'Present';
            $dateLabel = strtoupper($fromLabel . ' - ' . $toLabel);
        }
        ?>
        <article class="report-sheet">
            <header class="report-title">
                <h2><?= $reportFormat === 'sdd_summary' ? 'SDD SUMMARY REPORT' : 'SUMMARY REPORTS ON FARMERS WHO SOLD PALAY' ?></h2>
                <p><?php if ($reportFormat === 'branch_region'): ?>BY BRANCH, BY REGION / <?= e($dateLabel) ?><?php elseif ($reportFormat === 'province_summary'): ?>BY PROVINCE, BY BRANCH, BY REGION / <?= e($dateLabel) ?><?php else: ?><?= e($dateLabel) ?><?php endif; ?></p>
            </header>
            <p class="report-unit">(IN BAGS AND METRIC TONS)</p>
            <div class="table-responsive">
                <?php if ($reportFormat === 'sdd_summary'): ?>
                    <table class="report-table sdd-summary-report expanded-weight-report">
                        <thead>
                            <tr>
                                <th colspan="11">CUMULATIVE TOTAL</th>
                                <th colspan="5">GRAND TOTAL</th>
                                <th colspan="4">FARMER GROUPS</th>
                            </tr>
                            <tr>
                                <th rowspan="2">REGION</th>
                                <th colspan="5">MALE</th>
                                <th colspan="5">FEMALE</th>
                                <th rowspan="2">Number of Farmers (Male and Female)</th>
                                <th colspan="3">Qty Sold</th>
                                <th rowspan="2">Amount Paid (NFA)</th>
                                <th colspan="2">FARMERS ORGANIZATION</th>
                                <th colspan="2">INDIGENOUS PEOPLE GROUP</th>
                            </tr>
                            <tr>
                                <th>Count</th><th>No. of Bags</th><th>Nkg</th><th>In MT</th><th>Amount Paid (NFA)</th>
                                <th>Count</th><th>No. of Bags</th><th>Nkg</th><th>In MT</th><th>Amount Paid (NFA)</th>
                                <th>No. of Bags</th><th>Nkg</th><th>In MT</th>
                                <th>No. of Groups</th><th>No. of Members Served</th>
                                <th>No. of Groups</th><th>No. of Members Served</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="report-grand-total-row">
                                <th>Philippines</th>
                                <td><?= number_format($grandTotals['male_count']) ?></td>
                                <td><?= number_format($grandTotals['male_qty'], 3) ?></td>
                                <td><?= number_format($grandTotals['male_net_kg'], 3) ?></td>
                                <td><?= number_format($grandTotals['male_net_kg'] / 1000, 3) ?></td>
                                <td><?= number_format($grandTotals['male_amount'], 3) ?></td>
                                <td><?= number_format($grandTotals['female_count']) ?></td>
                                <td><?= number_format($grandTotals['female_qty'], 3) ?></td>
                                <td><?= number_format($grandTotals['female_net_kg'], 3) ?></td>
                                <td><?= number_format($grandTotals['female_net_kg'] / 1000, 3) ?></td>
                                <td><?= number_format($grandTotals['female_amount'], 3) ?></td>
                                <td><?= number_format($grandTotals['total_farmers']) ?></td>
                                <td><?= number_format($grandTotals['total_qty'], 3) ?></td>
                                <td><?= number_format($grandTotals['total_net_kg'], 3) ?></td>
                                <td><?= number_format($grandTotals['total_net_kg'] / 1000, 3) ?></td>
                                <td><?= number_format($grandTotals['total_amount'], 3) ?></td>
                                <td><?= number_format($grandTotals['farmer_organization_count']) ?></td>
                                <td><?= number_format($grandTotals['farmer_organization_members']) ?></td>
                                <td><?= number_format($grandTotals['ip_group_count']) ?></td>
                                <td><?= number_format($grandTotals['ip_group_members']) ?></td>
                            </tr>
                            <tr class="report-spacer-row" aria-hidden="true"><td colspan="20">&nbsp;</td></tr>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <th><?= e($row['region'] ?? 'Unassigned') ?></th>
                                    <td><?= $formatReportValue($row['male_count']) ?></td>
                                    <td><?= $formatReportValue($row['male_qty'], 3) ?></td>
                                    <td><?= $formatReportValue($row['male_net_kg'], 3) ?></td>
                                    <td><?= $formatMetricTons($row['male_net_kg']) ?></td>
                                    <td><?= $formatReportValue($row['male_amount'], 3) ?></td>
                                    <td><?= $formatReportValue($row['female_count']) ?></td>
                                    <td><?= $formatReportValue($row['female_qty'], 3) ?></td>
                                    <td><?= $formatReportValue($row['female_net_kg'], 3) ?></td>
                                    <td><?= $formatMetricTons($row['female_net_kg']) ?></td>
                                    <td><?= $formatReportValue($row['female_amount'], 3) ?></td>
                                    <td><?= $formatReportValue($row['total_farmers']) ?></td>
                                    <td><?= $formatReportValue($row['total_qty'], 3) ?></td>
                                    <td><?= $formatReportValue($row['total_net_kg'], 3) ?></td>
                                    <td><?= $formatMetricTons($row['total_net_kg']) ?></td>
                                    <td><?= $formatReportValue($row['total_amount'], 3) ?></td>
                                    <td><?= $formatReportValue($row['farmer_organization_count']) ?></td>
                                    <td><?= $formatReportValue($row['farmer_organization_members']) ?></td>
                                    <td><?= $formatReportValue($row['ip_group_count']) ?></td>
                                    <td><?= $formatReportValue($row['ip_group_members']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <table class="report-table expanded-weight-report <?= $isHierarchicalSummary ? 'branch-region-report' : '' ?>">
                        <thead>
                            <tr>
                                <th rowspan="3"><?php if ($reportFormat === 'branch_region'): ?>REGION / BRANCH<?php elseif ($reportFormat === 'province_summary'): ?>REGION / BRANCH / PROVINCE<?php else: ?>REGION<?php endif; ?></th>
                                <th colspan="5">INDIVIDUAL FARMERS</th>
                                <th colspan="12">FARMER GROUPS</th>
                                <th colspan="5">TOTAL</th>
                            </tr>
                            <tr>
                                <th rowspan="2">No. of Farmers</th><th rowspan="2">No. of Bags</th><th rowspan="2">Nkg</th><th rowspan="2">In MT</th><th rowspan="2">Amount Paid (NFA)</th>
                                <th colspan="6">FARMERS ORGANIZATION</th>
                                <th colspan="6">INDIGENOUS PEOPLE GROUP</th>
                                <th rowspan="2">No. of Farmers</th><th rowspan="2">No. of Bags</th><th rowspan="2">Nkg</th><th rowspan="2">In MT</th><th rowspan="2">Amount Paid (NFA)</th>
                            </tr>
                            <tr>
                                <th>No. of Groups</th><th>No. of Members</th><th>No. of Bags</th><th>Nkg</th><th>In MT</th><th>Amount Paid (NFA)</th>
                                <th>No. of Groups</th><th>No. of Members</th><th>No. of Bags</th><th>Nkg</th><th>In MT</th><th>Amount Paid (NFA)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($reportFormat === 'default'): ?>
                                <tr class="report-grand-total-row">
                                    <th>Philippines</th>
                                    <td><?= number_format($grandTotals['individual_farmers']) ?></td>
                                    <td><?= number_format($grandTotals['individual_qty'], 3) ?></td>
                                    <td><?= number_format($grandTotals['individual_net_kg'], 3) ?></td>
                                    <td><?= number_format($grandTotals['individual_net_kg'] / 1000, 3) ?></td>
                                    <td><?= number_format($grandTotals['individual_amount'], 3) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_count']) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_members']) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_qty'], 3) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_net_kg'], 3) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_net_kg'] / 1000, 3) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_amount'], 3) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_count']) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_members']) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_qty'], 3) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_net_kg'], 3) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_net_kg'] / 1000, 3) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_amount'], 3) ?></td>
                                    <td><?= number_format($grandTotals['total_farmers']) ?></td>
                                    <td><?= number_format($grandTotals['total_qty'], 3) ?></td>
                                    <td><?= number_format($grandTotals['total_net_kg'], 3) ?></td>
                                    <td><?= number_format($grandTotals['total_net_kg'] / 1000, 3) ?></td>
                                    <td><?= number_format($grandTotals['total_amount'], 3) ?></td>
                                </tr>
                                <tr class="report-spacer-row" aria-hidden="true"><td colspan="23">&nbsp;</td></tr>
                            <?php endif; ?>
                            <?php foreach ($rows as $row): ?>
                                <?php
                                $rowType = $row['row_type'] ?? '';
                                if ($reportFormat === 'province_summary') {
                                    $label = match ($rowType) {
                                        'province' => $row['province'] ?? 'Unassigned Province',
                                        'branch_total' => $row['branch'] ?? 'Unassigned Branch',
                                        default => $row['region'] ?? 'Unassigned',
                                    };
                                } elseif ($reportFormat === 'branch_region') {
                                    $label = $rowType === 'branch' ? ($row['branch'] ?? 'Unassigned Branch') : ($row['region'] ?? 'Unassigned');
                                } else {
                                    $label = $row[$scope === 'branch' ? 'region_branch' : 'region'] ?? 'Unassigned';
                                }
                                ?>
                                <tr class="<?= $rowType === 'region_total' ? 'report-region-total' : (in_array($rowType, ['branch', 'branch_total', 'province'], true) ? 'report-branch-row report-' . e($rowType) . '-row' : '') ?>">
                                    <th><?= e($label ?: 'Unassigned') ?></th>
                                    <td><?= $formatReportValue($row['individual_farmers']) ?></td>
                                    <td><?= $formatReportValue($row['individual_qty'], 3) ?></td>
                                    <td><?= $formatReportValue($row['individual_net_kg'], 3) ?></td>
                                    <td><?= $formatMetricTons($row['individual_net_kg']) ?></td>
                                    <td><?= $formatReportValue($row['individual_amount'], 3) ?></td>
                                    <td><?= $formatReportValue($row['farmer_organization_count']) ?></td>
                                    <td><?= $formatReportValue($row['farmer_organization_members']) ?></td>
                                    <td><?= $formatReportValue($row['farmer_organization_qty'], 3) ?></td>
                                    <td><?= $formatReportValue($row['farmer_organization_net_kg'], 3) ?></td>
                                    <td><?= $formatMetricTons($row['farmer_organization_net_kg']) ?></td>
                                    <td><?= $formatReportValue($row['farmer_organization_amount'], 3) ?></td>
                                    <td><?= $formatReportValue($row['ip_group_count']) ?></td>
                                    <td><?= $formatReportValue($row['ip_group_members']) ?></td>
                                    <td><?= $formatReportValue($row['ip_group_qty'], 3) ?></td>
                                    <td><?= $formatReportValue($row['ip_group_net_kg'], 3) ?></td>
                                    <td><?= $formatMetricTons($row['ip_group_net_kg']) ?></td>
                                    <td><?= $formatReportValue($row['ip_group_amount'], 3) ?></td>
                                    <td><?= $formatReportValue($row['total_farmers']) ?></td>
                                    <td><?= $formatReportValue($row['total_qty'], 3) ?></td>
                                    <td><?= $formatReportValue($row['total_net_kg'], 3) ?></td>
                                    <td><?= $formatMetricTons($row['total_net_kg']) ?></td>
                                    <td><?= $formatReportValue($row['total_amount'], 3) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php if ($reportFormat !== 'default'): ?>
                            <tfoot>
                                <tr>
                                    <th>GRAND TOTAL</th>
                                    <td><?= number_format($grandTotals['individual_farmers']) ?></td>
                                    <td><?= number_format($grandTotals['individual_qty'], 3) ?></td>
                                    <td><?= number_format($grandTotals['individual_net_kg'], 3) ?></td>
                                    <td><?= number_format($grandTotals['individual_net_kg'] / 1000, 3) ?></td>
                                    <td><?= number_format($grandTotals['individual_amount'], 3) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_count']) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_members']) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_qty'], 3) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_net_kg'], 3) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_net_kg'] / 1000, 3) ?></td>
                                    <td><?= number_format($grandTotals['farmer_organization_amount'], 3) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_count']) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_members']) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_qty'], 3) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_net_kg'], 3) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_net_kg'] / 1000, 3) ?></td>
                                    <td><?= number_format($grandTotals['ip_group_amount'], 3) ?></td>
                                    <td><?= number_format($grandTotals['total_farmers']) ?></td>
                                    <td><?= number_format($grandTotals['total_qty'], 3) ?></td>
                                    <td><?= number_format($grandTotals['total_net_kg'], 3) ?></td>
                                    <td><?= number_format($grandTotals['total_net_kg'] / 1000, 3) ?></td>
                                    <td><?= number_format($grandTotals['total_amount'], 3) ?></td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                <?php endif; ?>
            </div>
        </article>
        <?php endif; ?>

        <?php if (($_SESSION['role'] ?? '') !== 'Read-Only User'): ?>
        <section class="panel report-signatory-selector no-print" data-report-signatory-selector data-order-key="fsr-report-signatory-order-<?= e($_SESSION['user_id'] ?? 0) ?>">
            <div class="panel-head">
                <div>
                    <h2>Report Signatories</h2>
                    <p>Select the signatories, assign their roles, then drag the cards into print order.</p>
                </div>
                <a class="btn btn-outline-success btn-sm" href="index.php?page=report-settings">Manage Signatories</a>
            </div>
            <?php if (($signatories ?? []) === []): ?>
                <p class="empty-state">No signatories are enrolled. Add one in Report Settings before printing.</p>
            <?php else: ?>
                <div class="report-signatory-options">
                    <?php foreach ($signatories as $index => $signatory): ?>
                        <div class="report-signatory-option" data-signatory-option data-signatory-id="<?= e($signatory['id']) ?>" data-name="<?= e($signatory['full_name']) ?>" data-designation="<?= e($signatory['designation']) ?>">
                            <button class="signatory-select-button" type="button" draggable="true" data-signatory-toggle data-signatory-drag aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>" title="Drag to rearrange print order">
                                <span class="signatory-drag-grip" aria-hidden="true">&#8942;&#8942;</span>
                                <span class="signatory-check" aria-hidden="true">&#10003;</span>
                                <span><strong><?= e($signatory['full_name']) ?></strong><small><?= e($signatory['designation']) ?></small></span>
                            </button>
                            <label>
                                <span>Report role</span>
                                <select class="form-select form-select-sm" data-signatory-role>
                                    <option value="" selected></option>
                                    <option value="Prepared by:">Prepared by:</option>
                                    <option value="Reviewed by:">Reviewed by:</option>
                                    <option value="Approved by:">Approved by:</option>
                                </select>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="signatory-selector-note">Drag the signatory buttons to set their left-to-right print order. Selected buttons appear pressed.</p>
            <?php endif; ?>
        </section>
        <?php endif; ?>
    <?php endif; ?>
</section>
