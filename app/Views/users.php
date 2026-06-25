<section class="workspace-section">
    <div class="section-head compact">
        <div><p class="eyebrow">Super Admin</p><h3>User Control Management</h3></div>
    </div>
    <?php if (($_SESSION['role'] ?? '') !== 'Super Admin'): ?>
        <div class="panel"><p class="mb-0">Only Super Admin can access this page.</p></div>
    <?php else: ?>
        <section class="panel table-section">
            <div class="panel-head">
                <h2>Users</h2>
            </div>
            <div class="table-responsive">
            <table class="table align-middle" id="users-table" data-page-size="10">
                <thead><tr><th>Username</th><th>Name</th><th>Office</th><th>Designation</th><th>Status</th><th>Role</th><th>Password Reset</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($users as $account): ?>
                    <?php $formId = 'userAccessForm' . (int) $account['id']; ?>
                    <tr>
                        <td><?= e($account['username']) ?></td>
                        <td><?= e($account['full_name']) ?></td>
                        <td><?= e(str_replace("\n", ' / ', \App\Models\User::locationLabel($account))) ?></td>
                        <td><?= e($account['designation']) ?></td>
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

        <section class="panel table-section">
            <div class="panel-head">
                <h2>Audit Logs</h2>
            </div>
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
    <?php endif; ?>
</section>
