<?php
$status = (string) ($schedule['status'] ?? '');
$statusSlug = strtolower(str_replace(' ', '-', $status));
$statusContent = [
    'Scheduled' => ['title' => 'Your appointment is scheduled', 'message' => 'Bring your printed form and deliver to the receiving facility on the scheduled date or within the stated five-day grace period.'],
    'Completed' => ['title' => 'Delivery completed', 'message' => 'The receiving facility has marked this scheduled delivery as completed. Official warehouse transaction records remain controlling.'],
    'Rescheduled' => ['title' => 'Appointment requires rescheduling', 'message' => 'This appointment has been marked for rescheduling. Please coordinate with the concerned NFA Branch Office before delivery.'],
    'No-show' => ['title' => 'Appointment marked no-show', 'message' => 'The receiving facility recorded that the scheduled delivery was not made. Contact the concerned NFA Branch Office for assistance.'],
];
$scheduledDate = $schedule ? new DateTimeImmutable((string) $schedule['schedule_date']) : null;
$validUntil = $scheduledDate?->modify('+5 days');
$expectedBags = $schedule ? rtrim(rtrim(number_format((float) $schedule['expected_bags'], 3, '.', ','), '0'), '.') : '';
$facility = $schedule ? implode(', ', array_filter([
    $schedule['warehouse_name'] ?? '',
    $schedule['province_name'] ?? '',
    $schedule['branch_name'] ?? '',
    $schedule['region_name'] ?? '',
])) : '';
$updatedAt = $schedule ? (($schedule['status_changed_at'] ?? '') ?: ($schedule['created_at'] ?? '')) : '';
?>
<main class="tracking-shell">
    <header class="tracking-brand">
        <img src="<?= e(app_base_path()) ?>/assets/images/nfa-logo-official.png" alt="National Food Authority logo">
        <div><span>Republic of the Philippines</span><strong>National Food Authority</strong><small>Farmer-Seller Registry</small></div>
    </header>

    <?php if (!$schedule): ?>
        <section class="tracking-card tracking-not-found">
            <span class="tracking-symbol" aria-hidden="true">!</span>
            <p class="tracking-kicker">Appointment verification</p>
            <h1>Appointment not found</h1>
            <p>The link is invalid, disabled, or incomplete. Check the printed address or contact the concerned NFA Branch Office.</p>
        </section>
    <?php else: ?>
        <section class="tracking-card is-<?= e($statusSlug) ?>">
            <div class="tracking-status-head">
                <div>
                    <p class="tracking-kicker">Official appointment status</p>
                    <h1><?= e($statusContent[$status]['title'] ?? 'Appointment status') ?></h1>
                </div>
                <span class="tracking-badge"><?= e($status) ?></span>
            </div>
            <p class="tracking-message"><?= e($statusContent[$status]['message'] ?? '') ?></p>

            <div class="tracking-reference">
                <span>Schedule Reference No.</span>
                <strong><?= e($schedule['confirmation_code']) ?></strong>
            </div>

            <dl class="tracking-details">
                <div><dt>Scheduled delivery</dt><dd><?= e($scheduledDate?->format('F j, Y')) ?></dd></div>
                <div><dt>Valid through</dt><dd><?= e($validUntil?->format('F j, Y')) ?></dd></div>
                <div><dt>Expected delivery</dt><dd><?= e($expectedBags) ?> bags</dd></div>
                <div><dt>Schedule type</dt><dd><?= e($schedule['seller_type']) ?></dd></div>
                <div class="is-wide"><dt>Receiving facility</dt><dd><?= e($facility) ?></dd></div>
            </dl>

            <div class="tracking-privacy">
                <strong>Privacy protected</strong>
                <p>This public page does not display the farmer's name, RSBSA number, contact number, address, or internal notes.</p>
            </div>
            <?php if ($updatedAt !== ''): ?><p class="tracking-updated">Status last updated <?= e(date('F j, Y \a\t g:i A', strtotime($updatedAt))) ?></p><?php endif ?>
        </section>
    <?php endif ?>

    <footer>For questions, coordinate directly with the NFA Branch Office shown on your printed form.</footer>
</main>
