<?php
$isOrganization = ($schedule['seller_type'] ?? 'Individual') === 'Farmer Organization';
$isFilipino = ($printLanguage ?? 'tl') !== 'en';
$scheduledName = trim((string) ($isOrganization
    ? ($schedule['enrolled_organization_name'] ?: $schedule['temporary_organization_name'])
    : ($schedule['enrolled_name'] ?: $schedule['temporary_name'])));
$representativeName = trim((string) ($schedule['representative_name'] ?? ''));
$scheduledDate = new DateTimeImmutable((string) $schedule['schedule_date']);
$validUntil = $scheduledDate->modify('+5 days');
$issuedDate = new DateTimeImmutable((string) $schedule['created_at']);
$expectedBags = rtrim(rtrim(number_format((float) $schedule['expected_bags'], 3, '.', ','), '0'), '.');
$expectedKilograms = rtrim(rtrim(number_format((float) $schedule['expected_bags'] * 50, 3, '.', ','), '0'), '.');
$facility = implode(', ', array_filter([
    $schedule['warehouse_name'] ?? '',
    $schedule['province_name'] ?? '',
    $schedule['branch_name'] ?? '',
]));
$farmerRsbsa = trim((string) ($schedule['farmer_rsbsa'] ?? ''));
$farmerAddress = trim((string) ($schedule['farmer_address'] ?? ''));
$farmerContact = trim((string) (($schedule['farmer_contact'] ?? '') ?: ($schedule['temporary_contact_number'] ?? '')));
$maoCertification = trim((string) ($schedule['farmer_mao_certification'] ?? ''));
$organizationAddress = trim((string) ($schedule['organization_address'] ?? ''));
$bookingUserName = trim((string) ($schedule['created_by_name'] ?? ''));
$bookingUserName = $bookingUserName !== '' ? mb_convert_case($bookingUserName, MB_CASE_TITLE, 'UTF-8') : '';
$bookingUserPosition = trim((string) ($schedule['created_by_designation'] ?? ''));
$publicStatusUrl = delivery_schedule_public_url((string) ($schedule['public_token'] ?? ''));
$publicStatusQr = \App\Support\QrCode::dataUri($publicStatusUrl);
$blank = '<span class="po-manual-line" aria-label="Blank field"></span>';
$shortBlank = '<span class="po-manual-line is-short" aria-label="Blank field"></span>';

$filipinoMonths = [1 => 'Enero', 'Pebrero', 'Marso', 'Abril', 'Mayo', 'Hunyo', 'Hulyo', 'Agosto', 'Setyembre', 'Oktubre', 'Nobyembre', 'Disyembre'];
$formatDate = static function (DateTimeInterface $date) use ($isFilipino, $filipinoMonths): string {
    return $isFilipino
        ? 'ika-' . $date->format('j') . ' ng ' . $filipinoMonths[(int) $date->format('n')] . ' ' . $date->format('Y')
        : $date->format('F j, Y');
};
$ordinalDay = static function (int $day): string {
    if ($day % 100 >= 11 && $day % 100 <= 13) return $day . 'th';
    return $day . match ($day % 10) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
};

$scheduledMonth = (int) $scheduledDate->format('n');
$season = match ($scheduledMonth) {
    4, 5, 6, 7 => ['SC', $isFilipino ? 'Ani sa Tag-init' : 'Summer Crop'],
    8 => ['TC', $isFilipino ? 'Ikatlong Ani' : 'Third Crop'],
    9, 10, 11, 12 => ['MC', $isFilipino ? 'Pangunahing Ani' : 'Main Crop'],
    default => ['EC', $isFilipino ? 'Maagang Ani' : 'Early Crop'],
};

