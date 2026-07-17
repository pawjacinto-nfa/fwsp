<section class="workspace-section">
    <div class="section-head compact">
        <div><p class="eyebrow">Account</p><h3>Account Management</h3></div>
    </div>
    <?php if (!$user): ?>
        <div class="panel"><p class="mb-0">Please log in to manage your account.</p></div>
    <?php else: ?>
        <form method="post" enctype="multipart/form-data" class="panel form-panel" data-account-form>
            <input type="hidden" name="action" value="account">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Profile Image</label>
                    <?php if (!empty($user['profile_image'])): ?><img class="account-preview" src="<?= e($user['profile_image']) ?>" alt=""><?php endif; ?>
                    <input type="file" name="profile_image" accept="image/*" class="form-control">
                </div>
                <div class="col-md-3"><label class="form-label">Full Name</label><input required name="full_name" value="<?= e($user['full_name']) ?>" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Email</label><input required type="email" name="email" value="<?= e($user['email']) ?>" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Contact Number</label><input name="contact_number" value="<?= e($user['contact_number']) ?>" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Designation</label><input name="designation" value="<?= e($user['designation']) ?>" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">New Password</label><input type="password" name="password" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Password Confirmation</label><input type="password" name="password_confirmation" class="form-control"></div>
            </div>
            <div class="form-section-title">Location Assignment</div>
            <div class="row g-3">
                <?php
                $locationClass = 'col-md-3';
                $locationRequired = false;
                $locationIncludeAll = false;
                $locationValues = [
                    'region_id' => $user['region_id'] ?? '',
                    'branch_id' => $user['branch_id'] ?? '',
                    'province_id' => $user['province_id'] ?? '',
                    'warehouse_id' => $user['warehouse_id'] ?? '',
                ];
                $locationLabelWarehouse = 'Facility Name';
                require BASE_PATH . '/app/Views/partials/location-selects.php';
                ?>
            </div>
            <?php if (in_array($user['role'] ?? '', ['Warehouse Personnel', 'System Admin'], true)): ?>
                <div class="form-section-title">Offline work</div>
                <div class="offline-setting">
                    <div><strong>Enable offline mode</strong><p>Install the delivery forms on this device. Pending delivery inputs remain encrypted in this browser until you upload them.</p></div>
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="offline_enabled" value="1" id="offlineEnabled" data-offline-enable <?= !empty($user['offline_enabled']) ? 'checked' : '' ?>><label class="form-check-label" for="offlineEnabled">Available on this device</label></div>
                </div>
            <?php endif; ?>
            <div class="form-actions"><button class="btn btn-success" type="submit">Update Account</button></div>
        </form>
    <?php endif; ?>
</section>
