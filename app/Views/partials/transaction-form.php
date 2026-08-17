<?php
$editingTransaction = $transaction ?? null;
$schedulePrefill = $scheduledDelivery ?? null;
$scheduledFarmerIdentifier = $schedulePrefill && !empty($schedulePrefill['farmer_id']) ? ($schedulePrefill['farmer_key'] ?? '') : '';
$scheduledOrganizationName = $schedulePrefill && ($schedulePrefill['seller_type'] ?? '') === 'Farmer Organization'
    ? ($schedulePrefill['enrolled_organization_name'] ?: $schedulePrefill['temporary_organization_name'])
    : '';
?>
<form method="post" class="panel form-panel tracked-form">
    <input type="hidden" name="action" value="<?= $editingTransaction ? 'transaction-update' : 'transaction' ?>">
    <?php if ($editingTransaction): ?><input type="hidden" name="transaction_id" value="<?= e($editingTransaction['id']) ?>"><?php endif; ?>
    <input type="hidden" name="client_control_number" value="">
    <input type="hidden" name="type" value="<?= e($sellerType) ?>">
    <div class="progress form-progress" role="progressbar" aria-label="Transaction form progress">
        <div class="progress-bar" style="width: 0%">0%</div>
    </div>
    <div class="row g-3">
        <?php if ($sellerType === 'Farmer Organization'): ?>
            <?php
            $locationClass = 'col-md-3';
            $locationRequired = true;
            $locationIncludeAll = false;
            $locationValues = $locationDefaults ?? [];
            $locationLabelWarehouse = 'Facility Name';
            require BASE_PATH . '/app/Views/partials/location-selects.php';
            ?>
        <?php endif; ?>
        <div class="col-md-3"><label class="form-label">Seller Type</label><input class="form-control" value="<?= e($sellerType) ?>" disabled></div>
        <div class="col-md-3"><label class="form-label">Procurement</label><select name="procurement" class="form-select"><option <?= ($editingTransaction['procurement_type'] ?? '') === 'In-Warehouse' ? 'selected' : '' ?>>In-Warehouse</option><option <?= ($editingTransaction['procurement_type'] ?? '') === 'Mobile Procurement' ? 'selected' : '' ?>>Mobile Procurement</option></select></div>
        <?php if ($sellerType === 'Individual'): ?>
            <div class="col-md-6">
                <label class="form-label">Farmer Name / Control Number</label>
                <div class="autocomplete-field" data-autocomplete-field>
                    <?php
                    $farmerOptions = array_map(
                        fn (array $farmer): string => ($farmer['farmer_key'] ?? $farmer['rsbsa']) . ' - ' . trim(($farmer['first_name'] ?? '') . ' ' . ($farmer['middle_name'] ?? '') . ' ' . ($farmer['last_name'] ?? '') . (!empty($farmer['no_available_control_number']) ? ' (Orange tag)' : '')),
                        $farmers
                    );
                    $farmerOrganizationMap = [];
                    $farmerProfileMap = [];
                    foreach ($farmers as $farmer) {
                        $identifiers = array_values(array_unique(array_filter([
                            $farmer['farmer_key'] ?? '',
                            $farmer['rsbsa'] ?? '',
                        ])));
                        foreach ($identifiers as $identifier) {
                            if (!empty($farmer['organization'])) $farmerOrganizationMap[$identifier] = $farmer['organization'];
                            $farmerProfileMap[$identifier] = [
                                'farm_area' => $farmer['harvest_area'] ?? '',
                                'region_id' => $farmer['region_id'] ?? '',
                                'branch_id' => $farmer['branch_id'] ?? '',
                                'province_id' => $farmer['province_id'] ?? '',
                                'warehouse_id' => $farmer['warehouse_id'] ?? '',
                            ];
                        }
                    }
                    ?>
                    <input required name="rsbsa" value="<?= e($editingTransaction['rsbsa'] ?? $scheduledFarmerIdentifier) ?>" class="form-control" autocomplete="off" placeholder="Type farmer name or control number" data-individual-farmer-input data-farmer-organization-map='<?= e(json_encode($farmerOrganizationMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>' data-farmer-profile-map='<?= e(json_encode($farmerProfileMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>' data-autocomplete-input data-autocomplete-source='<?= e(json_encode($farmerOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>'>
                    <div class="autocomplete-menu" data-autocomplete-menu></div>
                </div>
            </div>
            <div class="modal fade" id="individualFarmerOrganizationPrompt" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5">Individual Delivery Not Allowed</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body" data-fo-delivery-prompt-message></div><div class="modal-footer"><button class="btn btn-success" type="button" data-bs-dismiss="modal">OK</button></div></div></div></div>
        <?php else: ?>
            <?php
            $selectedDeliveredMemberIds = array_map(
                'intval',
                array_column($editingTransaction['delivered_members'] ?? [], 'id')
            );
            ?>
            <div class="col-md-4">
                <label class="form-label" for="foDeliveryName">Farmer Group</label>
                <div class="autocomplete-field" data-autocomplete-field>
                    <input required id="foDeliveryName" name="fo_name" value="<?= e($editingTransaction['fo_name'] ?? $scheduledOrganizationName) ?>" class="form-control" autocomplete="off" placeholder="Search farmer organization or IP group" data-fo-name-input data-autocomplete-input data-autocomplete-source='<?= e(json_encode(array_column($farmerOrganizations ?? [], 'name'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>'>
                    <div class="autocomplete-menu" data-autocomplete-menu></div>
                </div>
            </div>
            <div class="col-md-4"><label class="form-label">Authorized Representative</label><input required name="representative" value="<?= e($editingTransaction['representative_name'] ?? $schedulePrefill['representative_name'] ?? '') ?>" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Total Farmer-Members</label><input type="number" min="0" name="members" value="<?= e($editingTransaction['total_members'] ?? '') ?>" class="form-control"></div>
        <?php endif; ?>
        <?php if ($sellerType !== 'Farmer Organization'): ?>
            <?php
            $locationClass = 'col-md-3';
            $locationRequired = true;
            $locationIncludeAll = false;
            $locationValues = $locationDefaults ?? [];
            $locationLabelWarehouse = 'Facility Name';
            require BASE_PATH . '/app/Views/partials/location-selects.php';
            ?>
        <?php endif; ?>
        <div class="col-md-3"><label class="form-label">Verified Farm Area (ha)</label><input type="number" min="0" step="0.001" name="farm_area" value="<?= e($editingTransaction['verified_farm_area'] ?? '') ?>" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Delivery Date</label><input required type="date" name="delivery_date" value="<?= e($editingTransaction['delivery_date'] ?? $schedulePrefill['schedule_date'] ?? date('Y-m-d')) ?>" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">WSR Number</label><input required name="wsr" value="<?= e($editingTransaction['wsr'] ?? '') ?>" class="form-control" data-duplicate-check="wsr"<?= !empty($editingTransaction['id']) ? ' data-duplicate-exclude-id="' . e((string) $editingTransaction['id']) . '"' : '' ?>><small class="text-danger d-none" data-duplicate-warning>Record already exists.</small></div>
        <div class="col-md-3"><label class="form-label">Palay Variety</label><select name="palay_variety" class="form-select"><?php foreach (\App\Models\Transaction::PALAY_VARIETIES as $variety): ?><option value="<?= e($variety) ?>" <?= ($editingTransaction['palay_variety'] ?? 'PD1') === $variety ? 'selected' : '' ?>><?= e($variety) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><label class="form-label">Price/Kg</label><input required type="number" min="0.001" step="0.001" name="price" value="<?= e($editingTransaction['price_per_kilogram'] ?? '') ?>" class="form-control" data-delivery-price></div>
        <div class="col-md-3"><label class="form-label">Net Kilogram</label><input required type="number" min="0.001" step="0.001" name="net_kg" value="<?= e($editingTransaction['net_kilogram'] ?? '') ?>" class="form-control" data-delivery-net-kg></div>
        <div class="col-md-3"><label class="form-label">Bags Delivered (50kg)</label><input required type="number" min="0.001" step="0.001" name="bags" value="<?= e($editingTransaction['bags_50kg'] ?? '') ?>" class="form-control"></div>
        <div class="col-md-3 delivery-total-field">
            <label class="form-label">Total Amount</label>
            <input type="number" step="0.001" name="total_amount" class="form-control" data-delivery-total-cost value="<?= e($editingTransaction['total_amount'] ?? '0.000') ?>">
        </div>
        <?php if ($sellerType === 'Farmer Organization'): ?>
            <div class="col-12">
                <div class="fo-member-selector" data-fo-member-picker>
                    <div class="fo-member-selector-head">
                        <div>
                            <label class="form-label mb-1">Farmers Delivered Under This Farmer Group</label>
                            <p class="small text-muted mb-0">Select the member-farmers included in this delivery transaction.</p>
                        </div>
                        <button class="btn btn-outline-success" type="button" data-bs-toggle="modal" data-bs-target="#foMemberPickerModal">Add Farmer</button>
                    </div>
                    <div class="selected-member-list" data-selected-member-list>
                        <span class="text-muted">No farmer members selected yet.</span>
                    </div>
                    <div data-selected-member-inputs></div>

                    <div class="modal fade auth-modal fo-member-modal" id="foMemberPickerModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title fs-5">Add Farmer Members</h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input class="form-control mb-3" type="search" placeholder="Search by name, RSBSA, or farmer organization" data-fo-member-search>
                                    <div class="fo-member-picker-list">
                                        <?php foreach ($farmers as $farmer): ?>
                                            <?php
                                            $fullName = trim(($farmer['first_name'] ?? '') . ' ' . ($farmer['middle_name'] ?? '') . ' ' . ($farmer['last_name'] ?? ''));
                                            $organization = $farmer['organization'] ?? '';
                                            $searchText = strtolower(trim($fullName . ' ' . ($farmer['rsbsa'] ?? '') . ' ' . $organization));
                                            ?>
                                            <label class="fo-member-option" data-fo-member-option data-member-id="<?= e($farmer['id']) ?>" data-member-name="<?= e($fullName) ?>" data-member-rsbsa="<?= e($farmer['rsbsa']) ?>" data-member-organization="<?= e($organization) ?>" data-member-search="<?= e($searchText) ?>">
                                                <input type="checkbox" value="<?= e($farmer['id']) ?>" <?= in_array((int) $farmer['id'], $selectedDeliveredMemberIds ?? [], true) ? 'checked' : '' ?>>
                                                <span>
                                                    <strong><?= e($fullName) ?></strong>
                                                    <small><?= e($farmer['rsbsa']) ?><?= $organization !== '' ? ' / ' . e($organization) : '' ?></small>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="small text-muted mt-3 mb-0" data-fo-member-empty hidden>No matching farmers found for this search and farmer organization.</p>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                                    <button class="btn btn-success" type="button" data-fo-member-submit data-bs-dismiss="modal">Submit Selected Farmers</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="form-actions">
        <button class="btn btn-success" type="submit"><?= $editingTransaction ? 'Save Transaction' : 'Record Delivery' ?></button>
    </div>
</form>
<?php if ($editingTransaction): require BASE_PATH . '/app/Views/partials/version-history.php'; endif; ?>