$labels = $isFilipino ? [
    'form' => 'Iskedyul ng Paghahatid',
    'copy' => 'Kopya ng Kliyente',
    'region' => 'NFA Rehiyon',
    'branch' => 'Sangay',
    'reference' => 'Schedule Reference No.',
    'date' => 'Petsa',
    'validity' => 'Validity Period',
    'individual_name' => 'Pangalan ng Indibidwal na Magsasaka',
    'member_name' => 'Pangalan ng Magsasaka na Miyembro ng Samahan',
    'rsbsa' => $isOrganization ? 'RSBSA No. ng Magsasaka na Miyembro ng Samahan' : 'RSBSA No.',
    'address' => 'Tirahan',
    'contact' => 'Contact Number',
    'mao' => 'Petsa ng Sertipikasyon mula sa MAO kung walang RSBSA No. (kopya ay i-attach)',
    'organization' => 'Pangalan ng Samahan',
    'representative' => 'Pangalan ng Magsasaka na Representative ng Samahan',
    'dear' => 'Mahal na G./Gng.',
    'intro' => 'Ang NFA ay sumasang-ayon na bumili ng inyong ani ng palay alinsunod sa mga sumusunod:',
    'details' => 'Mga Detalye ng Binibili',
    'product' => 'Uri ng Produkto',
    'palay' => 'Palay',
    'quantity' => 'Tinatayang Dami',
    'members' => 'Bilang ng Miyembro na Magbebenta',
    'members_value' => 'Mula ' . $shortBlank . ' hanggang ' . $shortBlank,
    'price' => 'Presyo kada kilo',
    'price_value' => 'PHP 23.00/kg o mas mataas, batay sa umiiral na Buying Price Bulletin (BPB) sa panahon ng pagbenta.',
    'quality' => 'Pamantayan ng Kalidad',
    'quality_value' => 'Malinis (95%-100% Purity Level), tuyo (11%-14% MC), at iba pa alinsunod sa takdang pamantayan sa pamimili ng palay. Ang pagbili ng basang palay na higit sa 14% MC ay hanggang sa limitasyon ng drying capacity ng tanggapan lamang.',
    'sample_date' => 'Petsa ng Paghahatid ng Sample',
    'bulk_date' => 'Petsa ng Paghahatid ng Buong Delivery',
    'warehouse' => 'Bodega / Lugar ng Paghahatidan',
    'payment' => 'Ang kabayaran ay ibibigay sa loob ng ' . $shortBlank . ' araw matapos ang inspeksyon at pagtanggap ng produktong palay at pagproseso batay sa umiiral na patakaran ng NFA.',
    'terms' => 'Iba pang mga Tuntunin at Kundisyon',
    'signing' => 'Nilagdaan ngayong ' . $formatDate($issuedDate) . '.',
    'nfa_signature' => 'Lagda ng Kinatawan ng NFA',
    'farmer_signature' => 'Lagda ng Magsasaka',
    'name' => 'Pangalan',
    'position' => 'Posisyon',
    'notes_label' => 'Mahalagang Paalala sa Iskedyul',
    'tracking_title' => 'Tingnan ang Status Online',
    'tracking_text' => 'I-scan ang QR code o buksan ang maikling link upang makita ang kasalukuyang status ng appointment.',
    'tracking_privacy' => 'Hindi ipinapakita online ang personal na impormasyon ng magsasaka.',
] : [
    'form' => 'Delivery Schedule Form',
    'copy' => "Client's Copy",
    'region' => 'NFA Region',
    'branch' => 'Branch',
    'reference' => 'Schedule Reference No.',
    'date' => 'Date',
    'validity' => 'Validity Period',
    'individual_name' => 'Name of Individual Farmer',
    'member_name' => 'Name of Farmer Member of the Organization',
    'rsbsa' => $isOrganization ? 'RSBSA No. of Farmer Member' : 'RSBSA No.',
    'address' => 'Address',
    'contact' => 'Contact Number',
    'mao' => 'Date of MAO Certification if without RSBSA No. (attach a copy)',
    'organization' => 'Name of Farmer Organization',
    'representative' => 'Name of Farmer Representative of the Organization',
    'dear' => 'Dear Mr./Ms.',
    'intro' => 'The NFA agrees to purchase your palay produce subject to the following:',
    'details' => 'Purchase Details',
    'product' => 'Product Type',
    'palay' => 'Palay',
    'quantity' => 'Estimated Quantity',
    'members' => 'Number of Members Who Will Sell',
    'members_value' => 'From ' . $shortBlank . ' to ' . $shortBlank,
    'price' => 'Price per kilogram',
    'price_value' => 'PHP 23.00/kg or higher, based on the prevailing Buying Price Bulletin (BPB) at the time of sale.',
    'quality' => 'Quality Standards',
    'quality_value' => 'Clean (95%-100% purity), dry (11%-14% MC), and compliant with the prescribed palay procurement standards. Wet palay above 14% MC may be purchased only within the office drying-capacity limit.',
    'sample_date' => 'Sample Delivery Date',
    'bulk_date' => 'Bulk Delivery Date',
    'warehouse' => 'Warehouse / Delivery Location',
    'payment' => 'Payment shall be released within ' . $shortBlank . ' days after inspection and acceptance of the palay and completion of processing under prevailing NFA policy.',
    'terms' => 'Other Terms and Conditions',
    'signing' => 'Signed this ' . $ordinalDay((int) $issuedDate->format('j')) . ' day of ' . $issuedDate->format('F, Y') . '.',
    'nfa_signature' => 'Signature of NFA Representative',
    'farmer_signature' => "Farmer's Signature",
    'name' => 'Name',
    'position' => 'Position',
    'notes_label' => 'Important Scheduling Notes',
    'tracking_title' => 'Check Status Online',
    'tracking_text' => 'Scan the QR code or open the short link to view the current appointment status.',
    'tracking_privacy' => 'Farmer personal information is not displayed online.',
];

