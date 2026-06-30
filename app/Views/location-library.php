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
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>Region</th><th>Branch</th><th>Province</th><th>Facility Name</th></tr>
                </thead>
                <tbody>
                <?php foreach ($locations as $location): ?>
                    <tr>
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
                            <form method="post" class="inline-edit">
                                <input type="hidden" name="type" value="warehouse">
                                <input type="hidden" name="id" value="<?= e($location['warehouse_id']) ?>">
                                <input name="name" class="form-control form-control-sm" value="<?= e($location['warehouse_name']) ?>">
                                <button class="btn btn-sm btn-outline-success" type="submit" name="action" value="location-update">Save</button>
                                <button class="location-delete-x" type="submit" name="action" value="location-delete" formnovalidate data-confirm-message="Delete this facility?" aria-label="Delete facility">X</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
