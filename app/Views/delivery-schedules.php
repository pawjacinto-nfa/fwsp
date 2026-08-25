<?php
$itemsByDate = [];
foreach ($schedules as $schedule) $itemsByDate[$schedule['schedule_date']][] = $schedule;
$first = new DateTimeImmutable($month . '-01');
$start = $first->modify('monday this week');
$end = $first->modify('last day of this month')->modify('sunday this week');
$statusClasses = ['Scheduled' => 'scheduled', 'Completed' => 'completed', 'Rescheduled' => 'rescheduled', 'No-show' => 'no-show'];
$statusSymbols = ['Completed' => '✓', 'Rescheduled' => '!', 'No-show' => '×'];
$farmerSearchOptions = [];
$farmerSearchMap = [];
foreach ($farmers as $farmer) {
    $controlNumber = (string) ($farmer['farmer_key'] ?: $farmer['rsbsa']);
    $fullName = trim(($farmer['first_name'] ?? '') . ' ' . ($farmer['middle_name'] ?? '') . ' ' . ($farmer['last_name'] ?? ''));
    $label = $controlNumber . ' - ' . $fullName;
    $farmerSearchOptions[] = $label;
    $farmerSearchMap[$label] = [
        'id' => (int) $farmer['id'],
        'rsbsa' => (string) ($farmer['rsbsa'] ?? ''),
        'contact' => (string) ($farmer['contact'] ?? ''),
    ];
}
$organizationSearchOptions = [];
$organizationSearchMap = [];
foreach ($farmerOrganizations as $organization) {
    $label = (string) $organization['name'];
    $organizationSearchOptions[] = $label;
    $organizationSearchMap[$label] = (int) $organization['id'];
}
?>
<section class="workspace-section delivery-calendar-page">
    <div class="section-head compact delivery-calendar-head">
        <div>
            <p class="eyebrow">Shared Facility Calendar</p>
            <h3>Delivery Schedules</h3>
            <p class="mb-0 text-muted"><strong><?= e($calendarFacilityName ?? 'Assigned facility') ?></strong> &mdash; all authorized users assigned to this facility see the same bookings.</p>
        </div>
        <div class="calendar-nav">
            <a class="btn btn-outline-success" href="index.php?page=delivery-schedules&amp;month=<?= e($first->modify('-1 month')->format('Y-m')) ?>">&larr; Previous</a>
            <strong><?= e($first->format('F Y')) ?></strong>
            <a class="btn btn-outline-success" href="index.php?page=delivery-schedules&amp;month=<?= e($first->modify('+1 month')->format('Y-m')) ?>">Next &rarr;</a>
        </div>
    </div>

    <div class="schedule-legend">
        <span><i class="vacant"></i> Vacant</span>
        <span><i class="scheduled"></i> Scheduled</span>
        <span><i class="completed"></i> ✓ Completed</span>
        <span><i class="rescheduled"></i> ! Rescheduled</span>
        <span><i class="no-show"></i> × No-show</span>
        <span><i class="full"></i> No slots</span>
    </div>

    <div class="calendar-grid">
        <div class="calendar-weekdays"><?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday): ?><span><?= $weekday ?></span><?php endforeach ?></div>
        <div class="calendar-days">
            <?php for ($day = $start; $day <= $end; $day = $day->modify('+1 day')):
                $date = $day->format('Y-m-d');
                $entries = $itemsByDate[$date] ?? [];
                $count = count($entries);
                $slotStatus = $dayStatuses[$date] ?? 'Vacant';
                $noShowCount = count(array_filter($entries, fn (array $entry): bool => ($entry['status'] ?? 'Scheduled') === 'No-show'));
                $onlyNoShows = $count > 0 && $noShowCount === $count;
            ?>
                <button type="button" class="calendar-day <?= $day->format('m') !== $first->format('m') ? 'outside' : '' ?> <?= $slotStatus === 'Full' ? 'is-full' : ($onlyNoShows ? 'has-no-shows' : ($count ? 'has-schedules' : '')) ?>" data-schedule-date="<?= e($date) ?>" data-schedule-count="<?= $count ?>" data-no-show-count="<?= $noShowCount ?>">
                    <span class="calendar-date"><?= $day->format('j') ?></span>
                    <span class="calendar-count"><?= $slotStatus === 'Full' ? 'No slots' : ($count ? $count . ' scheduled' : 'Vacant') ?></span>
                    <?php if ($noShowCount): ?><span class="calendar-no-show-count"><?= $noShowCount ?> no-show<?= $noShowCount > 1 ? 's' : '' ?></span><?php endif ?>
                    <?php foreach (array_slice($entries, 0, 3) as $entry):
                        $entryStatus = $entry['status'] ?? 'Scheduled';
                        $entryName = ($entry['seller_type'] ?? 'Individual') === 'Farmer Organization'
                            ? ($entry['enrolled_organization_name'] ?: $entry['temporary_organization_name'])
                            : ($entry['enrolled_name'] ?: $entry['temporary_name']);
                    ?>
                        <small class="calendar-schedule-item is-<?= e($statusClasses[$entryStatus] ?? 'scheduled') ?>" title="<?= e($entryStatus . ': ' . $entryName) ?>">
                            <span class="visually-hidden"><?= e($entryStatus) ?>: </span>
                            <?php if (isset($statusSymbols[$entryStatus])): ?><span class="calendar-schedule-symbol" aria-hidden="true"><?= e($statusSymbols[$entryStatus]) ?></span><?php endif ?>
                            <span class="calendar-schedule-name"><?= e($entryName) ?></span>
                        </small>
                    <?php endforeach ?>
                    <?= $count > 3 ? '<small>+' . ($count - 3) . ' more</small>' : '' ?>
                </button>
            <?php endfor ?>
        </div>
    </div>
