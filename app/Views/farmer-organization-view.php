<section class="workspace-section">
    <div class="section-head compact">
        <div>
            <p class="eyebrow">Farmer Organizations</p>
            <h3><?= e($organization['name'] ?? 'Farmer Organization') ?></h3>
            <?php if ($organization): ?>
                <?php $officeLocation = trim((string) ($organization['office_location'] ?? '')); ?>
                <form method="post" class="office-location-line">
                    <input type="hidden" name="action" value="farmer-organization-location-update">
                    <input type="hidden" name="id" value="<?= e($organization['id']) ?>">
                    <label>Office Location</label>
                    <input name="office_location" class="form-control form-control-sm" value="<?= e($officeLocation) ?>" placeholder="Enter office location">
                    <button class="btn btn-sm btn-success" type="submit">Save</button>
                </form>
            <?php endif; ?>
        </div>
        <a class="btn btn-outline-success" href="index.php?page=farmer-organization-library">Back to FO list</a>
    </div>

    <?php if (!$organization): ?>
        <div class="panel"><p class="mb-0">Farmer organization was not found.</p></div>
    <?php else: ?>
        <section class="sector-scoreboard fo-scoreboard-compact">
            <article class="sector-score-card headline">
                <span>Total Number of Members</span>
                <strong><?= number_format((int) ($organization['total_members'] ?? 0)) ?></strong>
                <p>Library metadata for this farmers organization.</p>
            </article>
        </section>

        <section class="panel table-section">
            <div class="panel-head"><h2>Farmer Members</h2></div>
            <div class="table-responsive">
                <table class="table align-middle sortable-table">
                    <thead>
                        <tr>
                            <th>RSBSA</th>
                            <th>Full Name</th>
                            <th>Sex</th>
                            <th>Age</th>
                            <th>Location</th>
                            <th>Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach (($members ?? []) as $member): ?>
                        <?php
                        $fullName = trim(($member['first_name'] ?? '') . ' ' . ($member['middle_name'] ?? '') . ' ' . ($member['last_name'] ?? ''));
                        $location = trim(implode(' / ', array_filter([
                            $member['region_name'] ?? '',
                            $member['branch_name'] ?? '',
                            $member['province_name'] ?? '',
                            $member['warehouse_name'] ?? '',
                        ])));
                        ?>
                        <tr>
                            <td><?= e($member['rsbsa']) ?></td>
                            <td><?= e($fullName) ?></td>
                            <td><?= e($member['sex']) ?></td>
                            <td><?= e($member['age'] ?? '') ?></td>
                            <td class="table-location-cell"><?= e($location ?: 'Not set') ?></td>
                            <td><a class="btn btn-sm btn-outline-success" href="index.php?page=farmer-view&id=<?= e($member['id']) ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (($members ?? []) === []): ?>
                        <tr><td colspan="6">No encoded farmer profiles are linked to this organization yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</section>
