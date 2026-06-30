<?php
$activeClassification = ($activeClassification ?? 'organizations') === 'indigenous' ? 'indigenous' : 'organizations';
$isIndigenousTab = $activeClassification === 'indigenous';
$classificationLabel = $isIndigenousTab ? 'Indigenous People Group' : 'Farmer Organization';
$classificationListLabel = $isIndigenousTab ? 'Indigenous People Groups' : 'Farmer Organizations';
$locationFilters = $locationFilters ?? [];
$baseParams = ['page' => 'farmer-organization-library'];
foreach (['region_id', 'branch_id', 'province_id', 'warehouse_id'] as $filterKey) {
    if (($locationFilters[$filterKey] ?? '') !== '') {
        $baseParams[$filterKey] = $locationFilters[$filterKey];
    }
}
$organizationTabUrl = 'index.php?' . http_build_query($baseParams + ['classification' => 'organizations']);
$indigenousTabUrl = 'index.php?' . http_build_query($baseParams + ['classification' => 'indigenous']);
$classificationUrl = $activeClassification === 'indigenous' ? $indigenousTabUrl : $organizationTabUrl;
?>
<section class="workspace-section">
    <div class="section-head compact">
        <div>
            <p class="eyebrow">Records</p>
            <h3>Farmer Classifications</h3>
        </div>
    </div>

    <nav class="farmer-classification-tabs" aria-label="Farmer classification types">
        <a class="farmer-classification-tab <?= !$isIndigenousTab ? 'is-active' : '' ?>" href="<?= e($organizationTabUrl) ?>" <?= !$isIndigenousTab ? 'aria-current="page"' : '' ?>>Farmer Organizations</a>
        <a class="farmer-classification-tab <?= $isIndigenousTab ? 'is-active' : '' ?>" href="<?= e($indigenousTabUrl) ?>" <?= $isIndigenousTab ? 'aria-current="page"' : '' ?>>Indigenous People Groups</a>
    </nav>

    <form method="get" class="panel filter-panel">
        <input type="hidden" name="page" value="farmer-organization-library">
        <input type="hidden" name="classification" value="<?= e($activeClassification) ?>">
        <div class="row g-3 align-items-end">
            <?php
            $locationClass = 'col-md-2';
            $locationRequired = false;
            $locationIncludeAll = true;
            $locationShowClear = false;
            $locationValues = $locationFilters;
            $locationLabelWarehouse = 'Facility';
            require BASE_PATH . '/app/Views/partials/location-selects.php';
            ?>
            <div class="col-md-1"><button class="btn btn-success w-100" type="submit">Apply</button></div>
            <div class="col-md-1"><a class="btn btn-outline-success w-100" href="index.php?page=farmer-organization-library&amp;classification=<?= e($activeClassification) ?>">Reset</a></div>
        </div>
    </form>

    <?php if (!empty($canManageClassifications)): ?>
    <section class="panel farmer-organization-library">
        <div class="panel-head"><h2><?= $editOrganization ? 'Edit ' : 'Add ' ?><?= e($classificationLabel) ?></h2></div>
        <form method="post" class="mini-form">
            <input type="hidden" name="action" value="<?= $editOrganization ? 'farmer-organization-update' : 'farmer-organization-add' ?>">
            <input type="hidden" name="classification" value="<?= e($activeClassification) ?>">
            <?php foreach (['region_id', 'branch_id', 'province_id', 'warehouse_id'] as $filterKey): ?>
                <?php if (($locationFilters[$filterKey] ?? '') !== ''): ?>
                    <input type="hidden" name="<?= e($filterKey) ?>" value="<?= e($locationFilters[$filterKey]) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($editOrganization): ?>
                <input type="hidden" name="id" value="<?= e($editOrganization['id']) ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><?= e($classificationLabel) ?> Name</label>
                    <input required name="name" class="form-control" value="<?= e($editOrganization['name'] ?? '') ?>" placeholder="Enter <?= strtolower(e($classificationLabel)) ?> name">
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

            <div class="row g-3 mt-1">
                <?php
                $locationPrefix = 'organization_';
                $locationClass = 'col-md-3';
                $locationRequired = true;
                $locationIncludeAll = false;
                $locationValues = $editOrganization ?: $locationFilters;
                $locationLabelWarehouse = 'Assigned Facility';
                require BASE_PATH . '/app/Views/partials/location-selects.php';
                $locationPrefix = '';
                ?>
            </div>

            <div class="form-actions">
                <?php if ($editOrganization): ?>
                    <a class="btn btn-outline-success" href="<?= e($classificationUrl) ?>">Cancel</a>
                <?php endif; ?>
                <button class="btn btn-success" type="submit"><?= $editOrganization ? 'Save Changes' : 'Add ' . e($classificationLabel) ?></button>
            </div>
        </form>
    </section>
    <?php endif; ?>

    <section class="panel table-section">
        <div class="panel-head"><h2><?= e($classificationListLabel) ?></h2></div>
        <div class="table-responsive">
            <table class="table align-middle sortable-table">
                <thead>
                    <tr>
                        <th><?= e($classificationLabel) ?> Name</th>
                        <th>Total Number of Members</th>
                        <th>Office Location</th>
                        <th>Assigned Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (($farmerOrganizations ?? []) as $organization): ?>
                    <?php
                    $officeLocation = trim((string) ($organization['office_location'] ?? ''));
                    $assignedLocation = trim(implode(' / ', array_filter([
                        $organization['region_name'] ?? '',
                        $organization['branch_name'] ?? '',
                        $organization['province_name'] ?? '',
                        $organization['warehouse_name'] ?? '',
                    ])));
                    ?>
                    <tr>
                        <td><?= e($organization['name']) ?></td>
                        <td><?= number_format((int) ($organization['total_members'] ?? 0)) ?></td>
                        <td class="table-location-cell"><?= e($officeLocation ?: 'Not set') ?></td>
                        <td class="table-location-cell"><?= e($assignedLocation ?: 'Unassigned') ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-success" href="index.php?page=farmer-organization-view&id=<?= e($organization['id']) ?>&classification=<?= e($activeClassification) ?>">View</a>
                            <?php if (!empty($canManageClassifications)): ?>
                                <a class="btn btn-sm btn-warning" href="<?= e($classificationUrl) ?>&edit_id=<?= e($organization['id']) ?>">Edit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (($farmerOrganizations ?? []) === []): ?>
                    <tr><td colspan="5" class="text-center text-muted">No <?= strtolower(e($classificationListLabel)) ?> found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
