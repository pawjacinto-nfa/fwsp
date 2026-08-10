<?php
$farm = $farm ?? [];
$farmIndex = $farmIndex ?? 0;
$classifications = $farm['landholding'] ?? $farm['classification'] ?? [];
if (!is_array($classifications)) {
    $classifications = [];
}
$irrigated = $farm['irrigated'] ?? 'Yes';
?>
<section class="farm-location-card" data-farm-location>
    <div class="farm-location-head">
        <h3>Farm Location <span data-farm-number><?= e((int) $farmIndex + 1) ?></span></h3>
        <button class="btn btn-sm btn-outline-danger" type="button" data-remove-farm-location>Remove Farm</button>
    </div>
    <div class="row g-3">
        <div class="col-md-2"><label class="form-label">Palay Location</label><input name="farms[<?= e($farmIndex) ?>][palay_location]" value="<?= e($farm['palay_location'] ?? '') ?>" class="form-control"></div>
        <div class="col-md-2"><label class="form-label d-block">Irrigated</label><div class="farm-irrigated-options"><label><input type="radio" name="farms[<?= e($farmIndex) ?>][irrigated]" value="Yes" <?= $irrigated === 'Yes' ? 'checked' : '' ?>> Yes</label><label><input type="radio" name="farms[<?= e($farmIndex) ?>][irrigated]" value="No" <?= $irrigated === 'No' ? 'checked' : '' ?>> No</label></div></div>
        <div class="col-md-2"><label class="form-label">Harvested Area (ha)</label><input type="number" step="0.001" name="farms[<?= e($farmIndex) ?>][harvest_area]" value="<?= e($farm['harvest_area'] ?? '') ?>" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Average Yield/ha (Main, MT)</label><input type="number" step="0.001" name="farms[<?= e($farmIndex) ?>][main_crop_yield]" value="<?= e($farm['main_crop_yield'] ?? $farm['average_yield'] ?? '') ?>" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Average Yield/ha (Summer, MT)</label><input type="number" step="0.001" name="farms[<?= e($farmIndex) ?>][summer_crop_yield]" value="<?= e($farm['summer_crop_yield'] ?? '') ?>" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Average Yield/ha (Third Crop, MT)</label><input type="number" step="0.001" name="farms[<?= e($farmIndex) ?>][third_crop_yield]" value="<?= e($farm['third_crop_yield'] ?? '') ?>" class="form-control"></div>
    </div>
    <div class="check-grid farm-classification-grid">
        <?php foreach (['Riceland', 'Cornland', 'Owner-Tiller', 'Landowner/Lessor', 'CLT Holder/Recipient'] as $item): ?>
            <label><input type="checkbox" name="farms[<?= e($farmIndex) ?>][landholding][]" value="<?= e($item) ?>" <?= in_array($item, $classifications, true) ? 'checked' : '' ?>> <?= e($item) ?></label>
        <?php endforeach; ?>
    </div>
</section>