$terms = $isFilipino ? [
    $isOrganization
        ? 'Ang magsasakang miyembro ng samahan ay nagpapatunay na totoo ang mga ibinigay na impormasyon.'
        : 'Ang magsasaka ay nagpapatunay na totoo ang mga ibinigay na impormasyon.',
    'Ang NFA ay may karapatang magsagawa ng inspeksyon bago tanggapin ang produkto.',
    'Ang hindi pumasa sa itinakdang kalidad ay maaaring tanggihan o bawasan ang presyo ayon sa Equivalent Net Weight Factor (ENWF) Table ng NFA.',
    'Ang parehong panig ay sumasang-ayon sa lahat ng nakasaad sa form ng iskedyul ng paghahatid na ito.',
] : [
    $isOrganization
        ? 'The farmer member of the organization certifies that all information provided is true and correct.'
        : 'The farmer certifies that all information provided is true and correct.',
    'The NFA may inspect the product before acceptance.',
    'Palay that fails the prescribed quality standards may be rejected or repriced using the NFA Equivalent Net Weight Factor (ENWF) Table.',
    'Both parties agree to all provisions stated in this Delivery Schedule Form.',
];

$scheduleNotes = $isFilipino ? [
    'Ang dokumentong ito ay para lamang sa nakapangalan na nagbebenta, iskedyul, pasilidad, at dami. Hindi ito maaaring ilipat, baguhin, o kopyahin nang walang pahintulot. Ang kasalukuyang limitasyon ay 200 sako o 10,000 kilo bawat transaksyon, maliban kung may awtorisadong pagbabago.',
    'Maghatid sa nakatakdang petsa o sa loob ng limang araw na palugit. Ang pagbabago ng iskedyul ay nangangailangan ng nakasulat na pahintulot ng kinauukulang NFA Branch Office.',
    'Kung hindi makapaghahatid, ipagbigay-alam sa Branch Office nang nakasulat nang hindi bababa sa 48 oras bago ang iskedyul kung maaari. Ang no-show o hindi paghahatid nang walang abiso at makatuwirang dahilan ay maaaring makaapekto sa susunod na iskedyul at humantong sa kaukulang operational sanction.',
    'Mga dahilang sasailalim sa beripikasyon ng NFA: force majeure, kalamidad o matinding panahon, peste o pagkasira ng ani, at malubhang karamdaman o aksidente.',
] : [
    'This document applies only to the named seller, schedule, facility, and quantity. It is non-transferable and may not be altered or duplicated without authority. The current ceiling is 200 bags or 10,000 kilograms per transaction unless an authorized revision applies.',
    'Deliver on the scheduled date or within the five-day grace period. A schedule adjustment requires written approval from the concerned NFA Branch Office.',
    'If delivery cannot be made, notify the Branch Office in writing at least 48 hours before the schedule whenever practicable. A no-show or failure to deliver without notice and a justifiable reason may affect future scheduling and may result in operational sanctions.',
    'Reasons subject to NFA verification include force majeure, calamity or severe weather, pest infestation or crop failure, and serious illness or accident.',
];

