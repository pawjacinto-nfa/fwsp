<section class="workspace-section">
    <div class="section-head compact">
        <div><p class="eyebrow">System Admin</p><h3>User Control Management</h3></div>
    </div>
    <?php if (($_SESSION['role'] ?? '') !== 'System Admin'): ?>
        <div class="panel"><p class="mb-0">Only System Admin can access this page.</p></div>
    <?php else: ?>
        <ul class="nav nav-tabs mb-3" id="userControlTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="users-tab" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#users-panel" aria-controls="users-panel" aria-selected="true">Users</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="audit-logs-tab" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#audit-logs-panel" aria-controls="audit-logs-panel" aria-selected="false">Audit Logs</button>
            </li>
        </ul>

        <div class="tab-content" id="userControlTabContent">
            <section class="tab-pane fade show active panel table-section" id="users-panel" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <form method="post" id="bulkUserAccessForm" class="row g-2 align-items-end mb-3">
                    <input type="hidden" name="action" value="user-access-bulk">
                    <div class="col-md-4"><label class="form-label" for="userTableFilter">Filter users</label><input id="userTableFilter" class="form-control" type="search" placeholder="Search users, office, status, or role" data-table-filter="users-table"></div>
                    <div class="col-sm-4 col-md-2"><label class="form-label" for="bulkStatus">Set status</label><select id="bulkStatus" name="bulk_status" class="form-select"><option value="">No change</option><option>Pending</option><option>Active</option><option>Disabled</option></select></div>
                    <div class="col-sm-4 col-md-3"><label class="form-label" for="bulkRole">Set role</label><select id="bulkRole" name="bulk_role" class="form-select"><option value="">No change</option><?php foreach ($roles as $role): ?><option><?= e($role) ?></option><?php endforeach; ?></select></div>
                    <div class="col-sm-4 col-md-3"><button class="btn btn-success w-100" type="submit">Apply to selected users</button></div>
                </form>
                <div class="table-responsive">
                <table class="table align-middle" id="users-table" data-page-size="10">
                <thead><tr><th data-no-sort="true"><input class="form-check-input" type="checkbox" aria-label="Select all users" data-select-all="users-table"></th><th>Username</th><th>Name</th><th>Office</th><th>Designation</th><th>Created</th><th>Status</th><th>Role</th><th>Password Reset</th><th data-no-sort="true">Action</th></tr></thead>
                <tbody>
                <?php foreach ($users as $account): ?>
                    <?php $formId = 'userAccessForm' . (int) $account['id']; ?>
                    <tr>
                        <td><input class="form-check-input" type="checkbox" name="user_ids[]" value="<?= (int) $account['id'] ?>" form="bulkUserAccessForm" aria-label="Select <?= e($account['username']) ?>"></td>
                        <td><?= e($account['username']) ?></td>
                        <td><?= e($account['full_name']) ?></td>
                        <td class="user-office-cell"><?= e(str_replace("\n", ' / ', \App\Models\User::locationLabel($account))) ?></td>
                        <td><?= e($account['designation']) ?></td>
                        <td data-sort-value="<?= e($account['created_at']) ?>"><?= e(date('M j, Y', strtotime($account['created_at']))) ?></td>
                        <td data-sort-value="<?= e($account['status']) ?>"><select name="status" form="<?= e($formId) ?>" class="form-select"><option <?= $account['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option><option <?= $account['status'] === 'Active' ? 'selected' : '' ?>>Active</option><option <?= $account['status'] === 'Disabled' ? 'selected' : '' ?>>Disabled</option></select></td>
                        <td data-sort-value="<?= e($account['role']) ?>"><select name="role" form="<?= e($formId) ?>" class="form-select"><?php foreach ($roles as $role): ?><option <?= $account['role'] === $role ? 'selected' : '' ?>><?= e($role) ?></option><?php endforeach; ?></select></td>
                        <td data-sort-value="<?= e($account['password_reset_status'] ?? '') ?>">
                            <?php if (($account['password_reset_status'] ?? '') === 'Requested'): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="action" value="password-reset-approve">
                                    <input type="hidden" name="user_id" value="<?= e($account['id']) ?>">
                                    <button class="btn btn-sm btn-warning" type="submit">Reset Password</button>
                                </form>
                            <?php elseif (($account['password_reset_status'] ?? '') === 'Approved'): ?>
                                <span class="badge text-bg-success">Approved</span>
                            <?php else: ?>
                                <span class="text-muted">No request</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" id="<?= e($formId) ?>">
                                <input type="hidden" name="action" value="user-access">
                                <input type="hidden" name="user_id" value="<?= e($account['id']) ?>">
                                <button class="btn btn-sm btn-success" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                </table>
                </div>
            </section>

            <section class="tab-pane fade panel table-section" id="audit-logs-panel" role="tabpanel" aria-labelledby="audit-logs-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table align-middle" id="audit-logs-table" data-page-size="10">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>User</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach (($auditLogs ?? []) as $log): ?>
                            <tr>
                                <td data-sort-value="<?= e($log['sortable_created_at']) ?>"><?= e($log['created_at']) ?></td>
                                <td><?= e($log['username']) ?></td>
                                <td><?= e($log['full_name']) ?></td>
                                <td><?= e($log['action']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (($auditLogs ?? []) === []): ?>
                            <tr><td colspan="4" class="text-muted">No audit logs have been recorded yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    <?php endif; ?>
</section>
