<section class="workspace-section report-settings-page">
    <div class="section-head compact">
        <div>
            <p class="eyebrow">Reports</p>
            <h3>Report Settings</h3>
            <p class="section-copy">Manage the people who may appear as signatories on reports generated from your account.</p>
        </div>
        <a class="btn btn-outline-success" href="index.php?page=reports">Back to Reports</a>
    </div>

    <article class="panel signatory-add-panel">
        <div class="panel-head">
            <div>
                <h2>Add Signatories</h2>
                <p>One signatory row is provided by default. Add more rows if needed.</p>
            </div>
        </div>
        <form method="post" data-signatory-add-form>
            <input type="hidden" name="action" value="signatory-add">
            <div class="signatory-form-rows" data-signatory-form-rows>
                <div class="signatory-form-row" data-signatory-form-row>
                    <div>
                        <label class="form-label">Full Name</label>
                        <input class="form-control" type="text" name="full_name[]" maxlength="160" required>
                    </div>
                    <div>
                        <label class="form-label">Designation</label>
                        <input class="form-control" type="text" name="designation[]" maxlength="160" placeholder="e.g. Warehouse Personnel" required>
                    </div>
                    <button class="btn btn-outline-danger signatory-remove-row" type="button" data-remove-signatory-row aria-label="Remove signatory row">Remove</button>
                </div>
            </div>
            <div class="signatory-form-actions">
                <button class="btn btn-outline-success" type="button" data-add-signatory-row>+ Add another</button>
                <button class="btn btn-success" type="submit">Save Signatories</button>
            </div>
        </form>
    </article>

    <article class="panel">
        <div class="panel-head"><h2>Saved Signatories</h2><span><?= number_format(count($signatories ?? [])) ?> saved</span></div>
        <?php if (($signatories ?? []) === []): ?>
            <p class="empty-state">No signatories have been saved to your account yet.</p>
        <?php else: ?>
            <div class="saved-signatory-list">
                <?php foreach ($signatories as $signatory): ?>
                    <form method="post" class="saved-signatory-row">
                        <input type="hidden" name="action" value="signatory-update">
                        <input type="hidden" name="id" value="<?= e($signatory['id']) ?>">
                        <div>
                            <label class="form-label">Full Name</label>
                            <input class="form-control" type="text" name="full_name" maxlength="160" value="<?= e($signatory['full_name']) ?>" required>
                        </div>
                        <div>
                            <label class="form-label">Designation</label>
                            <input class="form-control" type="text" name="designation" maxlength="160" value="<?= e($signatory['designation']) ?>" required>
                        </div>
                        <div class="saved-signatory-actions">
                            <button class="btn btn-success" type="submit">Update</button>
                            <button class="btn btn-outline-danger" type="submit" name="action" value="signatory-delete" formnovalidate data-confirm-message="Delete this signatory from your account?">Delete</button>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>
