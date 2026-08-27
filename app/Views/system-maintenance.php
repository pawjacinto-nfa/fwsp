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
