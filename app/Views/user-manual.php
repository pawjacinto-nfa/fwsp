<?php
$currentRole = (string) ($_SESSION['role'] ?? 'Read-Only User');
$canEncode = in_array($currentRole, ['Warehouse Personnel', 'System Admin'], true);
$canReviewRecords = in_array($currentRole, ['Manager', 'Warehouse Personnel', 'System Admin'], true);
$canManageLibraries = $canEncode;
$isSystemAdmin = $currentRole === 'System Admin';

$allManualSections = [
    'system-overview' => 'System Overview',
    'getting-started' => 'Getting Started',
    'roles' => 'Roles & Access',
    'dashboard' => 'Dashboard',
    'farmer-profiles' => 'Farmer Profiles',
    'organizations' => 'Farmer Classifications',
    'deliveries' => 'Delivery Encoding',
    'records' => 'Records',
    'reports' => 'Reports & Analytics',
    'libraries' => 'Libraries',
    'account' => 'Account & Notifications',
    'support' => 'Tech Support',
    'administration' => 'System Administration',
    'about-us' => 'About Us',
    'troubleshooting' => 'Troubleshooting',
    'versions' => 'Versions',
];
$manualSections = [];
foreach ($allManualSections as $sectionId => $sectionLabel) {
    $allowed = match ($sectionId) {
        'dashboard', 'farmer-profiles', 'deliveries' => $canEncode,
        'organizations', 'records' => $canReviewRecords,
        'libraries' => $canManageLibraries,
        'administration' => $isSystemAdmin,
        default => true,
    };
    if ($allowed) {
        $manualSections[$sectionId] = $sectionLabel;
    }
}
$manualSectionNumbers = [];
foreach (array_keys($manualSections) as $index => $sectionId) {
    $manualSectionNumbers[$sectionId] = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
}
$roleDescriptions = [
    'Manager' => 'Review Records, generate Reports, and use Help resources for the assigned location.',
    'Warehouse Personnel' => 'Use all operational modules, including encoding, records, libraries, reports, and Help.',
    'Read-Only User' => 'View Reports only. Report signatories and operational tools are unavailable.',
    'System Admin' => 'Use every system module, including user control, audits, and support administration.',
];
?>
<section class="workspace-section user-manual-page">
    <button class="floating-print-button no-print" type="button" onclick="window.print()" aria-label="Print or save the user's manual as PDF" title="Print manual">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M7 8V3h10v5"></path>
            <path d="M7 17H5a3 3 0 0 1-3-3v-3a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v3a3 3 0 0 1-3 3h-2"></path>
            <path d="M7 14h10v7H7z"></path>
            <path d="M17 11h.01"></path>
        </svg>
    </button>

    <header class="manual-hero">
        <div>
            <p class="eyebrow">NFA Farmer-Seller Registry</p>
            <h1>User's Manual</h1>
            <p class="manual-lead">A focused guide to the features available to your <?= e($currentRole) ?> account.</p>
            <div class="manual-print-meta manual-print-only">
                <p>Official System Guide</p>
                <p>Prepared for <?= e($currentRole) ?> users</p>
                <p>Printed <?= e(date('F j, Y')) ?></p>
            </div>
        </div>
        <div class="manual-version" aria-label="Manual information">
            <span>System guide</span>
            <strong><?= e($currentRole) ?></strong>
        </div>
    </header>

    <section class="manual-print-toc manual-print-only" aria-label="Printed table of contents">
        <p class="manual-kicker">Contents</p>
        <h2>Table of Contents</h2>
        <ol>
            <?php foreach ($manualSections as $sectionLabel): ?>
                <li><?= e($sectionLabel) ?></li>
            <?php endforeach; ?>
        </ol>
    </section>

    <div class="manual-layout">
        <aside class="manual-sidebar" aria-label="User manual navigation">
            <div class="manual-nav-card">
                <p>On this page</p>
                <nav>
                    <?php foreach ($manualSections as $sectionId => $sectionLabel): ?>
                        <a href="#<?= e($sectionId) ?>"><?= e($sectionLabel) ?></a>
                    <?php endforeach; ?>
                </nav>
                <a class="manual-support-link" href="index.php?page=tech-support">Need more help? Open Tech Support</a>
            </div>
        </aside>

        <article class="manual-content">
            <section class="manual-section" id="system-overview">
                <p class="manual-kicker"><?= e($manualSectionNumbers['system-overview']) ?></p>
                <h2>System Overview</h2>

                <h3>Introduction</h3>
                <p>The NFA Farmer-Seller Registry is a web-based information system developed to support the National Food Authority's management of farmer-seller profiles and palay procurement records. It provides authorized users with a single workspace for registering farmers and farmer organizations, recording deliveries, retrieving records, and preparing reports for operational use.</p>

                <h3>Background</h3>
                <p>Before the development of this system, the NFA did not have a dedicated system for centrally managing farmer-seller information and palay delivery records. Information could be handled through separate files or manual processes, making it more difficult to maintain complete, consistent, and readily available records. The Farmer-Seller Registry was created to address this need by organizing relevant data in one system and making it easier for authorized personnel to access reliable information for day-to-day operations and reporting.</p>

                <h3>Objectives</h3>
                <ul class="manual-list">
                    <li>Maintain accurate and organized profiles of individual farmer-sellers and farmer organizations.</li>
                    <li>Record palay delivery transactions in a timely and consistent manner.</li>
                    <li>Provide authorized users with searchable records and reports to support monitoring and decision-making.</li>
                    <li>Improve data quality through role-based access, required information, and validation of key records.</li>
                    <li>Produce operational and sex-disaggregated reports from a centralized source of information.</li>
                </ul>

                <h3>Scope and Limitations</h3>
                <p>The system covers farmer-seller and farmer-organization registration, palay delivery encoding, record searching, location and reference-list maintenance, reports, user access management, and technical-support concerns. Access to these functions depends on the user's assigned role and organizational location.</p>
                <div class="manual-callout is-warning">
                    <strong>System limitations</strong>
                    <p>The accuracy of reports and records depends on the completeness and correctness of encoded information. The system is intended for authorized NFA personnel and its assigned operational scope; it does not replace required NFA approvals, policies, or source-document verification. Users may only view or manage information allowed by their role and location assignment.</p>
                </div>
            </section>

            <section class="manual-section" id="getting-started">
                <p class="manual-kicker"><?= e($manualSectionNumbers['getting-started']) ?></p>
                <h2>Getting Started</h2>
                <p>The Farmer-Seller Registry is used to maintain farmer and farmer-organization profiles, capture palay delivery transactions, and prepare operational and sex-disaggregated reports.</p>
                <div class="manual-callout">
                    <strong>Before you begin</strong>
                    <p>Use an activated account and confirm that the location shown under your name is correct. Your assigned region, branch, province, and facility determine the records and default filters available to you.</p>
                </div>
                <h3>Sign in and sign out</h3>
                <ol class="manual-steps">
                    <li><span>1</span><div>Select <strong>Login</strong> in the upper-right corner.</div></li>
                    <li><span>2</span><div>Enter your registered email address and password, then submit the form.</div></li>
                    <li><span>3</span><div>When finished, select <strong>Logout</strong>. Always log out on shared computers.</div></li>
                </ol>
                <p>New users may select <strong>Register</strong>. Registration does not grant immediate access; a System Admin must activate the account and assign its role and organizational location.</p>
            </section>

            <section class="manual-section" id="roles">
                <p class="manual-kicker"><?= e($manualSectionNumbers['roles']) ?></p>
                <h2>Roles and Access</h2>
                <div class="manual-table-wrap">
                    <table class="manual-table">
                        <thead><tr><th>Your role</th><th>Your access</th></tr></thead>
                        <tbody>
                            <tr><td><strong><?= e($currentRole) ?></strong></td><td><?= e($roleDescriptions[$currentRole] ?? 'Access is determined by your assigned role.') ?></td></tr>
                        </tbody>
                    </table>
                </div>
                <p>If a menu or action is not displayed, it is normally outside your assigned role. Contact your System Admin if your duties have changed.</p>
            </section>

            <?php if ($canEncode): ?>
            <section class="manual-section" id="dashboard">
                <p class="manual-kicker"><?= e($manualSectionNumbers['dashboard']) ?></p>
                <h2>Dashboard and Main Navigation</h2>
                <p>After login, use the top menu to move between system modules. The dashboard provides activity shortcuts for users who can encode records. Select the activity card that matches the work you are about to perform.</p>
                <div class="manual-grid">
                    <div class="manual-feature"><strong>Encode</strong><span>Create a farmer profile or record an individual or organization delivery.</span></div>
                    <div class="manual-feature"><strong>Records</strong><span>Search farmers, farmer organizations, and transactions.</span></div>
                    <div class="manual-feature"><strong>Library</strong><span>Maintain the operational location and Central Office reference lists.</span></div>
                    <div class="manual-feature"><strong>Reports</strong><span>Generate summary, full-list, and sex-disaggregated outputs.</span></div>
                    <div class="manual-feature"><strong>Support</strong><span>Read this manual, submit a concern, or manage user access if authorized.</span></div>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($canEncode): ?>
            <section class="manual-section" id="farmer-profiles">
                <p class="manual-kicker"><?= e($manualSectionNumbers['farmer-profiles']) ?></p>
                <h2>Farmer Profiles</h2>
                <p>Open <strong>Encode → Farmer Profile</strong> to register a farmer before recording their first individual delivery.</p>
                <ol class="manual-steps">
                    <li><span>1</span><div>Enter the farmer's RSBSA number and complete the name, address, birth, civil-status, and contact fields.</div></li>
                    <li><span>2</span><div>Complete sex, gender orientation, sector, and landholding information as applicable.</div></li>
                    <li><span>3</span><div>Enter production details such as irrigated area, palay location, harvest area, and average yield.</div></li>
                    <li><span>4</span><div>Select the farmer organization and assigned facility when applicable, add a photo if available, then save.</div></li>
                </ol>
                <div class="manual-callout is-warning"><strong>Data quality</strong><p>Verify the RSBSA number and spelling before saving. Use accurate, current contact and location information; these values affect searches and reports.</p></div>
                <h3>Review or update a farmer</h3>
                <p>Go to <strong>Records → Farmers</strong>, search by name or RSBSA number, and open the farmer record. Review the profile and use the available edit action to correct or update details.</p>
            </section>

            <?php endif; ?>

            <?php if ($canReviewRecords): ?>
            <section class="manual-section" id="organizations">
                <p class="manual-kicker"><?= e($manualSectionNumbers['organizations']) ?></p>
                <h2>Farmer Classifications</h2>
                <?php if (!$canEncode): ?>
                <p>Open <strong>Records → Farmer Classifications</strong> to review Farmer Organizations and Indigenous People Groups for your assigned location. Select <strong>View</strong> to inspect a group and its members. Manager access is read-only on this page.</p>
                <?php else: ?>
                <p>Open <strong>Records → Farmer Classifications</strong>, then choose Farmer Organizations or Indigenous People Groups. Enter the official group name, total membership, and office location, then save. Select an existing group to view or edit its details and membership information.</p>
                <p>Create the organization before encoding an organization delivery so it can be selected consistently and reported under one official name.</p>
                <?php endif; ?>
            </section>

            <?php endif; ?>

            <?php if ($canEncode): ?>
            <section class="manual-section" id="deliveries">
                <p class="manual-kicker"><?= e($manualSectionNumbers['deliveries']) ?></p>
                <h2>Delivery Encoding</h2>
                <h3>Individual delivery</h3>
                <ol class="manual-steps">
                    <li><span>1</span><div>Open <strong>Encode → Individual Delivery</strong> and locate the registered farmer by RSBSA number.</div></li>
                    <li><span>2</span><div>Select the procurement method and confirm the delivery date, warehouse stock receipt (WSR), buying price, net kilograms, number of bags, and receiving facility.</div></li>
                    <li><span>3</span><div>Review all figures against the source document, then submit the transaction.</div></li>
                </ol>
                <div class="manual-callout is-warning"><strong>Annual delivery limit</strong><p>Each individual farmer may deliver up to 400 bags per calendar year. The system blocks quantities above the remaining allowance, notifies the encoder when the farmer reaches 400 bags, and marks that farmer with a red exclamation point in the Farmers list.</p></div>
                <h3>Farmer-organization delivery</h3>
                <ol class="manual-steps">
                    <li><span>1</span><div>Open <strong>Encode → Farmers Organization Delivery</strong> and select the organization.</div></li>
                    <li><span>2</span><div>Enter the authorized representative, member and farm-area details, procurement data, delivery date, WSR, price, weight, bags, and facility.</div></li>
                    <li><span>3</span><div>Select the farmer-members included in the delivery where required, verify the totals, and submit.</div></li>
                </ol>
                <div class="manual-callout"><strong>Cross-location deliveries</strong><p>When an individual farmer sells palay to a facility other than their home facility, the system may notify responsible users at the farmer's assigned location.</p></div>
            </section>

            <?php endif; ?>

            <?php if ($canReviewRecords): ?>
            <section class="manual-section" id="records">
                <p class="manual-kicker"><?= e($manualSectionNumbers['records']) ?></p>
                <h2>Searching and Reviewing Records</h2>
                <p>Use <strong>Records → Farmers</strong> or <strong>Records → Transactions</strong>. Search by farmer name, RSBSA, or WSR and narrow results by location, procurement method, or date range. Select <strong>Filter</strong> to apply the criteria.</p>
                <ul class="manual-list">
                    <li>Open a farmer row to review the complete profile.</li>
                    <li>Open a transaction to review delivery details and, for organization deliveries, included farmer-members.</li>
                    <li>Use the table headings to sort results and the page controls to move through longer lists.</li>
                    <li>Clear or replace filters when a known record does not appear.</li>
                </ul>
            </section>

            <?php endif; ?>

            <section class="manual-section" id="reports">
                <p class="manual-kicker"><?= e($manualSectionNumbers['reports']) ?></p>
                <h2>Reports and SDD Analytics</h2>
                <p>Open <strong>Reports → Summary Report</strong> and choose the required output. Available formats include the standard summary, branch/region summary, SDD summary, and the full FWSP list of individual farmers and organizations.</p>
                <ol class="manual-steps">
                    <li><span>1</span><div>Select the report format or result basis.</div></li>
                    <li><span>2</span><div>Set the date range and applicable region, branch, province, and facility filters.</div></li>
                    <li><span>3</span><div>Generate the report, review its title, scope, totals, and rows, then use the print option when a hard copy or PDF is needed.</div></li>
                </ol>
                <h3>Sex-disaggregated data</h3>
                <p>Open <strong>Reports → SDD Analytics</strong> to view sex, sectoral, and SOGIE distributions. Apply location and date filters before interpreting charts so the population and reporting period are clear.</p>
                <div class="manual-callout is-warning"><strong>Reporting check</strong><p>The default reporting period begins on January 1 of the current year and ends today. Always verify the displayed dates and location scope before issuing a report.</p></div>
            </section>

            <?php if ($canManageLibraries): ?>
            <section class="manual-section" id="libraries">
                <p class="manual-kicker"><?= e($manualSectionNumbers['libraries']) ?></p>
                <h2>Reference Libraries</h2>
                <p>Authorized users can maintain the lists used throughout forms and filters.</p>
                <div class="manual-grid two-column">
                    <div class="manual-feature"><strong>Location Library</strong><span>Add and maintain regions, branches, provinces, and facilities. Preserve the correct hierarchy so users and records receive valid locations.</span></div>
                    <div class="manual-feature"><strong>Central Office Directory</strong><span>Add and maintain departments, divisions, and services or units used for Central Office assignments.</span></div>
                </div>
                <p>Before deleting or renaming an entry, check whether it is already used by accounts or records. Prefer correcting a spelling error over creating a duplicate location.</p>
            </section>

            <?php endif; ?>

            <section class="manual-section" id="account">
                <p class="manual-kicker"><?= e($manualSectionNumbers['account']) ?></p>
                <h2>Account and Notifications</h2>
                <p>Select your name in the upper-right corner to see notifications and the link to <strong>Edit Profile Settings</strong>. Notifications identify important record and support activity; select one to open its related page. Use <strong>Clear all</strong> only when you no longer need the current list.</p>
                <p>On the Account page, update your profile image, name, email, contact number, designation, or password. Enter and confirm the same new password before saving. Organizational location may be controlled by your administrator.</p>
            </section>

            <section class="manual-section" id="support">
                <p class="manual-kicker"><?= e($manualSectionNumbers['support']) ?></p>
                <h2>Tech Support</h2>
                <p>Open <strong>Help → Tech Support</strong> when this manual does not resolve a problem. Non-admin users with Help access can submit and track concerns; System Admins receive, reply to, complete, and archive tickets.</p>
                <ol class="manual-steps">
                    <li><span>1</span><div>Enter a short, specific title and select the closest category.</div></li>
                    <li><span>2</span><div>Describe the page, expected result, actual result, and exact steps that reproduce the issue.</div></li>
                    <li><span>3</span><div>Attach a JPG, PNG, or WebP screenshot if it helps explain the concern, then submit.</div></li>
                    <li><span>4</span><div>Return to the ticket list to read replies and status updates. Archive it when no longer needed.</div></li>
                </ol>
                <p>Do not include passwords or other unnecessary sensitive information in a ticket or screenshot.</p>
            </section>

            <?php if ($isSystemAdmin): ?>
            <section class="manual-section" id="administration">
                <p class="manual-kicker"><?= e($manualSectionNumbers['administration']) ?></p>
                <h2>System Administration</h2>
                <p>System Admins can open <strong>Help → User Control</strong> to review registered accounts, assign roles and locations, and activate or update access. The page also provides audit logs for reviewing significant system activity.</p>
                <ul class="manual-list">
                    <li>Grant the minimum role needed for the user's duties.</li>
                    <li>Confirm the correct location before activating an account.</li>
                    <li>Review audit activity when investigating unexpected changes.</li>
                    <li>Use the Tech Support queue to reply to users, mark resolved tickets completed, and archive closed work.</li>
                </ul>
            </section>

            <?php endif; ?>

            <section class="manual-section manual-about-section" id="about-us">
                <p class="manual-kicker"><?= e($manualSectionNumbers['about-us']) ?></p>
                <h2>About Us</h2>
                <p>The NFA Farmer-Seller Registry supports the National Food Authority in maintaining reliable farmer-seller information, documenting palay procurement transactions, and producing timely operational and sex-disaggregated reports.</p>
                <div class="manual-about-card">
                    <p class="eyebrow">System Development Team</p>
                    <h3>Corporate Planning and Management Services Department</h3>
                    <p><strong>Information and Communications Technology Support Division</strong><br>Software Development Unit</p>
                    <p>Created by <strong>Paulo Anthony A. Jacinto</strong>, Computer Programmer II, under the supervision of <strong>Rainier John S. Dela Cruz</strong>, Information Systems Analyst III, and <strong>Gary R. Riparip</strong>, Division Chief - ICTSD.</p>
                </div>
                <p>The team develops and supports information systems that improve data quality, accessibility, and operational decision-making across the organization.</p>
            </section>

            <section class="manual-section" id="troubleshooting">
                <p class="manual-kicker"><?= e($manualSectionNumbers['troubleshooting']) ?></p>
                <h2>Troubleshooting and Good Practice</h2>
                <div class="manual-faq">
                    <details><summary>I cannot see a menu or button.</summary><p>Your account role may not include that function. Confirm your role with a System Admin. Read-Only Users are limited to Reports and cannot use signatories.</p></details>
                    <details><summary>A farmer or transaction is missing from the results.</summary><p>Remove restrictive filters, broaden the date range, verify the spelling or identifier, and confirm that you are searching the correct assigned location.</p></details>
                    <details><summary>A report total looks incomplete.</summary><p>Check the format, date range, result basis, and every location filter. Reports only include records matching the displayed scope.</p></details>
                    <details><summary>My form will not submit.</summary><p>Complete all required fields, check number and date formats, and look for an on-screen validation message. Avoid refreshing immediately after a successful submission to prevent confusion.</p></details>
                    <details><summary>I forgot my password.</summary><p>Use the password-reset option from the login window and follow the approval and reset process. If you still cannot sign in, contact the system administrator.</p></details>
                </div>
                <div class="manual-closing">
                    <h3>Still need assistance?</h3>
                    <p>Send the developer team a detailed ticket and include the affected page and steps to reproduce the issue.</p>
                    <a class="btn btn-success" href="index.php?page=tech-support">Go to Tech Support</a>
                </div>
            </section>

            <section class="manual-section manual-versions-section" id="versions">
                <p class="manual-kicker"><?= e($manualSectionNumbers['versions']) ?></p>
                <h2>Versions</h2>
                <p>This history records the major system releases and feature updates completed for the Farmer-Seller Registry.</p>
                <div class="manual-version-timeline" aria-label="System version history">
                    <article class="manual-version-entry">
                        <div class="manual-version-date"><time datetime="2026-06-25">June 25, 2026</time><span>Initial release</span></div>
                        <div><h3>Farmer-Seller Registry launched</h3><ul class="manual-list">
                            <li>Created the application foundation: database connection, page layout, navigation, login, registration, logout, and password-reset request screens.</li>
                            <li>Added four account roles: System Admin, Manager, Warehouse Personnel, and Read-Only User.</li>
                            <li>Added a dashboard with shortcuts for farmer registration, individual delivery, and farmer-organization delivery.</li>
                            <li>Added farmer profile encoding with RSBSA number, personal details, address, civil status, contact details, sex, SOGIE, sector, landholding, production data, organization, facility, and photo upload.</li>
                            <li>Added farmer-organization records with organization name, membership count, office location, and member listings.</li>
                            <li>Added individual and farmer-organization delivery forms for procurement method, delivery date, WSR number, price, net kilograms, 50 kg bags, receiving facility, representative, member count, and farm area.</li>
                            <li>Added farmer and transaction record lists with search, filtering, record viewing, and location-based scope.</li>
                            <li>Added Region, Branch, Province, Facility, and Central Office directory libraries for maintaining reference data.</li>
                            <li>Added summary reports, regional reports, the initial sex-disaggregated analytics view, report filters, and printing.</li>
                            <li>Added account profile editing, profile images, notifications, and activity tracking.</li>
                            <li>Added a tech-support ticket queue with categories, status tracking, replies, attachments, completion, and archiving.</li>
                            <li>Added initial database schema and location-data seed files for deployment and test data preparation.</li>
                        </ul></div>
                    </article>
                    <article class="manual-version-entry">
                        <div class="manual-version-date"><time datetime="2026-06-30">June 30, 2026</time><span>Major feature update</span></div>
                        <div><h3>Reporting and administration expanded</h3><ul class="manual-list">
                            <li>Added the full FWSP report, including separate individual-farmer and farmer-organization lists and their delivery details.</li>
                            <li>Added the IP Group Delivery report and marked IP-group deliveries separately from regular farmer-organization deliveries.</li>
                            <li>Added the SDD Summary Report with national and location-filtered results, including farmer organizations and Indigenous People Groups as separate classifications.</li>
                            <li>Added report signatory settings: authorized users can add, edit, and remove their own signatory names and designations.</li>
                            <li>Added a Report Settings page and prevented Read-Only Users from accessing signatory controls.</li>
                            <li>Added a Database Management page for System Admins and a database-schema inspection model.</li>
                            <li>Added role migration for legacy accounts, mapping Super Admin, Warehouse Supervisor, Regional/Branch Manager, and Viewer to the current role names.</li>
                            <li>Restricted Read-Only Users to reports and restricted user control, database management, and access approvals to System Admins.</li>
                            <li>Added password-reset approval handling for System Admins and updated registration to collect username/employee number, designation, password confirmation, and required office scope.</li>
                            <li>Added a generated Farmer Key, displayed it in farmer forms and records, and added an Indigenous Sector Group Delivery Member flag to farmer profiles.</li>
                            <li>Added Farmer Organization and Indigenous People Group tabs, classification type, assigned facility, editing, and location filters.</li>
                            <li>Added selectable delivery members for organization deliveries, stored the members per transaction, and used them in reports.</li>
                            <li>Added automatic delivery total-cost calculation, Amount Paid, metric-ton conversion, and transaction-table columns for these values.</li>
                            <li>Added the 400-bag annual individual-delivery limit, a warning marker in farmer lists, and notifications when the limit is reached.</li>
                            <li>Added delete controls with confirmation prompts for regions, branches, provinces, and facilities.</li>
                            <li>Added configurable required levels to the reusable location selector and improved Central Office, farmer, record, support, and user-management views.</li>
                            <li>Added the role-aware User's Manual, including printable contents and a print-to-PDF control.</li>
                        </ul></div>
                    </article>
                    <article class="manual-version-entry is-current">
                        <div class="manual-version-date"><time datetime="2026-07-17">July 17, 2026</time><span>Current release</span></div>
                        <div><h3>Reporting dashboard and offline readiness improved</h3><ul class="manual-list">
                            <li>Added the <strong>Summary Report with SDD</strong>, organized by region, seller classification, sex, and month.</li>
                            <li>Added monthly People Count, Quantity Sold in 50 kg bags and metric tons, Amount Paid, and cumulative totals to that report.</li>
                            <li>Added the new report to the report-format selector and preserved the selected report type when resetting filters.</li>
                            <li>Improved print preparation for report sheets, including repeating report-title rows in printed output.</li>
                            <li>Added optional offline mode for Warehouse Personnel and System Admin accounts from Account Settings.</li>
                            <li>Added a service worker that caches the application shell and individual and organization delivery pages for offline use.</li>
                            <li>Made delivery forms available while offline; records, libraries, reports, Help, and other unavailable menus are hidden until the connection returns.</li>
                            <li>Added local offline delivery storage, a pending-input badge, connection-status messages, a guided offline-workspace setup, and an upload progress dialog.</li>
                            <li>Added a unique offline control number to every queued delivery and server-side duplicate protection, so a retried upload does not create another transaction.</li>
                            <li>Added the transaction creator ID and client control number to the transaction data structure.</li>
                            <li>Refined the sign-in modal with the Farmer Seller Registry logo, branded title, and full-width Login button.</li>
                            <li>Added Farmer Seller Registry logo assets and updated visual styling for reports, dashboard elements, forms, navigation, account screens, and offline status messages.</li>
                        </ul></div>
                    </article>
                </div>
            </section>
        </article>
    </div>
</section>
