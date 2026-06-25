<section class="workspace-section">
    <div class="section-head compact">
        <div>
            <p class="eyebrow">Records</p>
            <h3>Farmer Organizations</h3>
        </div>
    </div>

    <section class="panel farmer-organization-library">
        <div class="panel-head"><h2><?= $editOrganization ? 'Edit Farmer Organization' : 'Add Farmer Organization' ?></h2></div>
        <form method="post" class="mini-form">
            <input type="hidden" name="action" value="<?= $editOrganization ? 'farmer-organization-update' : 'farmer-organization-add' ?>">
            <?php if ($editOrganization): ?>
                <input type="hidden" name="id" value="<?= e($editOrganization['id']) ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Farmer Organization Name</label>
                    <input required name="name" class="form-control" value="<?= e($editOrganization['name'] ?? '') ?>" placeholder="Enter farmer organization name">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total Members</label>
                    <input type="number" min="0" name="total_members" class="form-control" value="<?= e($editOrganization['total_members'] ?? 0) ?>">
                </div>
                <?php if ($editOrganization): ?>
                    <div class="col-md-6">
                        <label class="form-label">Office Location</label>
                        <input name="office_location" class="form-control" value="<?= e($editOrganization['office_location'] ?? '') ?>" placeholder="Enter office location">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <?php if ($editOrganization): ?>
                    <a class="btn btn-outline-success" href="index.php?page=farmer-organization-library">Cancel</a>
                <?php endif; ?>
                <button class="btn btn-success" type="submit"><?= $editOrganization ? 'Save Changes' : 'Add Organization' ?></button>
            </div>
        </form>
    </section>

    <section class="panel table-section">
        <div class="panel-head"><h2>Farmer Organizations</h2></div>
        <div class="table-responsive">
            <table class="table align-middle sortable-table">
                <thead>
                    <tr>
                        <th>Farmers Organization Name</th>
                        <th>Total Number of Members</th>
                        <th>Office Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (($farmerOrganizations ?? []) as $organization): ?>
                    <?php
                    $officeLocation = trim((string) ($organization['office_location'] ?? ''));
                    ?>
                    <tr>
                        <td><?= e($organization['name']) ?></td>
                        <td><?= number_format((int) ($organization['total_members'] ?? 0)) ?></td>
                        <td class="table-location-cell"><?= e($officeLocation ?: 'Not set') ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-success" href="index.php?page=farmer-organization-view&id=<?= e($organization['id']) ?>">View</a>
                            <a class="btn btn-sm btn-warning" href="index.php?page=farmer-organization-library&edit_id=<?= e($organization['id']) ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
