<form method="post" enctype="multipart/form-data" class="panel form-panel tracked-form">
    <input type="hidden" name="action" value="farmer">
    <div class="progress form-progress" role="progressbar" aria-label="Farmer form progress">
        <div class="progress-bar" style="width: 0%">0%</div>
    </div>
    <div class="form-section-title">Personal Details</div>
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label">RSBSA Number</label><input required name="rsbsa" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">First Name</label><input required name="first_name" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Middle Name</label><input name="middle_name" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Last Name</label><input required name="last_name" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Full Home Address</label><input required name="address" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Birth Date</label><input type="date" name="birthdate" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Birthplace</label><input name="birthplace" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Civil Status</label><select name="civil_status" class="form-select"><option>Single</option><option>Married</option><option>Widowed</option><option>Separated</option></select></div>
        <div class="col-md-3"><label class="form-label">Spouse</label><input name="spouse" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Dependents</label><input type="number" min="0" name="dependents" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Contact Number</label><input name="contact" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Sex</label><select required name="sex" class="form-select"><option>Female</option><option>Male</option></select></div>
        <div class="col-md-4"><label class="form-label">Farmer Photo</label><input type="file" name="farmer_photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="form-control"><small class="text-muted">JPG or PNG only, max 50MB.</small></div>
    </div>
    <div class="form-section-title">NFA Location</div>
    <div class="row g-3">
        <?php
        $locationClass = 'col-md-3';
        $locationRequired = true;
        $locationIncludeAll = false;
        $locationValues = $locationDefaults ?? [];
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
                        <input type="radio" name="gender_orientation" value="<?= e($item) ?>">
                        <span><?= e($item) ?></span>
                    </label>
                <?php endforeach; ?>
                <label class="rainbow-choice rainbow-choice-other">
                    <input type="radio" name="gender_orientation" value="Other" data-toggle-other-input="identityOtherField">
                    <span>Others</span>
                </label>
                <input id="identityOtherField" class="form-control rainbow-other-input" name="gender_orientation_other" placeholder="Please specify" disabled>
                <label class="rainbow-choice rainbow-choice-na">
                    <input type="radio" name="gender_orientation" value="N/A" checked>
                    <span>N/A</span>
                </label>
            </div>
        </section>
        <section>
            <div class="form-section-title">Sector</div>
            <div class="check-grid sector-check-grid">
                <?php foreach (['Persons with Disability', 'Indigenous People', 'Senior Citizen', 'Muslim', 'Youth', 'Adult'] as $item): ?>
                    <label><input type="checkbox" name="sector[]" value="<?= e($item) ?>"> <?= e($item) ?></label>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
    <div class="form-section-title">Landholding Data</div>
    <div class="check-grid">
        <?php foreach (['Riceland', 'Cornland', 'Owner-Tiller', 'Landowner/Lessor', 'CLT Holder/Recipient'] as $item): ?>
            <label><input type="checkbox" name="landholding[]" value="<?= e($item) ?>"> <?= e($item) ?></label>
        <?php endforeach; ?>
    </div>
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Irrigated</label><select name="irrigated" class="form-select"><option>Yes</option><option>No</option></select></div>
        <div class="col-md-3"><label class="form-label">Palay Location</label><input name="palay_location" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Harvested Area (ha)</label><input type="number" step="0.01" name="harvest_area" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Average Yield/ha</label><input type="number" step="0.01" name="average_yield" class="form-control"></div>
        <div class="col-md-6">
            <label class="form-label">Farmer Organization</label>
            <div class="autocomplete-field" data-autocomplete-field>
                <input name="organization" class="form-control" autocomplete="off" placeholder="Type to search farmer organization" data-autocomplete-input data-autocomplete-source='<?= e(json_encode(array_column($farmerOrganizations ?? [], 'name'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>'>
                <div class="autocomplete-menu" data-autocomplete-menu></div>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-success" type="submit">Save Farmer</button>
    </div>
</form>
