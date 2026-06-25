<?php
$mode = $mode ?? 'index';
$individualTransactions = [];
$organizationTransactions = [];
if ($mode === 'transactions') {
    $individualTransactions = array_values(array_filter($transactions ?? [], fn (array $transaction): bool => ($transaction['type'] ?? '') === 'Individual'));
    $organizationTransactions = array_values(array_filter($transactions ?? [], fn (array $transaction): bool => ($transaction['type'] ?? '') === 'Farmer Organization'));
}
?>
<section class="workspace-section">
    <div class="section-head compact">
        <div>
            <p class="eyebrow">Record Viewing</p>
            <h3><?= $mode === 'transactions' ? 'Transaction Records' : ($mode === 'farmers' ? 'Farmers Records' : 'Records') ?></h3>
        </div>
        <div class="quick-actions">
            <a class="btn btn-outline-success" href="index.php?page=farmers">Farmers</a>
            <a class="btn btn-outline-success" href="index.php?page=transactions">Transactions</a>
        </div>
    </div>

    <?php if ($mode === 'index'): ?>
        <div class="dashboard-actions compact-actions">
            <a class="action-square" href="index.php?page=farmers">
                <span class="activity-image-stack">
                    <img class="activity-image base" src="assets/images/activity-buttons/button1-a1-v.png" alt="">
                    <img class="activity-image hover" src="assets/images/activity-buttons/button1-a1-h.png" alt="">
                </span>
                <strong>Farmers Records</strong>
            </a>
            <a class="action-square" href="index.php?page=transactions">
                <span class="activity-image-stack">
                    <img class="activity-image base" src="assets/images/activity-buttons/button2-a2-v.png" alt="">
                    <img class="activity-image hover" src="assets/images/activity-buttons/button2-a2-h.png" alt="">
                </span>
                <strong>Transaction Records</strong>
            </a>
        </div>
    <?php else: ?>
        <form method="get" class="panel filter-panel">
            <input type="hidden" name="page" value="<?= $mode === 'transactions' ? 'transactions' : 'farmers' ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label">Search</label><input name="q" value="<?= e($filters['q'] ?? '') ?>" class="form-control" placeholder="Name, RSBSA, WSR"></div>
                <?php if (in_array($mode, ['farmers', 'transactions'], true)): ?>
                    <?php
                    $locationClass = 'col-md-2';
                    $locationRequired = false;
                    $locationIncludeAll = true;
                    $locationValues = $filters;
                    $locationLabelWarehouse = 'Facility';
                    require BASE_PATH . '/app/Views/partials/location-selects.php';
                    ?>
                <?php endif; ?>
                <?php if ($mode === 'transactions'): ?>
                    <div class="col-md-2">
                        <label class="form-label">Procurement</label>
                        <div class="filter-check-row">
                            <?php foreach (['In-Warehouse', 'Mobile Procurement'] as $procurement): ?>
                                <label><input type="checkbox" name="procurement[]" value="<?= e($procurement) ?>" <?= in_array($procurement, (array) ($filters['procurement'] ?? []), true) ? 'checked' : '' ?>> <?= e($procurement) ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="col-md-1"><label class="form-label">From</label><input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="form-control"></div>
                <div class="col-md-1"><label class="form-label">To</label><input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="form-control"></div>
                <div class="col-md-1"><button class="btn btn-success w-100" type="submit">Search</button></div>
            </div>
        </form>
    <?php endif; ?>

    <?php if (($selectedTransaction ?? null)): ?>
        <div class="modal fade" id="transactionDetailModal" tabindex="-1" aria-labelledby="transactionDetailModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <p class="eyebrow mb-1">Transaction Details</p>
                            <h3 class="modal-title h5" id="transactionDetailModalTitle"><?= e($selectedTransaction['wsr']) ?></h3>
                        </div>
                        <a class="btn-close" href="index.php?page=transactions" aria-label="Close"></a>
                    </div>
                    <div class="modal-body">
                        <dl class="detail-grid">
                            <div><dt>WSR Number</dt><dd><?= e($selectedTransaction['wsr']) ?></dd></div>
                            <div><dt>Seller</dt><dd><?= e(trim($selectedTransaction['farmer_name']) ?: $selectedTransaction['fo_name']) ?></dd></div>
                            <div><dt>Type</dt><dd><?= e($selectedTransaction['seller_type']) ?> / <?= e($selectedTransaction['procurement_type']) ?></dd></div>
                            <div><dt>Delivery Date</dt><dd><?= e($selectedTransaction['delivery_date']) ?></dd></div>
                            <div><dt>Representative</dt><dd><?= e($selectedTransaction['representative_name'] ?: 'N/A') ?></dd></div>
                            <div><dt>Total Farmer-Members</dt><dd><?= number_format((int) ($selectedTransaction['total_members'] ?? 0)) ?></dd></div>
                            <div><dt>Verified Farm Area</dt><dd><?= e($selectedTransaction['verified_farm_area'] ?? 'N/A') ?></dd></div>
                            <div><dt>Bags</dt><dd><?= number_format((float) $selectedTransaction['bags_50kg']) ?></dd></div>
                            <div><dt>Net Kilogram</dt><dd><?= number_format((float) $selectedTransaction['net_kilogram'], 2) ?></dd></div>
                            <div><dt>Price/Kg</dt><dd><?= number_format((float) $selectedTransaction['price_per_kilogram'], 2) ?></dd></div>
                            <div><dt>Amount Paid</dt><dd><?= number_format((float) $selectedTransaction['net_kilogram'] * (float) $selectedTransaction['price_per_kilogram'], 2) ?></dd></div>
                            <div><dt>Location</dt><dd><?= e($selectedTransaction['region_name'] . ' / ' . $selectedTransaction['branch_name'] . ' / ' . $selectedTransaction['province_name'] . ' / ' . $selectedTransaction['warehouse_name']) ?></dd></div>
                        </dl>
                        <?php if (($selectedTransaction['seller_type'] ?? '') === 'Farmer Organization'): ?>
                            <div class="detail-subsection">
                                <h4>Farmer-Members Included in This Delivery</h4>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead><tr><th>RSBSA</th><th>Full Name</th><th>Farmer Organization</th></tr></thead>
                                        <tbody>
                                        <?php foreach (($selectedTransaction['delivered_members'] ?? []) as $member): ?>
                                            <tr>
                                                <td><?= e($member['rsbsa']) ?></td>
                                                <td><?= e($member['full_name']) ?></td>
                                                <td><?= e($member['organization']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (($selectedTransaction['delivered_members'] ?? []) === []): ?>
                                            <tr><td colspan="3" class="text-center text-muted">No farmer-members were linked to this FO delivery transaction.</td></tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-outline-success" href="index.php?page=transactions">Close</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($mode === 'farmers'): ?>
        <section class="panel table-section">
            <div class="panel-head">
                <h2>Farmers Encoded</h2>
                <div class="quick-actions">
                    <button class="btn btn-sm btn-outline-success" type="button" data-print-target="farmers-print-area" data-report-title="Farmers Records Report">Print Preview</button>
                    <button class="btn btn-sm btn-warning" type="button" data-print-target="farmers-print-area" data-report-title="Farmers Records Report" data-print-mode="pdf">PDF Download</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle sortable-table" id="farmers-print-area">
                    <thead><tr><th>RSBSA</th><th>Full Name</th><th>Sex</th><th>Age</th><th>Location</th><th>SOGIE</th><th>Sector/s</th><th>Farmer Organization</th></tr></thead>
                    <tbody>
                    <?php foreach ($farmers as $farmer): ?>
                        <?php
                        $fullName = trim(($farmer['first_name'] ?? '') . ' ' . ($farmer['middle_name'] ?? '') . ' ' . ($farmer['last_name'] ?? ''));
                        $location = trim(implode(' / ', array_filter([
                            $farmer['region_name'] ?? '',
                            $farmer['branch_name'] ?? '',
                            $farmer['province_name'] ?? '',
                            $farmer['warehouse_name'] ?? '',
                        ])));
                        $sogie = implode(', ', array_filter($farmer['gender_orientation'] ?? []));
                        $sectors = implode(', ', array_filter($farmer['sector'] ?? []));
                        ?>
                        <tr>
                            <td><?= e($farmer['rsbsa']) ?></td>
                            <td><a class="table-profile-link" href="index.php?page=farmer-view&id=<?= e($farmer['id']) ?>"><?= e($fullName) ?></a></td>
                            <td><?= e($farmer['sex']) ?></td>
                            <td><?= e($farmer['age'] ?? '') ?></td>
                            <td class="table-location-cell"><?= e($location) ?></td>
                            <td><?= e($sogie ?: 'N/A') ?></td>
                            <td><?= e($sectors ?: 'N/A') ?></td>
                            <td><?= e($farmer['organization'] ?: 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($mode === 'transactions'): ?>
        <section class="panel table-section">
            <div class="panel-head">
                <h2>Individual Farmer Transactions</h2>
                <div class="quick-actions">
                    <button class="btn btn-sm btn-outline-success" type="button" data-print-target="individual-transactions-print-area" data-report-title="Individual Farmer Transaction Records Report">Print Preview</button>
                    <button class="btn btn-sm btn-warning" type="button" data-print-target="individual-transactions-print-area" data-report-title="Individual Farmer Transaction Records Report" data-print-mode="pdf">PDF Download</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle sortable-table" id="individual-transactions-print-area">
                    <thead><tr><th>WSR</th><th>Seller</th><th>Type</th><th>Date</th><th>Province</th><th>Facility</th><th>Bags</th><th>Net Kg</th><th>Amount</th><th class="print-exclude">Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($individualTransactions as $transaction): ?>
                        <tr>
                            <td><?= e($transaction['wsr']) ?></td>
                            <td><?= e(trim($transaction['farmer_name'])) ?></td>
                            <td><?= e($transaction['type']) ?> / <?= e($transaction['procurement']) ?></td>
                            <td><?= e($transaction['delivery_date']) ?></td>
                            <td><?= e($transaction['province_name']) ?></td>
                            <td><?= e($transaction['warehouse_name']) ?></td>
                            <td><?= number_format((float) $transaction['bags']) ?></td>
                            <td><?= number_format((float) $transaction['net_kg'], 2) ?></td>
                            <td><?= number_format((float) $transaction['net_kg'] * (float) $transaction['price'], 2) ?></td>
                            <td class="print-exclude"><a class="btn btn-sm btn-outline-success" href="index.php?page=transactions&transaction_id=<?= e($transaction['id']) ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($individualTransactions === []): ?>
                        <tr><td colspan="10" class="text-center text-muted">No individual farmer transactions found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel table-section">
            <div class="panel-head">
                <h2>Farmer Organization Delivery Transactions</h2>
                <div class="quick-actions">
                    <button class="btn btn-sm btn-outline-success" type="button" data-print-target="organization-transactions-print-area" data-report-title="Farmer Organization Delivery Transaction Records Report">Print Preview</button>
                    <button class="btn btn-sm btn-warning" type="button" data-print-target="organization-transactions-print-area" data-report-title="Farmer Organization Delivery Transaction Records Report" data-print-mode="pdf">PDF Download</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle sortable-table" id="organization-transactions-print-area">
                    <thead><tr><th>WSR</th><th>Seller</th><th>Type</th><th>Date</th><th>Province</th><th>Facility</th><th>Bags</th><th>Net Kg</th><th>Amount</th><th class="print-exclude">Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($organizationTransactions as $transaction): ?>
                        <tr>
                            <td><?= e($transaction['wsr']) ?></td>
                            <td><?= e($transaction['fo_name']) ?></td>
                            <td><?= e($transaction['type']) ?> / <?= e($transaction['procurement']) ?></td>
                            <td><?= e($transaction['delivery_date']) ?></td>
                            <td><?= e($transaction['province_name']) ?></td>
                            <td><?= e($transaction['warehouse_name']) ?></td>
                            <td><?= number_format((float) $transaction['bags']) ?></td>
                            <td><?= number_format((float) $transaction['net_kg'], 2) ?></td>
                            <td><?= number_format((float) $transaction['net_kg'] * (float) $transaction['price'], 2) ?></td>
                            <td class="print-exclude"><a class="btn btn-sm btn-outline-success" href="index.php?page=transactions&transaction_id=<?= e($transaction['id']) ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($organizationTransactions === []): ?>
                        <tr><td colspan="10" class="text-center text-muted">No farmer organization delivery transactions found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</section>
