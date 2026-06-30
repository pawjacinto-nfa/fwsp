<?php
$locationPrefix = $locationPrefix ?? '';
$locationClass = $locationClass ?? 'col-md-3';
$locationRequired = $locationRequired ?? false;
$locationRequiredLevels = $locationRequiredLevels ?? [];
$locationIncludeAll = $locationIncludeAll ?? false;
$locationValues = $locationValues ?? [];
$locationLabelWarehouse = $locationLabelWarehouse ?? 'Facility Name';
$regionRequiredAttr = ($locationRequired || in_array('region', $locationRequiredLevels, true)) ? 'required' : '';
$branchRequiredAttr = ($locationRequired || in_array('branch', $locationRequiredLevels, true)) ? 'required' : '';
$provinceRequiredAttr = ($locationRequired || in_array('province', $locationRequiredLevels, true)) ? 'required' : '';
$warehouseRequiredAttr = ($locationRequired || in_array('warehouse', $locationRequiredLevels, true)) ? 'required' : '';
$emptyLabel = $locationIncludeAll ? 'All' : 'Select';
$showLocationClear = $locationShowClear ?? ($locationIncludeAll && !$locationRequired);
?>
<div class="<?= e($locationClass) ?>">
    <label class="form-label">Region</label>
    <select <?= $regionRequiredAttr ?> name="<?= e($locationPrefix) ?>region_id" class="form-select" data-location-level="region" data-selected="<?= e($locationValues['region_id'] ?? '') ?>">
        <option value=""><?= e($emptyLabel) ?></option>
        <?php foreach (\App\Models\Location::regions() as $region): ?>
            <option value="<?= e($region['id']) ?>" <?= ($locationValues['region_id'] ?? '') == $region['id'] ? 'selected' : '' ?>><?= e($region['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="<?= e($locationClass) ?>">
    <label class="form-label">Branch</label>
    <select <?= $branchRequiredAttr ?> name="<?= e($locationPrefix) ?>branch_id" class="form-select" data-location-level="branch" data-selected="<?= e($locationValues['branch_id'] ?? '') ?>">
        <option value=""><?= e($emptyLabel) ?></option>
        <?php foreach (\App\Models\Location::branches() as $branch): ?>
            <option value="<?= e($branch['id']) ?>" <?= ($locationValues['branch_id'] ?? '') == $branch['id'] ? 'selected' : '' ?>><?= e($branch['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="<?= e($locationClass) ?>">
    <label class="form-label">Province</label>
    <select <?= $provinceRequiredAttr ?> name="<?= e($locationPrefix) ?>province_id" class="form-select" data-location-level="province" data-selected="<?= e($locationValues['province_id'] ?? '') ?>">
        <option value=""><?= e($emptyLabel) ?></option>
        <?php foreach (\App\Models\Location::provinces() as $province): ?>
            <option value="<?= e($province['id']) ?>" <?= ($locationValues['province_id'] ?? '') == $province['id'] ? 'selected' : '' ?>><?= e($province['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="<?= e($locationClass) ?>">
    <label class="form-label"><?= e($locationLabelWarehouse) ?></label>
    <select <?= $warehouseRequiredAttr ?> name="<?= e($locationPrefix) ?>warehouse_id" class="form-select" data-location-level="warehouse" data-selected="<?= e($locationValues['warehouse_id'] ?? '') ?>">
        <option value=""><?= e($emptyLabel) ?></option>
        <?php foreach (\App\Models\Location::warehouses() as $warehouse): ?>
            <option value="<?= e($warehouse['id']) ?>" <?= ($locationValues['warehouse_id'] ?? '') == $warehouse['id'] ? 'selected' : '' ?>><?= e($warehouse['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<?php if ($showLocationClear): ?>
    <div class="col-md-auto align-self-end">
        <button class="btn btn-outline-success w-100" type="button" data-clear-location-filters>Clear Location</button>
    </div>
<?php endif; ?>
<?php unset($locationShowClear, $locationRequiredLevels); ?>
