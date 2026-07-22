<section class="workspace-section">
    <div class="section-head compact">
        <div><p class="eyebrow">Account</p><h3>Account Management</h3></div>
    </div>
    <?php if (!$user): ?>
        <div class="panel"><p class="mb-0">Please log in to manage your account.</p></div>
    <?php else: ?>
        <ul class="nav nav-tabs account-setting-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#account-settings" type="button">Account Settings</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#display-settings" type="button">Display Settings</button></li>
        </ul>
        <div class="tab-content">
        <div class="tab-pane fade show active" id="account-settings">
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
        </div>
        <div class="tab-pane fade" id="display-settings">
            <form method="post" enctype="multipart/form-data" class="panel form-panel">
                <input type="hidden" name="action" value="display-photo-submit">
                <div class="section-head compact"><div><p class="eyebrow">Landing Page</p><h4>Feature your photo</h4><p class="mb-0 text-muted">Submit one original 4K photo for System Admin review. Landscape or portrait images must have a minimum 3,840 × 2,160 pixel equivalent.</p></div></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="displayPhoto">4K Photo</label><input class="form-control" id="displayPhoto" name="display_photo" type="file" accept="image/jpeg,image/png,image/webp" required><div class="form-text">JPG, PNG, or WebP; up to 30 MB.</div></div>
                    <div class="col-md-6"><label class="form-label" for="displayTitle">Title</label><input class="form-control" id="displayTitle" name="title" maxlength="160" required></div>
                    <div class="col-md-6"><label class="form-label" for="photographerName">Name of Photographer</label><input class="form-control" id="photographerName" name="photographer_name" maxlength="160" required></div>
                    <div class="col-md-6"><label class="form-label" for="photoLocation">Location</label><input class="form-control" id="photoLocation" name="location" maxlength="160" placeholder="e.g. Banaue, Ifugao"></div>
                </div>
                <div class="form-actions"><button class="btn btn-success" type="submit">Submit for Review</button></div>
            </form>
        </div>
        </div>
    <?php endif; ?>
</section>
