<section class="workspace-section">
    <div class="section-head compact">
        <div><p class="eyebrow">System Admin · User Control Management</p><h3>User Profile</h3><p class="text-muted mb-0">View and edit the profile for <?= e($profile['username']) ?>.</p></div>
        <a class="btn btn-outline-secondary" href="index.php?page=users">Back to User Control</a>
    </div>
    <div class="panel mb-3">
        <div class="row g-3">
            <div class="col-md-3"><strong>Username / Employee Number</strong><div><?= e($profile['username']) ?></div></div>
            <div class="col-md-3"><strong>Role</strong><div><?= e($profile['role']) ?></div></div>
            <div class="col-md-3"><strong>Status</strong><div><?= e($profile['status']) ?></div></div>
            <div class="col-md-3"><strong>Registered</strong><div><?= e($profile['created_at']) ?></div></div>
        </div>
        <?php if (!empty($profile['deactivation_reason'])): ?><p class="mt-3 mb-0"><strong>Deactivation reason:</strong> <?= e($profile['deactivation_reason']) ?></p><?php endif; ?>
    </div>
    <form method="post" action="index.php?page=user-profile&amp;id=<?= (int) $profile['id'] ?>" enctype="multipart/form-data" class="panel form-panel">
        <input type="hidden" name="action" value="admin-user-profile">
        <input type="hidden" name="user_id" value="<?= (int) $profile['id'] ?>">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="adminProfileImage" class="form-label">Profile Image</label>
                <?php if (!empty($profile['profile_image'])): ?><img class="account-preview" src="<?= e($profile['profile_image']) ?>" alt="User profile photo"><?php endif; ?>
                <input id="adminProfileImage" name="profile_image" type="file" accept="image/jpeg,image/png,image/webp" class="form-control">
                <div class="form-text">JPG, PNG, or WebP, up to 8 MB.</div>
            </div>
            <?php foreach (['full_name'=>['Full Name',160], 'email'=>['Registered Email',160], 'contact_number'=>['Contact Number',40], 'designation'=>['Designation',120]] as $field=>[$label,$limit]): ?>
                <div class="col-md-<?= $field === 'designation' ? '6' : '3' ?>"><label class="form-label" for="admin-<?= e($field) ?>"><?= e($label) ?></label><input id="admin-<?= e($field) ?>" class="form-control" name="<?= e($field) ?>" type="<?= $field === 'email' ? 'email' : 'text' ?>" maxlength="<?= $limit ?>" value="<?= e($profile[$field] ?? '') ?>" <?= in_array($field,['full_name','email'],true) ? 'required' : '' ?>><?php if ($field === 'email'): ?><div class="form-text">Password reset emails are sent to this address.</div><?php endif; ?></div>
            <?php endforeach; ?>
            <div class="col-md-6"><label for="adminOfficeScope" class="form-label">Office Type</label><select id="adminOfficeScope" name="office_scope" class="form-select"><option value="field" <?= ($profile['office_scope'] ?? 'field') === 'field' ? 'selected' : '' ?>>Field Office</option><option value="central" <?= ($profile['office_scope'] ?? '') === 'central' ? 'selected' : '' ?>>Central Office</option></select></div>
        </div>
        <div class="form-section-title">Field Office Assignment</div>
        <p class="text-muted small">Used when Office Type is Field Office.</p>
        <div class="row g-3">
            <?php $locationValues = $profile; $locationRequired = false; $locationIncludeAll = false; require BASE_PATH . '/app/Views/partials/location-selects.php'; ?>
        </div>
        <div class="form-section-title">Central Office Assignment</div>
        <p class="text-muted small">Used when Office Type is Central Office. Select a division within its department and a unit within its division.</p>
        <div class="row g-3">
            <?php foreach ([['central_department_id','Department',\App\Models\CentralOffice::departments()], ['central_division_id','Division',\App\Models\CentralOffice::divisions()], ['central_unit_id','Unit',\App\Models\CentralOffice::units()]] as [$field,$label,$options]): ?>
                <div class="col-md-4"><label class="form-label" for="admin-<?= e($field) ?>"><?= e($label) ?></label><select id="admin-<?= e($field) ?>" name="<?= e($field) ?>" class="form-select"><option value="">Not assigned</option><?php foreach ($options as $option): ?><option value="<?= (int) $option['id'] ?>" <?= (string) ($profile[$field] ?? '') === (string) $option['id'] ? 'selected' : '' ?>><?= e($option['name']) ?></option><?php endforeach; ?></select></div>
            <?php endforeach; ?>
        </div>
        <div class="form-actions"><a class="btn btn-outline-secondary" href="index.php?page=users">Cancel</a><button class="btn btn-success" type="submit">Save User Profile</button></div>
    </form>
</section>
