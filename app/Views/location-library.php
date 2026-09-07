<?php
$warehousesByProvince = [];
foreach ($locations as $location) {
    if (!empty($location['warehouse_id'])) $warehousesByProvince[(int) $location['province_id']][] = $location;
}
?>
<section class="workspace-section field-office-library" data-field-office-library>
    <div class="section-head compact"><div><p class="eyebrow">Administration</p><h3>Field Office Library</h3></div></div>

    <section class="panel field-office-browser">
        <div class="panel-head"><div><h2>Location Hierarchy</h2><p class="text-muted mb-0">Select a region, then a branch and province to manage its facilities.</p></div></div>
        <div class="field-office-region-picker">
            <label class="form-label" for="fieldOfficeRegion">Region</label>
            <select id="fieldOfficeRegion" class="form-select" data-field-office-region-select>
                <option value="">Select a region</option>
                <?php foreach ($regions as $region): ?><option value="<?= e($region['id']) ?>"><?= e($region['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <p class="field-office-empty" data-field-office-empty>Select a region to view its branches.</p>

        <?php foreach ($regions as $region): ?>
            <section class="field-office-region-panel" data-field-office-region-panel="<?= e($region['id']) ?>" hidden>
                <div class="field-office-selected-heading">
                    <div><span class="field-office-step">1. Region</span><h3><?= e($region['name']) ?></h3></div>
                    <form method="post" class="field-office-edit-form">
                        <input type="hidden" name="action" value="location-update"><input type="hidden" name="type" value="region"><input type="hidden" name="id" value="<?= e($region['id']) ?>">
                        <input required name="name" class="form-control form-control-sm" value="<?= e($region['name']) ?>" aria-label="Region name"><button class="btn btn-sm btn-outline-success" type="submit">Save</button><button class="location-delete-x" type="submit" name="action" value="location-delete" formnovalidate data-confirm-message="Delete this region?" aria-label="Delete region">X</button>
                    </form>
                </div>

                <div class="field-office-columns">
                    <section class="field-office-level">
                        <div class="field-office-level-heading"><div><span class="field-office-step">2. Branch</span><h4>Branches</h4></div></div>
                        <div class="field-office-item-list">
                            <?php $hasBranches = false; foreach ($branches as $branch): ?>
                                <?php if ((int) $branch['region_id'] !== (int) $region['id']) continue; $hasBranches = true; ?>
                                <button type="button" class="field-office-item" data-field-office-branch-select="<?= e($branch['id']) ?>"><span><?= e($branch['name']) ?></span><span aria-hidden="true">›</span></button>
                            <?php endforeach; ?>
                            <?php if (!$hasBranches): ?><p class="field-office-empty-level">No branches yet.</p><?php endif; ?>
                        </div>
                        <form method="post" class="field-office-add-form">
                            <input type="hidden" name="action" value="location-add"><input type="hidden" name="type" value="branch"><input type="hidden" name="region_id" value="<?= e($region['id']) ?>">
                            <label class="visually-hidden" for="branchName<?= e($region['id']) ?>">New branch name</label><input id="branchName<?= e($region['id']) ?>" required name="name" class="form-control form-control-sm" placeholder="New branch name"><button class="btn btn-sm btn-success" type="submit">+ Add Branch</button>
                        </form>
                    </section>

                    <section class="field-office-level field-office-detail-level" data-field-office-branch-area>
                        <p class="field-office-empty-level" data-field-office-branch-empty>Select a branch.</p>
                        <?php foreach ($branches as $branch): ?>
                            <?php if ((int) $branch['region_id'] !== (int) $region['id']) continue; ?>
                            <div data-field-office-branch-panel="<?= e($branch['id']) ?>" hidden>
                                <div class="field-office-level-heading"><div><span class="field-office-step">3. Province</span><h4><?= e($branch['name']) ?></h4></div><form method="post" class="field-office-icon-form"><input type="hidden" name="action" value="location-delete"><input type="hidden" name="type" value="branch"><input type="hidden" name="id" value="<?= e($branch['id']) ?>"><button class="location-delete-x" type="submit" formnovalidate data-confirm-message="Delete this branch?" aria-label="Delete branch">X</button></form></div>
                                <form method="post" class="field-office-edit-form mb-2"><input type="hidden" name="action" value="location-update"><input type="hidden" name="type" value="branch"><input type="hidden" name="id" value="<?= e($branch['id']) ?>"><input required name="name" class="form-control form-control-sm" value="<?= e($branch['name']) ?>" aria-label="Branch name"><button class="btn btn-sm btn-outline-success" type="submit">Save</button></form>
                                <div class="field-office-item-list">
                                    <?php $hasProvinces = false; foreach ($provinces as $province): ?>
                                        <?php if ((int) $province['branch_id'] !== (int) $branch['id']) continue; $hasProvinces = true; ?>
                                        <button type="button" class="field-office-item" data-field-office-province-select="<?= e($province['id']) ?>"><span><?= e($province['name']) ?></span><span aria-hidden="true">›</span></button>
                                    <?php endforeach; ?>
                                    <?php if (!$hasProvinces): ?><p class="field-office-empty-level">No provinces yet.</p><?php endif; ?>
                                </div>
                                <form method="post" class="field-office-add-form"><input type="hidden" name="action" value="location-add"><input type="hidden" name="type" value="province"><input type="hidden" name="branch_id" value="<?= e($branch['id']) ?>"><label class="visually-hidden" for="provinceName<?= e($branch['id']) ?>">New province name</label><input id="provinceName<?= e($branch['id']) ?>" required name="name" class="form-control form-control-sm" placeholder="New province name"><button class="btn btn-sm btn-success" type="submit">+ Add Province</button></form>
                            </div>
                        <?php endforeach; ?>
                    </section>

                    <section class="field-office-level field-office-detail-level" data-field-office-province-area>
                        <p class="field-office-empty-level" data-field-office-province-empty>Select a province.</p>
                        <?php foreach ($provinces as $province): ?>
                            <?php $provinceBranch = array_values(array_filter($branches, static fn (array $candidate): bool => (int) $candidate['id'] === (int) $province['branch_id']))[0] ?? null; ?>
                            <?php if (!$provinceBranch || (int) $provinceBranch['region_id'] !== (int) $region['id']) continue; ?>
                            <div data-field-office-province-panel="<?= e($province['id']) ?>" hidden>
                                <div class="field-office-level-heading"><div><span class="field-office-step">4. Warehouse / Facility</span><h4><?= e($province['name']) ?></h4></div><form method="post" class="field-office-icon-form"><input type="hidden" name="action" value="location-delete"><input type="hidden" name="type" value="province"><input type="hidden" name="id" value="<?= e($province['id']) ?>"><button class="location-delete-x" type="submit" formnovalidate data-confirm-message="Delete this province?" aria-label="Delete province">X</button></form></div>
                                <form method="post" class="field-office-edit-form mb-2"><input type="hidden" name="action" value="location-update"><input type="hidden" name="type" value="province"><input type="hidden" name="id" value="<?= e($province['id']) ?>"><input required name="name" class="form-control form-control-sm" value="<?= e($province['name']) ?>" aria-label="Province name"><button class="btn btn-sm btn-outline-success" type="submit">Save</button></form>
                                <div class="field-office-item-list field-office-warehouse-list">
                                    <?php foreach ($warehousesByProvince[(int) $province['id']] ?? [] as $warehouse): ?>
                                        <form method="post" class="field-office-edit-form"><input type="hidden" name="action" value="location-update"><input type="hidden" name="type" value="warehouse"><input type="hidden" name="id" value="<?= e($warehouse['warehouse_id']) ?>"><input required name="name" class="form-control form-control-sm" value="<?= e($warehouse['warehouse_name']) ?>" aria-label="Facility name"><button class="btn btn-sm btn-outline-success" type="submit">Save</button><button class="location-delete-x" type="submit" name="action" value="location-delete" formnovalidate data-confirm-message="Delete this facility?" aria-label="Delete facility">X</button></form>
                                    <?php endforeach; ?>
                                    <?php if (empty($warehousesByProvince[(int) $province['id']])): ?><p class="field-office-empty-level">No facilities yet.</p><?php endif; ?>
                                </div>
                                <form method="post" class="field-office-add-form"><input type="hidden" name="action" value="location-add"><input type="hidden" name="type" value="warehouse"><input type="hidden" name="province_id" value="<?= e($province['id']) ?>"><label class="visually-hidden" for="warehouseName<?= e($province['id']) ?>">New warehouse or facility name</label><input id="warehouseName<?= e($province['id']) ?>" required name="name" class="form-control form-control-sm" placeholder="New warehouse / facility"><button class="btn btn-sm btn-success" type="submit">+ Add Warehouse</button></form>
                            </div>
                        <?php endforeach; ?>
                    </section>
                </div>
            </section>
        <?php endforeach; ?>
    </section>
</section>
<?php if (!empty($locationDeletionImpact['records'])): ?>
<div class="modal fade" id="locationReassignmentModal" tabindex="-1" aria-labelledby="locationReassignmentModalTitle" aria-hidden="true" data-location-reassignment-modal>
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5" id="locationReassignmentModalTitle">Reassign affected records before deletion</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <p>The selected location is used by the records below. Update and save each record’s location, then try deleting the location again. Only location fields are editable here.</p>
                <div class="accordion" id="locationReassignmentAccordion">
                    <?php foreach ($locationDeletionImpact['records'] as $index => $record): ?>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="locationReassignmentHeading<?= (int) $index ?>"><button class="accordion-button<?= $index ? ' collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#locationReassignmentItem<?= (int) $index ?>" aria-expanded="<?= $index ? 'false' : 'true' ?>"><strong class="me-2 text-capitalize"><?= e($record['record_type']) ?></strong><?= e($record['label']) ?></button></h3>
                        <div id="locationReassignmentItem<?= (int) $index ?>" class="accordion-collapse collapse<?= $index ? '' : ' show' ?>" data-bs-parent="#locationReassignmentAccordion"><div class="accordion-body">
                            <form method="post" class="row g-3">
                                <input type="hidden" name="action" value="location-record-reassign"><input type="hidden" name="record_type" value="<?= e($record['record_type']) ?>"><input type="hidden" name="record_id" value="<?= e($record['id']) ?>">
                                <?php $locationClass = 'col-md-3'; $locationRequired = false; $locationRequiredLevels = ['region', 'branch', 'province']; $locationIncludeAll = false; $locationValues = []; $locationLabelWarehouse = 'Facility Name'; require BASE_PATH . '/app/Views/partials/location-selects.php'; ?>
                                <div class="col-12"><button class="btn btn-success" type="submit">Save location details</button></div>
                            </form>
                        </div></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>
<?php endif; ?>