</section>

<div class="modal fade" id="scheduleDeliveryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content schedule-day-modal">
            <div class="modal-header">
                <div class="schedule-modal-title-wrap">
                    <div><h2 class="modal-title fs-5">Delivery Schedules</h2><small class="text-muted" data-schedule-date-label></small></div>
                    <label class="form-check form-switch no-slots-switch">
                        <input class="form-check-input" type="checkbox" role="switch" data-no-slots-toggle>
                        <span class="form-check-label">No slots</span>
                    </label>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <ul class="nav nav-tabs schedule-modal-tabs" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" id="schedule-delivery-tab" data-bs-toggle="tab" data-bs-target="#schedule-delivery-pane" type="button" role="tab">Schedule a Delivery</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="scheduled-list-tab" data-bs-toggle="tab" data-bs-target="#scheduled-list-pane" type="button" role="tab">List of Scheduled Deliveries <span class="badge text-bg-success" data-selected-day-count>0</span></button></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="schedule-delivery-pane" role="tabpanel" aria-labelledby="schedule-delivery-tab">
                    <form method="post">
                        <input type="hidden" name="action" value="delivery-schedule">
                        <input type="hidden" name="schedule_date" data-schedule-date-input>
                        <fieldset class="modal-body schedule-creation-fields" data-schedule-creation-fields>
                            <h3 class="form-section-title fs-6">Delivery Location</h3>
                            <div class="row g-3 mb-3" data-schedule-location>
                                <?php
                                $locationClass = 'col-md-6';
                                $locationRequired = true;
                                $locationIncludeAll = false;
                                $locationValues = $locationDefaults ?? [];
                                $locationLabelWarehouse = 'Facility Name';
                                $locationDisabled = !empty($lockScheduleFacility);
                                require BASE_PATH . '/app/Views/partials/location-selects.php';
                                ?>
                            </div>
                            <?php if (!empty($lockScheduleFacility)): ?><p class="form-text mt-n1">The delivery facility is fixed to your registered account assignment.</p><?php endif ?>

                            <fieldset class="schedule-type-fieldset">
                                <legend class="form-label">Schedule for</legend>
                                <div class="schedule-type-toggle" role="radiogroup" aria-label="Schedule type">
                                    <input type="radio" class="btn-check" name="seller_type" id="scheduleTypeIndividual" value="Individual" checked data-schedule-type>
                                    <label for="scheduleTypeIndividual">Individual</label>
                                    <input type="radio" class="btn-check" name="seller_type" id="scheduleTypeOrganization" value="Farmer Organization" data-schedule-type>
                                    <label for="scheduleTypeOrganization">Farmer Organization</label>
                                </div>
                            </fieldset>

                            <div class="schedule-party-panel is-visible" data-schedule-party-panel="Individual">
                                <label class="form-label" for="scheduledFarmer">Enrolled farmer</label>
                                <div class="autocomplete-field" data-autocomplete-field>
                                    <input type="hidden" name="farmer_id" value="" data-schedule-farmer-id>
                                    <input class="form-control" id="scheduledFarmer" autocomplete="off" placeholder="Search by control number or farmer name" data-autocomplete-input data-schedule-farmer-search data-farmer-search-map='<?= e(json_encode($farmerSearchMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>' data-autocomplete-source='<?= e(json_encode($farmerSearchOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>'>
                                    <div class="autocomplete-menu" data-autocomplete-menu></div>
                                </div>
                                <div class="form-text">Choose an enrolled farmer, or use the temporary-name field below.</div>
                                <label class="form-label mt-3" for="temporaryName">Non-enrolled farmer — full name</label>
                                <input class="form-control" id="temporaryName" name="temporary_name" maxlength="180" placeholder="Temporary full name">
                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label" for="scheduledFarmerRsbsa">RSBSA number</label>
                                        <input class="form-control" id="scheduledFarmerRsbsa" readonly placeholder="From enrolled farmer profile" data-schedule-farmer-rsbsa>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="scheduledFarmerContact">Contact number</label>
                                        <input class="form-control" id="scheduledFarmerContact" name="temporary_contact_number" type="tel" maxlength="40" autocomplete="tel" placeholder="Enter contact number for a temporary farmer" data-schedule-farmer-contact>
                                    </div>
                                </div>
                            </div>

                            <div class="schedule-party-panel" data-schedule-party-panel="Farmer Organization" hidden>
                                <label class="form-label" for="scheduledOrganization">Enrolled farmer organization</label>
                                <div class="autocomplete-field" data-autocomplete-field>
                                    <input type="hidden" name="farmer_organization_id" value="" data-schedule-organization-id disabled>
                                    <input class="form-control" id="scheduledOrganization" autocomplete="off" placeholder="Search farmer organization name" data-autocomplete-input data-schedule-organization-search data-organization-search-map='<?= e(json_encode($organizationSearchMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>' data-autocomplete-source='<?= e(json_encode($organizationSearchOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>' disabled>
                                    <div class="autocomplete-menu" data-autocomplete-menu></div>
                                </div>
                                <div class="form-text">Choose an enrolled organization, or enter a temporary organization name.</div>
                                <label class="form-label mt-3" for="temporaryOrganizationName">Temporary farmer organization name</label>
                                <input class="form-control" id="temporaryOrganizationName" name="temporary_organization_name" maxlength="180" placeholder="Temporary organization name" disabled>
                                <label class="form-label mt-3" for="representativeName">Representative full name</label>
                                <input class="form-control" id="representativeName" name="representative_name" maxlength="180" placeholder="Authorized representative" required disabled>
                            </div>

                            <label class="form-label mt-3" for="expectedBags">Number of Bags</label>
                            <input class="form-control" id="expectedBags" name="expected_bags" type="number" min="0.001" step="0.001" required>
                            <div class="alert alert-secondary mt-3 mb-0 py-2" data-no-slots-message hidden>This day is marked as having no available slots. Turn off “No slots” above before adding another schedule.</div>
                        </fieldset>
                        <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success" type="submit" data-save-schedule>Save schedule</button></div>
                    </form>
                </div>

                <div class="tab-pane fade" id="scheduled-list-pane" role="tabpanel" aria-labelledby="scheduled-list-tab">
                    <div class="modal-body scheduled-delivery-list">
                        <div class="scheduled-delivery-empty" data-scheduled-delivery-empty>No deliveries are scheduled for this day.</div>
                        <?php foreach ($schedules as $schedule):
                            $scheduleStatus = $schedule['status'] ?? 'Scheduled';
                            $statusClass = $statusClasses[$scheduleStatus] ?? 'scheduled';
                            $isOrganization = ($schedule['seller_type'] ?? 'Individual') === 'Farmer Organization';
                            $scheduledPartyName = $isOrganization ? ($schedule['enrolled_organization_name'] ?: $schedule['temporary_organization_name']) : ($schedule['enrolled_name'] ?: $schedule['temporary_name']);
                        ?>
                            <article class="scheduled-delivery-card is-<?= e($statusClass) ?>" data-schedule-entry data-schedule-entry-date="<?= e($schedule['schedule_date']) ?>" hidden>
                                <button class="schedule-card-menu" type="button" aria-label="Show schedule actions" aria-expanded="false" data-schedule-card-menu><span></span><span></span><span></span></button>
                                <div class="schedule-card-content">
                                    <div class="schedule-card-heading"><strong><?= e($scheduledPartyName) ?></strong><span class="schedule-status-badge"><?= e($scheduleStatus) ?></span></div>
                                    <small><?= e($schedule['confirmation_code']) ?></small>
                                    <dl><div><dt>Type</dt><dd><?= e($schedule['seller_type'] ?? 'Individual') ?></dd></div><?php if ($isOrganization): ?><div><dt>Representative</dt><dd><?= e($schedule['representative_name']) ?></dd></div><?php endif ?><div><dt>Number of bags</dt><dd><?= e(number_format((float) $schedule['expected_bags'], 3)) ?></dd></div><div><dt>Facility</dt><dd><?= e($schedule['warehouse_name']) ?></dd></div></dl>
                                </div>
                                <div class="schedule-card-actions" data-schedule-card-actions>
                                    <a class="schedule-action-print" href="index.php?page=delivery-schedule-confirmation&amp;id=<?= (int) $schedule['id'] ?>">Print Form</a>
                                    <?php foreach ([['completed', 'Completed'], ['rescheduled', 'Reschedule'], ['no-show', 'No-show']] as [$actionValue, $actionLabel]): ?>
                                        <form method="post">
                                            <input type="hidden" name="action" value="delivery-schedule-status">
                                            <input type="hidden" name="schedule_id" value="<?= (int) $schedule['id'] ?>">
                                            <input type="hidden" name="schedule_status" value="<?= e($actionValue) ?>">
                                            <button class="schedule-action-<?= e($actionValue) ?>" type="submit"><?= e($actionLabel) ?></button>
                                        </form>
                                    <?php endforeach ?>
                                </div>
                            </article>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalElement = document.getElementById('scheduleDeliveryModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const dateInput = modalElement.querySelector('[data-schedule-date-input]');
    const dateLabel = modalElement.querySelector('[data-schedule-date-label]');
    const countBadge = modalElement.querySelector('[data-selected-day-count]');
    const emptyState = modalElement.querySelector('[data-scheduled-delivery-empty]');
    const entries = [...modalElement.querySelectorAll('[data-schedule-entry]')];
    const scheduleTab = bootstrap.Tab.getOrCreateInstance(document.getElementById('schedule-delivery-tab'));
    const listTab = bootstrap.Tab.getOrCreateInstance(document.getElementById('scheduled-list-tab'));
    const noSlotsToggle = modalElement.querySelector('[data-no-slots-toggle]');
    const noSlotsMessage = modalElement.querySelector('[data-no-slots-message]');
    const saveSchedule = modalElement.querySelector('[data-save-schedule]');
    const creationFields = modalElement.querySelector('[data-schedule-creation-fields]');
    const warehouseSelect = modalElement.querySelector('[data-location-level="warehouse"]');
    const dayStatuses = <?= json_encode($allDayStatuses ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const calendarWarehouseId = String(<?= json_encode((int) ($calendarWarehouseId ?? 0)) ?>);

    const syncNoSlots = () => {
        const date = dateInput.value;
        const warehouseId = warehouseSelect?.value || '';
        const noSlots = Boolean(date && warehouseId && dayStatuses[warehouseId]?.[date] === 'Full');
        noSlotsToggle.checked = noSlots;
        noSlotsToggle.disabled = !date || !warehouseId;
        saveSchedule.disabled = noSlots;
        creationFields.disabled = noSlots;
        creationFields.classList.toggle('is-disabled', noSlots);
        noSlotsMessage.hidden = !noSlots;
    };

    const selectDate = (date, tab = 'schedule') => {
        dateInput.value = date;
        dateLabel.textContent = new Date(date + 'T00:00:00').toLocaleDateString(undefined, {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'});
        let visibleCount = 0;
        entries.forEach((entry) => {
            const visible = entry.dataset.scheduleEntryDate === date;
            entry.hidden = !visible;
            if (visible) visibleCount++;
        });
        countBadge.textContent = visibleCount;
        emptyState.hidden = visibleCount > 0;
        syncNoSlots();
        (tab === 'list' ? listTab : scheduleTab).show();
        modal.show();
    };

    document.querySelectorAll('[data-schedule-date]').forEach((button) => button.addEventListener('click', () => selectDate(button.dataset.scheduleDate)));
    modalElement.querySelectorAll('[data-schedule-card-menu]').forEach((button) => button.addEventListener('click', () => {
        const card = button.closest('[data-schedule-entry]');
        const open = card.classList.toggle('actions-open');
        button.setAttribute('aria-expanded', String(open));
    }));

    const farmerSearch = modalElement.querySelector('[data-schedule-farmer-search]');
    const farmerId = modalElement.querySelector('[data-schedule-farmer-id]');
    const farmerRsbsa = modalElement.querySelector('[data-schedule-farmer-rsbsa]');
    const farmerContact = modalElement.querySelector('[data-schedule-farmer-contact]');
    const temporaryName = modalElement.querySelector('#temporaryName');
    const farmerSearchMap = JSON.parse(farmerSearch?.dataset.farmerSearchMap || '{}');
    const syncFarmerDetails = () => {
        const previousFarmerId = farmerId.value;
        const farmer = farmerSearchMap[farmerSearch.value.trim()] || null;
        farmerId.value = farmer?.id || '';
        farmerRsbsa.value = farmer?.rsbsa || '';
        farmerContact.readOnly = Boolean(farmer);
        if (farmer) {
            farmerContact.value = farmer.contact || '';
            temporaryName.value = '';
        } else if (previousFarmerId) {
            farmerContact.value = '';
        }
    };
    farmerSearch?.addEventListener('input', syncFarmerDetails);
    farmerSearch?.addEventListener('change', syncFarmerDetails);
    temporaryName?.addEventListener('input', () => {
        if (!temporaryName.value.trim()) return;
        farmerSearch.value = '';
        syncFarmerDetails();
    });

    const organizationSearch = modalElement.querySelector('[data-schedule-organization-search]');
    const organizationId = modalElement.querySelector('[data-schedule-organization-id]');
    const organizationSearchMap = JSON.parse(organizationSearch?.dataset.organizationSearchMap || '{}');
    const syncOrganizationId = () => { organizationId.value = organizationSearchMap[organizationSearch.value.trim()] || ''; };
    organizationSearch?.addEventListener('input', syncOrganizationId);
    organizationSearch?.addEventListener('change', syncOrganizationId);

    let partyTransition;
    const switchPartyPanel = (sellerType, animate = true) => {
        window.clearTimeout(partyTransition);
        const current = modalElement.querySelector('[data-schedule-party-panel].is-visible');
        const next = modalElement.querySelector(`[data-schedule-party-panel="${sellerType}"]`);
        if (!next || current === next) return;
        const reveal = () => {
            if (current) {
                current.hidden = true;
                current.querySelectorAll('input, select, textarea').forEach((control) => { control.disabled = true; });
            }
            next.hidden = false;
            next.querySelectorAll('input, select, textarea').forEach((control) => { control.disabled = false; });
            window.requestAnimationFrame(() => next.classList.add('is-visible'));
        };
        if (!animate || !current) { reveal(); return; }
        current.classList.remove('is-visible');
        partyTransition = window.setTimeout(reveal, 150);
    };
    modalElement.querySelectorAll('[data-schedule-type]').forEach((input) => input.addEventListener('change', () => {
        if (input.checked) switchPartyPanel(input.value);
    }));

    modalElement.querySelectorAll('[data-location-level]').forEach((select) => select.addEventListener('change', () => window.setTimeout(syncNoSlots)));
    noSlotsToggle.addEventListener('change', async () => {
        const date = dateInput.value;
        const warehouseId = warehouseSelect?.value || '';
        const requestedNoSlots = noSlotsToggle.checked;
        if (!date || !warehouseId) { noSlotsToggle.checked = !requestedNoSlots; syncNoSlots(); return; }
        noSlotsToggle.disabled = true;
        try {
            const body = new URLSearchParams({
                action: 'delivery-day-status',
                csrf_token: window.FSR_MAINTENANCE?.csrfToken || '',
                schedule_date: date,
                warehouse_id: warehouseId,
                no_slots: requestedNoSlots ? '1' : '',
            });
            const response = await fetch('index.php', {method: 'POST', headers: {'X-Requested-With': 'fetch', 'Content-Type': 'application/x-www-form-urlencoded'}, body, credentials: 'same-origin'});
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'The day slot setting could not be saved.');
            dayStatuses[warehouseId] ||= {};
            dayStatuses[warehouseId][date] = result.status;
            if (warehouseId === calendarWarehouseId) {
                const calendarDay = [...document.querySelectorAll('[data-schedule-date]')].find((button) => button.dataset.scheduleDate === date);
                if (calendarDay) {
                    const count = Number(calendarDay.dataset.scheduleCount || 0);
                    const noShows = Number(calendarDay.dataset.noShowCount || 0);
                    calendarDay.classList.toggle('is-full', requestedNoSlots);
                    calendarDay.classList.remove('has-schedules', 'has-no-shows');
                    if (!requestedNoSlots && count > 0) calendarDay.classList.add(noShows === count ? 'has-no-shows' : 'has-schedules');
                    const countLabel = calendarDay.querySelector('.calendar-count');
                    if (countLabel) countLabel.textContent = requestedNoSlots ? 'No slots' : (count ? `${count} scheduled` : 'Vacant');
                }
            }
        } catch (error) {
            noSlotsToggle.checked = !requestedNoSlots;
            window.alert(error.message);
        } finally {
            syncNoSlots();
        }
    });

    const openDate = <?= json_encode($openDate ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    if (openDate) selectDate(openDate, <?= json_encode($activeScheduleTab ?? 'schedule', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>);
});
</script>
