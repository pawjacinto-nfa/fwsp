<form method="post" class="panel form-panel tracked-form">
    <input type="hidden" name="action" value="transaction">
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
        <div class="col-md-3"><label class="form-label">Procurement</label><select name="procurement" class="form-select"><option>In-Warehouse</option><option>Mobile Procurement</option></select></div>
        <?php if ($sellerType === 'Individual'): ?>
            <div class="col-md-6">
                <label class="form-label">Farmer Name / RSBSA</label>
                <div class="autocomplete-field" data-autocomplete-field>
                    <?php
                    $farmerOptions = array_map(
                        fn (array $farmer): string => $farmer['rsbsa'] . ' - ' . trim(($farmer['first_name'] ?? '') . ' ' . ($farmer['middle_name'] ?? '') . ' ' . ($farmer['last_name'] ?? '')),
                        $farmers
                    );
                    ?>
                    <input required name="rsbsa" class="form-control" autocomplete="off" placeholder="Type farmer name or RSBSA" data-autocomplete-input data-autocomplete-source='<?= e(json_encode($farmerOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>'>
                    <div class="autocomplete-menu" data-autocomplete-menu></div>
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-4">
                <label class="form-label" for="foDeliveryName">Farmer Classification</label>
                <div class="autocomplete-field" data-autocomplete-field>
                    <input required id="foDeliveryName" name="fo_name" class="form-control" autocomplete="off" placeholder="Search farmer organization or IP group" data-fo-name-input data-autocomplete-input data-autocomplete-source='<?= e(json_encode(array_column($farmerOrganizations ?? [], 'name'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>'>
                    <div class="autocomplete-menu" data-autocomplete-menu></div>
                </div>
            </div>
            <div class="col-md-4"><label class="form-label">Authorized Representative</label><input required name="representative" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Total Farmer-Members</label><input type="number" min="0" name="members" class="form-control"></div>
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
        <div class="col-md-3"><label class="form-label">Verified Farm Area (ha)</label><input type="number" step="0.01" name="farm_area" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Delivery Date</label><input type="date" name="delivery_date" value="<?= date('Y-m-d') ?>" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">WSR Number</label><input required name="wsr" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Price/Kg</label><input type="number" step="0.01" name="price" class="form-control" data-delivery-price></div>
        <div class="col-md-3"><label class="form-label">Net Kilogram</label><input type="number" step="0.01" name="net_kg" class="form-control" data-delivery-net-kg></div>
        <div class="col-md-3"><label class="form-label">Bags Delivered (50kg)</label><input type="number" min="0" name="bags" class="form-control"></div>
        <?php if ($sellerType === 'Individual'): ?>
            <div class="col-md-3 delivery-total-field">
                <span class="form-label">Calculated Amount</span>
                <output class="delivery-total-cost" data-delivery-total-cost>Total Cost: 0.00</output>
            </div>
        <?php endif; ?>
        <?php if ($sellerType === 'Farmer Organization'): ?>
            <div class="col-12">
                <div class="fo-member-selector" data-fo-member-picker>
                    <div class="fo-member-selector-head">
                        <div>
                            <label class="form-label mb-1">Farmers Delivered Under This FO</label>
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
                                                <input type="checkbox" value="<?= e($farmer['id']) ?>">
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
        <button class="btn btn-success" type="submit">Record Delivery</button>
    </div>
</form>
