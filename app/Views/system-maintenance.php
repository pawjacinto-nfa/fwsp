<?php $isMaintenanceScheduled = !empty($maintenanceSchedule) && strtotime($maintenanceSchedule) > time(); ?>
<section class="workspace-section">
    <div class="section-head compact">
        <div>
            <p class="eyebrow">System Admin</p>
            <h3>System Maintenance</h3>
            <p class="mb-0 text-muted">Control planned system availability and inspect read-only database metadata.</p>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link <?= $activeTab === 'maintenance' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#maintenance-panel" type="button" role="tab">System Maintenance</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link <?= $activeTab === 'database' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#database-panel" type="button" role="tab">Database Management</button></li>
    </ul>
    <div class="tab-content">
        <section class="tab-pane fade <?= $activeTab === 'maintenance' ? 'show active' : '' ?>" id="maintenance-panel" role="tabpanel">
            <div class="panel">
                <h4 class="h5">System Maintenance</h4>
                <p class="text-muted">Turn on maintenance immediately, or set a future schedule. A future schedule warns users at sign-in and activates maintenance automatically at the selected time.</p>
                <form method="post" class="maintenance-control mb-0" data-maintenance-form>
                    <input type="hidden" name="action" value="maintenance-mode">
                    <input type="hidden" name="maintenance_enabled" value="0">
                    <div class="maintenance-control-title"><strong id="maintenanceModeLabel">System Maintenance</strong><span class="badge <?= !empty($maintenanceModeEnabled) || $isMaintenanceScheduled ? 'text-bg-warning' : 'text-bg-success' ?>" data-maintenance-status><?= $isMaintenanceScheduled ? 'SCHEDULED' : (!empty($maintenanceModeEnabled) ? 'ON' : 'OFF') ?></span></div>
                    <div class="maintenance-switch"><span>OFF</span><div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" name="maintenance_enabled" value="1" aria-labelledby="maintenanceModeLabel" data-maintenance-toggle <?= !empty($maintenanceModeEnabled) || $isMaintenanceScheduled ? 'checked' : '' ?>></div><span>ON</span></div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-5"><label class="form-label" for="maintenanceDate">Scheduled date</label><input class="form-control" id="maintenanceDate" type="date" name="maintenance_date" value="<?= !empty($maintenanceSchedule) ? e(date('Y-m-d', strtotime($maintenanceSchedule))) : '' ?>"></div>
                        <div class="col-md-4"><label class="form-label" for="maintenanceTime">Scheduled time</label><input class="form-control" id="maintenanceTime" type="time" name="maintenance_time" value="<?= !empty($maintenanceSchedule) ? e(date('H:i', strtotime($maintenanceSchedule))) : '' ?>"></div>
                        <div class="col-md-3 d-flex align-items-end gap-2"><button class="btn btn-success flex-fill" type="submit">Save schedule</button><?php if ($isMaintenanceScheduled): ?><button class="btn btn-outline-danger" type="submit" name="clear_maintenance_schedule" value="1">Cancel</button><?php endif; ?></div>
                    </div>
                </form>
            </div>
            <div class="panel mt-3">
                <h4 class="h5">Transaction Controls</h4>
                <p class="text-muted">By default, farmers without an RSBSA or MAO Certification can only have one delivery transaction.</p>
                <form method="post" class="maintenance-control mb-0" data-module-maintenance-form>
                    <input type="hidden" name="action" value="no-control-number-transactions">
                    <input type="hidden" name="allow_no_control_number_transactions" value="0">
                    <div class="maintenance-control-title"><div><strong id="noControlNumberTransactionsLabel">Allow Transactions from Farmers without control numbers</strong><small class="d-block text-muted">When ON, orange-tagged farmers may transact even after their first delivery.</small></div><span class="badge <?= !empty($allowNoControlNumberTransactions) ? 'text-bg-success' : 'text-bg-warning' ?>"><?= !empty($allowNoControlNumberTransactions) ? 'ON' : 'OFF' ?></span></div>
                    <div class="maintenance-switch"><span>OFF</span><div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" name="allow_no_control_number_transactions" value="1" aria-labelledby="noControlNumberTransactionsLabel" data-module-maintenance-toggle <?= !empty($allowNoControlNumberTransactions) ? 'checked' : '' ?>></div><span>ON</span></div>
                </form>
                <form method="post" class="maintenance-control mt-3 mb-0" data-module-maintenance-form>
                    <input type="hidden" name="action" value="possible-duplicate-warning-setting">
                    <input type="hidden" name="possible_duplicate_warnings" value="0">
                    <div class="maintenance-control-title"><div><strong id="possibleDuplicateWarningsLabel">Enable Possible Duplicates Warning</strong><small class="d-block text-muted">Warn encoders before saving a farmer profile or individual delivery that matches an active farmer name or RSBSA Number.</small></div><span class="badge <?= !empty($possibleDuplicateWarningsEnabled) ? 'text-bg-warning' : 'text-bg-success' ?>"><?= !empty($possibleDuplicateWarningsEnabled) ? 'ON' : 'OFF' ?></span></div>
                    <div class="maintenance-switch"><span>OFF</span><div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" name="possible_duplicate_warnings" value="1" aria-labelledby="possibleDuplicateWarningsLabel" data-module-maintenance-toggle <?= !empty($possibleDuplicateWarningsEnabled) ? 'checked' : '' ?>></div><span>ON</span></div>
                </form>
                <div class="accordion mt-3" id="annualBagLimitAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="annualBagLimitHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#annualBagLimitPanel" aria-expanded="false" aria-controls="annualBagLimitPanel">
                                Annual 400-Bag Limit Override <span class="badge ms-2 <?= !empty($allowAnnualBagLimitExceeded) ? 'text-bg-warning' : 'text-bg-success' ?>"><?= !empty($allowAnnualBagLimitExceeded) ? 'OVERRIDE ON' : 'ENFORCED' ?></span>
                            </button>
                        </h2>
                        <div id="annualBagLimitPanel" class="accordion-collapse collapse" aria-labelledby="annualBagLimitHeading" data-bs-parent="#annualBagLimitAccordion">
                            <div class="accordion-body">
                                <p class="text-muted">When enabled, encoders may save individual farmer deliveries that bring a calendar-year total above 400 bags. Farmer Organization/IP Group deliveries are not affected.</p>
                                <form method="post" class="maintenance-control mb-3" data-module-maintenance-form>
                                    <input type="hidden" name="action" value="annual-bag-limit-setting">
                                    <input type="hidden" name="allow_annual_bag_limit_exceeded" value="0">
                                    <div class="maintenance-control-title"><div><strong id="annualBagLimitLabel">Allow transactions exceeding 400 bags per year</strong><small class="d-block text-muted">Use only when an authorized exception permits the annual limit to be exceeded.</small></div><span class="badge <?= !empty($allowAnnualBagLimitExceeded) ? 'text-bg-warning' : 'text-bg-success' ?>"><?= !empty($allowAnnualBagLimitExceeded) ? 'ON' : 'OFF' ?></span></div>
                                    <div class="maintenance-switch"><span>OFF</span><div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" name="allow_annual_bag_limit_exceeded" value="1" aria-labelledby="annualBagLimitLabel" data-module-maintenance-toggle <?= !empty($allowAnnualBagLimitExceeded) ? 'checked' : '' ?>></div><span>ON</span></div>
                                </form>
                                <h3 class="h6">Farmers currently exceeding 400 bags</h3>
                                <?php if (empty($annualBagLimitExceededDetails)): ?>
                                    <p class="text-muted mb-0">No individual farmer annual totals currently exceed 400 bags.</p>
                                <?php else: ?>
                                    <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Farmer</th><th>Year</th><th>Annual bags</th><th>Date</th><th>WSR No.</th><th>Transaction bags</th><th>Net kg</th><th>Total amount</th></tr></thead><tbody>
                                    <?php foreach ($annualBagLimitExceededDetails as $detail): ?>
                                        <tr><td><a href="index.php?page=farmer-view&id=<?= e($detail['farmer_id']) ?>"><?= e($detail['farmer_name']) ?></a><small class="d-block text-muted"><?= e($detail['farmer_key'] ?: $detail['rsbsa']) ?></small></td><td><?= e($detail['delivery_year']) ?></td><td class="fw-semibold text-danger"><?= number_format((float) $detail['annual_bags'], 3) ?></td><td><?= e($detail['delivery_date']) ?></td><td><?= e($detail['wsr']) ?></td><td><?= number_format((float) $detail['bags'], 3) ?></td><td><?= number_format((float) $detail['net_kg'], 3) ?></td><td><?= number_format((float) $detail['total_amount'], 3) ?></td></tr>
                                    <?php endforeach; ?>
                                    </tbody></table></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel mt-3">
                <h4 class="h5">Modular Restrictions</h4>
                <p class="text-muted">System Admin accounts retain access. Turning a module off blocks the menu and direct access for other encoders.</p>
                <?php foreach ([
                    ['encoding', 'Encoding', 'Farmer Profile, transactions, and editing', !empty($encodingEnabled)],
                    ['delivery_schedule', 'Delivery Schedule', 'Delivery Schedule calendar, creation, confirmation, and status actions', !empty($deliveryScheduleEnabled)],
                ] as [$module, $label, $description, $enabled]): ?>
                    <form method="post" class="maintenance-control mb-3" data-module-maintenance-form>
                        <input type="hidden" name="action" value="module-maintenance"><input type="hidden" name="module" value="<?= e($module) ?>"><input type="hidden" name="module_enabled" value="0">
                        <div class="maintenance-control-title"><div><strong id="<?= e($module) ?>MaintenanceLabel"><?= e($label) ?></strong><small class="d-block text-muted"><?= e($description) ?></small></div><span class="badge <?= $enabled ? 'text-bg-success' : 'text-bg-warning' ?>"><?= $enabled ? 'ON' : 'OFF' ?></span></div>
                        <div class="maintenance-switch"><span>OFF</span><div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" name="module_enabled" value="1" aria-labelledby="<?= e($module) ?>MaintenanceLabel" data-module-maintenance-toggle <?= $enabled ? 'checked' : '' ?>></div><span>ON</span></div>
                    </form>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="tab-pane fade <?= $activeTab === 'database' ? 'show active' : '' ?>" id="database-panel" role="tabpanel">
            <?php $embeddedDatabaseManagement = true; require BASE_PATH . '/app/Views/database-management.php'; ?>
        </section>
    </div>
</section>
