<section class="workspace-section">
    <div class="section-head compact">
        <div>
            <p class="eyebrow">Farmer Records</p>
            <h3>Farmer Profile</h3>
        </div>
        <a class="btn btn-outline-success" href="index.php?page=farmers">Back to Farmers</a>
    </div>

    <?php if (!$farmer): ?>
        <div class="panel"><p class="mb-0">Farmer profile was not found.</p></div>
    <?php else: ?>
        <?php
        $sogieOptions = ['Lesbian', 'Gay', 'Bisexual', 'Transgender', 'N/A'];
        $currentSogie = $farmer['gender_orientation'][0] ?? 'N/A';
        $otherSogie = '';
        if (!in_array($currentSogie, $sogieOptions, true)) {
            $otherSogie = $currentSogie;
            $currentSogie = 'Other';
        } elseif ($currentSogie === 'Other' && !empty($farmer['gender_orientation'][1])) {
            $otherSogie = $farmer['gender_orientation'][1];
        }
        $currentSectors = $farmer['sector'] ?? [];
        $currentLandholding = $farmer['landholding'] ?? [];
        ?>
        <form method="post" enctype="multipart/form-data" class="panel form-panel farmer-profile-edit" data-farmer-profile-form>
            <input type="hidden" name="action" value="farmer-update">
            <input type="hidden" name="farmer_id" value="<?= e($farmer['id']) ?>">

            <div class="farmer-profile-edit-head">
                <div class="farmer-photo-frame">
                    <?php if (!empty($farmer['photo_path'])): ?>
                        <img src="<?= e($farmer['photo_path']) ?>" alt="<?= e($farmer['first_name'] . ' ' . $farmer['last_name']) ?>">
                    <?php else: ?>
                        <span>No Photo</span>
                    <?php endif; ?>
                </div>
                <div>
                    <h4><?= e(trim($farmer['first_name'] . ' ' . $farmer['middle_name'] . ' ' . $farmer['last_name'])) ?></h4>
                    <p><?= e($farmer['region_name'] . ' / ' . $farmer['branch_name'] . ' / ' . $farmer['province_name'] . ' / ' . $farmer['warehouse_name']) ?></p>
                </div>
            </div>

            <fieldset disabled data-farmer-profile-fields>
                <div class="form-section-title">Personal Details</div>
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">RSBSA Number</label><input required name="rsbsa" value="<?= e($farmer['rsbsa']) ?>" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">First Name</label><input required name="first_name" value="<?= e($farmer['first_name']) ?>" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Middle Name</label><input name="middle_name" value="<?= e($farmer['middle_name']) ?>" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Last Name</label><input required name="last_name" value="<?= e($farmer['last_name']) ?>" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Full Home Address</label><input required name="address" value="<?= e($farmer['address']) ?>" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Birth Date</label><input type="date" name="birthdate" value="<?= e($farmer['birthdate']) ?>" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Birthplace</label><input name="birthplace" value="<?= e($farmer['birthplace']) ?>" class="form-control"></div>
                    <div class="col-md-3">
                        <label class="form-label">Civil Status</label>
                        <select name="civil_status" class="form-select">
                            <?php foreach (['Single', 'Married', 'Widowed', 'Separated'] as $status): ?>
                                <option <?= ($farmer['civil_status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Spouse</label><input name="spouse" value="<?= e($farmer['spouse']) ?>" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Dependents</label><input type="number" min="0" name="dependents" value="<?= e($farmer['dependents']) ?>" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Contact Number</label><input name="contact" value="<?= e($farmer['contact']) ?>" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Email Address</label><input type="email" name="email" value="<?= e($farmer['email']) ?>" class="form-control"></div>
                    <div class="col-md-4">
                        <label class="form-label">Sex</label>
                        <select required name="sex" class="form-select">
                            <?php foreach (['Female', 'Male'] as $sex): ?>
                                <option <?= ($farmer['sex'] ?? '') === $sex ? 'selected' : '' ?>><?= e($sex) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Farmer Photo</label><input type="file" name="farmer_photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="form-control"><small class="text-muted">JPG or PNG only, max 50MB.</small></div>
                </div>

                <div class="form-section-title">NFA Location</div>
                <div class="row g-3">
                    <?php
                    $locationClass = 'col-md-3';
                    $locationRequired = true;
                    $locationIncludeAll = false;
                    $locationValues = [
                        'region_id' => $farmer['region_id'] ?? '',
                        'branch_id' => $farmer['branch_id'] ?? '',
                        'province_id' => $farmer['province_id'] ?? '',
                        'warehouse_id' => $farmer['warehouse_id'] ?? '',
                    ];
                    $locationLabelWarehouse = 'Facility Name';
                    require BASE_PATH . '/app/Views/partials/location-selects.php';
                    ?>
                </div>

                <div class="identity-sector-row">
                    <section>
                        <div class="form-section-title">SOGIE</div>
                        <div class="rainbow-selection rainbow-selection-vertical" role="radiogroup" aria-label="LGBTQIA+ identity selection">
                            <?php foreach (['Lesbian', 'Gay', 'Bisexual', 'Transgender'] as $item): ?>
                                <label class="rainbow-choice">
                                    <input type="radio" name="gender_orientation" value="<?= e($item) ?>" <?= $currentSogie === $item ? 'checked' : '' ?>>
                                    <span><?= e($item) ?></span>
                                </label>
                            <?php endforeach; ?>
                            <label class="rainbow-choice rainbow-choice-other">
                                <input type="radio" name="gender_orientation" value="Other" data-toggle-other-input="profileIdentityOtherField" <?= $currentSogie === 'Other' ? 'checked' : '' ?>>
                                <span>Others</span>
                            </label>
                            <input id="profileIdentityOtherField" class="form-control rainbow-other-input" name="gender_orientation_other" value="<?= e($otherSogie) ?>" placeholder="Please specify" <?= $currentSogie === 'Other' ? '' : 'disabled' ?>>
                            <label class="rainbow-choice rainbow-choice-na">
                                <input type="radio" name="gender_orientation" value="N/A" <?= $currentSogie === 'N/A' ? 'checked' : '' ?>>
                                <span>N/A</span>
                            </label>
                        </div>
                    </section>
                    <section>
                        <div class="form-section-title">Sector</div>
                        <div class="check-grid sector-check-grid">
                            <?php foreach (['Persons with Disability', 'Indigenous People', 'Senior Citizen', 'Muslim', 'Youth', 'Adult'] as $item): ?>
                                <label><input type="checkbox" name="sector[]" value="<?= e($item) ?>" <?= in_array($item, $currentSectors, true) ? 'checked' : '' ?>> <?= e($item) ?></label>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <div class="form-section-title">Landholding Data</div>
                <div class="check-grid">
                    <?php foreach (['Riceland', 'Cornland', 'Owner-Tiller', 'Landowner/Lessor', 'CLT Holder/Recipient'] as $item): ?>
                        <label><input type="checkbox" name="landholding[]" value="<?= e($item) ?>" <?= in_array($item, $currentLandholding, true) ? 'checked' : '' ?>> <?= e($item) ?></label>
                    <?php endforeach; ?>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Irrigated</label>
                        <select name="irrigated" class="form-select">
                            <?php foreach (['Yes', 'No'] as $item): ?>
                                <option <?= ($farmer['irrigated'] ?? '') === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Palay Location</label><input name="palay_location" value="<?= e($farmer['palay_location']) ?>" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Harvested Area (ha)</label><input type="number" step="0.01" name="harvest_area" value="<?= e($farmer['harvest_area']) ?>" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Average Yield/ha</label><input type="number" step="0.01" name="average_yield" value="<?= e($farmer['average_yield']) ?>" class="form-control"></div>
                    <div class="col-md-6">
                        <label class="form-label">Farmer Organization</label>
                        <div class="autocomplete-field" data-autocomplete-field>
                            <input name="organization" value="<?= e($farmer['organization']) ?>" class="form-control" autocomplete="off" placeholder="Type to search farmer organization" data-autocomplete-input data-autocomplete-source='<?= e(json_encode(array_column($farmerOrganizations ?? [], 'name'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>'>
                            <div class="autocomplete-menu" data-autocomplete-menu></div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="form-actions farmer-profile-actions">
                <button class="btn btn-outline-success" type="button" data-profile-edit-button>Edit Profile</button>
                <button class="btn btn-success d-none" type="submit" data-profile-save-button>Save Profile</button>
            </div>
        </form>
    <?php endif; ?>
</section>
