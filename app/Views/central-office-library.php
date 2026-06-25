<section class="workspace-section">
    <div class="section-head compact">
        <div>
            <p class="eyebrow">Administration</p>
            <h3>Central Office Directory</h3>
        </div>
    </div>

    <section class="panel location-library">
        <div class="panel-head"><h2>Add Central Office Details</h2></div>
        <div class="location-add-stack" data-central-office-add-stack>
            <form method="post" class="mini-form library-add-row central-office-department-row">
                <input type="hidden" name="action" value="central-office-add">
                <input type="hidden" name="type" value="department">
                <div>
                    <label class="form-label">Department</label>
                    <input required name="name" class="form-control" placeholder="Department name">
                </div>
                <button class="btn btn-success" type="submit">Add Department</button>
            </form>
            <form method="post" class="mini-form library-add-row">
                <input type="hidden" name="action" value="central-office-add">
                <input type="hidden" name="type" value="division">
                <div>
                    <label class="form-label">Department</label>
                    <select required name="department_id" class="form-select" data-central-office-add-level="department">
                        <option value="">Select</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= e($department['id']) ?>"><?= e($department['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Division</label>
                    <input required name="name" class="form-control" placeholder="Division name">
                </div>
                <button class="btn btn-success" type="submit">Add Division</button>
            </form>
            <form method="post" class="mini-form library-add-row">
                <input type="hidden" name="action" value="central-office-add">
                <input type="hidden" name="type" value="unit">
                <div>
                    <label class="form-label">Division</label>
                    <select required name="division_id" class="form-select" data-central-office-add-level="division">
                        <option value="">Select</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Service/Unit</label>
                    <input required name="name" class="form-control" placeholder="Service or unit name">
                </div>
                <button class="btn btn-success" type="submit">Add Service/Unit</button>
            </form>
            <div>
                <button class="btn btn-outline-success" type="button" data-clear-central-office-add>Clear Selection</button>
            </div>
        </div>
    </section>

    <section class="panel table-section">
        <div class="panel-head"><h2>Central Office Master Directory</h2></div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>Department</th><th>Division</th><th>Service/Unit</th></tr>
                </thead>
                <tbody>
                <?php foreach ($locations as $location): ?>
                    <tr>
                        <td>
                            <form method="post" class="inline-edit">
                                <input type="hidden" name="type" value="department">
                                <input type="hidden" name="id" value="<?= e($location['department_id']) ?>">
                                <input name="name" class="form-control form-control-sm" value="<?= e($location['department_name']) ?>">
                                <button class="btn btn-sm btn-outline-success" type="submit" name="action" value="central-office-update">Save</button>
                                <button class="location-delete-x" type="submit" name="action" value="central-office-delete" formnovalidate onclick="return confirm('Delete this department?')" aria-label="Delete department">X</button>
                            </form>
                        </td>
                        <td>
                            <?php if (!empty($location['division_id'])): ?>
                                <form method="post" class="inline-edit">
                                    <input type="hidden" name="type" value="division">
                                    <input type="hidden" name="id" value="<?= e($location['division_id']) ?>">
                                    <input name="name" class="form-control form-control-sm" value="<?= e($location['division_name']) ?>">
                                    <button class="btn btn-sm btn-outline-success" type="submit" name="action" value="central-office-update">Save</button>
                                    <button class="location-delete-x" type="submit" name="action" value="central-office-delete" formnovalidate onclick="return confirm('Delete this division?')" aria-label="Delete division">X</button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($location['unit_id'])): ?>
                                <form method="post" class="inline-edit">
                                    <input type="hidden" name="type" value="unit">
                                    <input type="hidden" name="id" value="<?= e($location['unit_id']) ?>">
                                    <input name="name" class="form-control form-control-sm" value="<?= e($location['unit_name']) ?>">
                                    <button class="btn btn-sm btn-outline-success" type="submit" name="action" value="central-office-update">Save</button>
                                    <button class="location-delete-x" type="submit" name="action" value="central-office-delete" formnovalidate onclick="return confirm('Delete this service/unit?')" aria-label="Delete service or unit">X</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($locations === []): ?>
                    <tr><td colspan="3" class="text-muted">No central office directory entries yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