$recipientName = $isOrganization ? ($representativeName ?: $scheduledName) : $scheduledName;
?>
<section class="schedule-confirmation-page">
    <div class="confirmation-toolbar no-print">
        <a class="btn btn-outline-secondary" href="index.php?page=delivery-schedules&amp;month=<?= e($scheduledDate->format('Y-m')) ?>">Back to Calendar</a>
        <div class="btn-group" role="group" aria-label="Printout language">
            <a class="btn <?= $isFilipino ? 'btn-success' : 'btn-outline-success' ?>" href="index.php?page=delivery-schedule-confirmation&amp;id=<?= e((string) $schedule['id']) ?>&amp;language=tl">Tagalog Form</a>
            <a class="btn <?= !$isFilipino ? 'btn-success' : 'btn-outline-success' ?>" href="index.php?page=delivery-schedule-confirmation&amp;id=<?= e((string) $schedule['id']) ?>&amp;language=en">English Form</a>
        </div>
        <button class="btn btn-primary" type="button" onclick="window.print()">Print / Save <?= $isFilipino ? 'Tagalog' : 'English' ?> PDF</button>
    </div>

    <section class="confirmation-appointment-summary no-print" aria-labelledby="appointmentSummaryTitle">
        <div>
            <p class="eyebrow">Scheduled appointment</p>
            <h2 id="appointmentSummaryTitle"><?= e($schedule['confirmation_code']) ?></h2>
            <p>Review the saved details, select a language above, then preview and print the form below.</p>
        </div>
        <span class="confirmation-status-pill is-<?= e(strtolower(str_replace(' ', '-', (string) ($schedule['status'] ?? 'Scheduled')))) ?>"><?= e($schedule['status'] ?? 'Scheduled') ?></span>
        <dl>
            <div><dt><?= $isOrganization ? 'Farmer Organization' : 'Farmer / Seller' ?></dt><dd><?= e($scheduledName) ?></dd></div>
            <?php if ($isOrganization): ?><div><dt>Representative</dt><dd><?= e($representativeName) ?></dd></div><?php endif ?>
            <div><dt>Scheduled Delivery</dt><dd><?= e($scheduledDate->format('F j, Y')) ?></dd></div>
            <div><dt>Valid Until</dt><dd><?= e($validUntil->format('F j, Y')) ?></dd></div>
            <div><dt>Expected Delivery</dt><dd><?= e($expectedBags) ?> bags / <?= e($expectedKilograms) ?> kg</dd></div>
            <div><dt>Receiving Facility</dt><dd><?= e($facility) ?></dd></div>
        </dl>
    </section>

    <article class="confirmation-paper po-confirmation-paper" lang="<?= $isFilipino ? 'fil' : 'en' ?>">
        <header class="confirmation-header po-confirmation-header">
            <img class="confirmation-logo" src="assets/images/nfa-logo-official.png" alt="National Food Authority logo">
            <div class="confirmation-agency">
                <p>Republic of the Philippines</p>
                <h1>National Food Authority</h1>
                <strong><?= e($labels['form']) ?></strong>
            </div>
            <div class="confirmation-copy-mark"><?= e($labels['copy']) ?></div>
        </header>

        <section class="po-office-strip">
            <div><span><?= e($labels['region']) ?></span><strong><?= e($schedule['region_name'] ?? '') ?></strong></div>
            <div><span><?= e($labels['branch']) ?></span><strong><?= e($schedule['branch_name'] ?? '') ?></strong></div>
        </section>

        <section class="po-meta-grid">
            <div class="is-wide"><span><?= e($labels['reference']) ?></span><strong><?= e($schedule['confirmation_code']) ?></strong></div>
            <div><span><?= e($labels['date']) ?></span><strong><?= e($formatDate($issuedDate)) ?></strong></div>
            <div><span><?= e($labels['validity']) ?></span><strong><?= e($formatDate($scheduledDate)) ?> - <?= e($formatDate($validUntil)) ?></strong></div>
        </section>

        <section class="po-party-grid">
            <?php if ($isOrganization): ?>
                <div class="is-full"><span><?= e($labels['member_name']) ?></span><?= $blank ?></div>
                <div class="is-full"><span><?= e($labels['rsbsa']) ?></span><?= $blank ?></div>
                <div><span><?= e($labels['address']) ?></span><?= $blank ?></div>
                <div><span><?= e($labels['contact']) ?></span><?= $blank ?></div>
                <div class="is-full"><span><?= e($labels['mao']) ?></span><?= $blank ?></div>
                <div class="is-full"><span><?= e($labels['organization']) ?></span><strong><?= e($scheduledName) ?></strong></div>
                <div class="is-full"><span><?= e($labels['representative']) ?></span><strong><?= $representativeName !== '' ? e($representativeName) : $blank ?></strong></div>
            <?php else: ?>
                <div class="is-full"><span><?= e($labels['individual_name']) ?></span><strong><?= e($scheduledName) ?></strong></div>
                <div class="is-full"><span><?= e($labels['rsbsa']) ?></span><strong><?= $farmerRsbsa !== '' ? e($farmerRsbsa) : $blank ?></strong></div>
                <div><span><?= e($labels['address']) ?></span><strong><?= $farmerAddress !== '' ? e($farmerAddress) : $blank ?></strong></div>
                <div><span><?= e($labels['contact']) ?></span><strong><?= $farmerContact !== '' ? e($farmerContact) : $blank ?></strong></div>
                <div class="is-full"><span><?= e($labels['mao']) ?></span><strong><?= $maoCertification !== '' ? e($maoCertification) : $blank ?></strong></div>
            <?php endif ?>
        </section>

        <p class="po-salutation"><?= e($labels['dear']) ?> <strong><?= e($recipientName) ?></strong>,</p>
        <p class="po-intro"><?= e($labels['intro']) ?></p>

        <section class="po-purchase-details">
            <h2><?= e($labels['details']) ?></h2>
            <div class="po-detail-row"><span><?= e($labels['product']) ?></span><strong><?= e($labels['palay']) ?></strong></div>
            <div class="po-detail-row"><span><?= e($labels['quantity']) ?></span><strong><?= e($expectedBags) ?> <?= $isFilipino ? 'sako' : 'bags' ?> / <?= e($expectedKilograms) ?> <?= $isFilipino ? 'kilo' : 'kilograms' ?></strong></div>
            <?php if ($isOrganization): ?><div class="po-detail-row"><span><?= e($labels['members']) ?></span><strong><?= $labels['members_value'] ?></strong></div><?php endif ?>
            <div class="po-detail-row"><span><?= e($labels['price']) ?></span><p><?= e($labels['price_value']) ?></p></div>
            <div class="po-detail-row is-tall"><span><?= e($labels['quality']) ?></span><p><?= e($labels['quality_value']) ?></p></div>
            <div class="po-detail-row"><span><?= e($labels['sample_date']) ?></span><strong><?= $blank ?></strong></div>
            <div class="po-detail-row"><span><?= e($labels['bulk_date']) ?></span><strong><?= e($formatDate($scheduledDate)) ?></strong></div>
            <div class="po-detail-row"><span><?= e($labels['warehouse']) ?></span><strong><?= e($facility) ?></strong></div>
        </section>

        <p class="po-payment"><?= $labels['payment'] ?></p>

        <section class="po-terms">
            <h2><?= e($labels['terms']) ?></h2>
            <ol><?php foreach ($terms as $term): ?><li><?= e($term) ?></li><?php endforeach ?></ol>
        </section>

        <section class="po-schedule-notes">
            <h2><?= e($labels['notes_label']) ?></h2>
            <ol><?php foreach ($scheduleNotes as $note): ?><li><?= e($note) ?></li><?php endforeach ?></ol>
        </section>

        <p class="po-signing-date"><?= $labels['signing'] ?></p>
        <section class="po-signatures">
            <div><span class="po-signature-line"></span><strong><?= e($labels['nfa_signature']) ?></strong><p><?= e($labels['name']) ?>: <?= $bookingUserName !== '' ? e($bookingUserName) : $blank ?></p><p><?= e($labels['position']) ?>: <?= $bookingUserPosition !== '' ? e($bookingUserPosition) : $blank ?></p></div>
            <div><span class="po-signature-line"></span><strong><?= e($labels['farmer_signature']) ?></strong><p><?= e($labels['name']) ?>: <?= e($recipientName) ?></p></div>
        </section>

        <section class="po-tracking">
            <img src="<?= e($publicStatusQr) ?>" alt="QR code for the online appointment status">
            <div>
                <h2><?= e($labels['tracking_title']) ?></h2>
                <p><?= e($labels['tracking_text']) ?></p>
                <strong><?= e($publicStatusUrl) ?></strong>
                <small><?= e($labels['tracking_privacy']) ?></small>
            </div>
        </section>

        <footer class="po-footer">NFA-FSR / <?= e($labels['form']) ?> / <?= e($schedule['confirmation_code']) ?></footer>
    </article>
</section>
