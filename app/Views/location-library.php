<section class="workspace-section">
    <div class="section-head compact">
        <div>
            <p class="eyebrow">Administration</p>
            <h3>Location Library</h3>
        </div>
    </div>

    <section class="panel location-library">
        <div class="panel-head"><h2>Add Location Details</h2></div>
        <div class="location-add-stack" data-location-add-stack>
            <form method="post" class="mini-form library-add-row">
                <input type="hidden" name="action" value="location-add">
                <input type="hidden" name="type" value="branch">
                <div>
                    <label class="form-label">Region</label>
                    <select required name="region_id" class="form-select" data-location-add-level="region">
                        <option value="">Select</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= e($region['id']) ?>"><?= e($region['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Branch</label>
                    <input required name="name" class="form-control" placeholder="Branch name">
                </div>
                <button class="btn btn-success" type="submit">Add Branch</button>
            </form>
            <form method="post" class="mini-form library-add-row">
                <input type="hidden" name="action" value="location-add">
                <input type="hidden" name="type" value="province">
                <div>
                    <label class="form-label">Branch</label>
                    <select required name="branch_id" class="form-select" data-location-add-level="branch">
                        <option value="">Select</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Province</label>
                    <input required name="name" class="form-control" placeholder="Province name">
                </div>
                <button class="btn btn-success" type="submit">Add Province</button>
            </form>
            <form method="post" class="mini-form library-add-row">
                <input type="hidden" name="action" value="location-add">
                <input type="hidden" name="type" value="warehouse">
                <div>
                    <label class="form-label">Province</label>
                    <select required name="province_id" class="form-select" data-location-add-level="province">
                        <option value="">Select</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Facility Name</label>
                    <input required name="name" class="form-control" placeholder="Warehouse / facility name">
                </div>
                <button class="btn btn-success" type="submit">Add Facility</button>
            </form>
            <div>
                <button class="btn btn-outline-success" type="button" data-clear-location-add>Clear Selection</button>
            </div>
        </div>
    </section>

    <section class="panel table-section">
        <div class="panel-head"><h2>Master Location List</h2></div>
        <div class="row g-2 align-items-end mb-3" data-field-office-filters>
            <div class="col-md-4">
                <label class="form-label" for="fieldOfficeSearch">Search locations</label>
                <input id="fieldOfficeSearch" class="form-control" type="search" placeholder="Search region, branch, province, or facility">
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="form-label" for="fieldOfficeRegionFilter">Region</label>
                <select id="fieldOfficeRegionFilter" class="form-select" data-field-office-filter="region"><option value="">All</option><?php foreach ($regions as $region): ?><option value="<?= e($region['id']) ?>"><?= e($region['name']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="form-label" for="fieldOfficeBranchFilter">Branch</label>
                <select id="fieldOfficeBranchFilter" class="form-select" data-field-office-filter="branch"><option value="">All</option><?php foreach ($branches as $branch): ?><option value="<?= e($branch['id']) ?>"><?= e($branch['name']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="form-label" for="fieldOfficeProvinceFilter">Province</label>
                <select id="fieldOfficeProvinceFilter" class="form-select" data-field-office-filter="province"><option value="">All</option><?php foreach ($provinces as $province): ?><option value="<?= e($province['id']) ?>"><?= e($province['name']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-success w-100" type="button" data-clear-field-office-filters>Clear filters</button></div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle" id="field-office-table" data-page-size="20">
                <thead>
                    <tr><th>Region</th><th>Branch</th><th>Province</th><th>Facility Name</th></tr>
                </thead>
                <tbody>
                <?php foreach ($locations as $location): ?>
                    <tr data-region-id="<?= e($location['region_id']) ?>" data-branch-id="<?= e($location['branch_id']) ?>" data-province-id="<?= e($location['province_id']) ?>">
                        <td>
                            <form method="post" class="inline-edit">
                                <input type="hidden" name="type" value="region">
                                <input type="hidden" name="id" value="<?= e($location['region_id']) ?>">
                                <input name="name" class="form-control form-control-sm" value="<?= e($location['region_name']) ?>">
                                <button class="btn btn-sm btn-outline-success" type="submit" name="action" value="location-update">Save</button>
                                <button class="location-delete-x" type="submit" name="action" value="location-delete" formnovalidate data-confirm-message="Delete this region?" aria-label="Delete region">X</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" class="inline-edit">
                                <input type="hidden" name="type" value="branch">
                                <input type="hidden" name="id" value="<?= e($location['branch_id']) ?>">
                                <input name="name" class="form-control form-control-sm" value="<?= e($location['branch_name']) ?>">
                                <button class="btn btn-sm btn-outline-success" type="submit" name="action" value="location-update">Save</button>
                                <button class="location-delete-x" type="submit" name="action" value="location-delete" formnovalidate data-confirm-message="Delete this branch?" aria-label="Delete branch">X</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" class="inline-edit">
                                <input type="hidden" name="type" value="province">
                                <input type="hidden" name="id" value="<?= e($location['province_id']) ?>">
                                <input name="name" class="form-control form-control-sm" value="<?= e($location['province_name']) ?>">
                                <button class="btn btn-sm btn-outline-success" type="submit" name="action" value="location-update">Save</button>
                                <button class="location-delete-x" type="submit" name="action" value="location-delete" formnovalidate data-confirm-message="Delete this province?" aria-label="Delete province">X</button>
                            </form>
                        </td>
                        <td>
                            <?php if (empty($location['warehouse_id'])): ?>
                                <input class="form-control form-control-sm" value="" disabled aria-label="No facility assigned">
                            <?php else: ?>
                            <form method="post" class="inline-edit">
                                <input type="hidden" name="type" value="warehouse">
                                <input type="hidden" name="id" value="<?= e($location['warehouse_id']) ?>">
                                <input name="name" class="form-control form-control-sm" value="<?= e($location['warehouse_name']) ?>">
                                <button class="btn btn-sm btn-outline-success" type="submit" name="action" value="location-update">Save</button>
                                <button class="location-delete-x" type="submit" name="action" value="location-delete" formnovalidate data-confirm-message="Delete this facility?" aria-label="Delete facility">X</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
