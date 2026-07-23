<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Activity;
use App\Models\CentralOffice;
use App\Models\DatabaseSchema;
use App\Models\DisplayPhoto;
use App\Models\Farmer;
use App\Models\FarmerOrganization;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Report;
use App\Models\Signatory;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;

final class DashboardController
{
    private const ROLES = ['System Admin', 'Manager', 'Warehouse Personnel', 'Read-Only User'];

    public function __construct()
    {
        User::migrateLegacyRoles();
        $legacyRoles = [
            'Super Admin' => 'System Admin',
            'Regional/Branch Manager' => 'Manager',
            'Warehouse Supervisor' => 'Warehouse Personnel',
            'Viewer' => 'Read-Only User',
        ];
        if (isset($_SESSION['role'], $legacyRoles[$_SESSION['role']])) {
            $_SESSION['role'] = $legacyRoles[$_SESSION['role']];
        }
    }

    public function index(): void
    {
        View::render('dashboard', [
            'title' => "Farmer's Who Sold Palay to NFA",
            'alert' => $this->pullFlash(),
            'slides' => DisplayPhoto::slides(),
            'displaySettings' => DisplayPhoto::settings(),
        ]);
    }

    public function records(array $filters): void
    {
        if (!$this->authorizeRecords()) {
            return;
        }

        View::render('records', [
            'title' => 'Record Viewing',
            'alert' => $this->pullFlash(),
        ]);
    }

    public function farmerRecords(array $filters): void
    {
        if (!$this->authorizeRecords()) {
            return;
        }

        $filters = $this->withUserLocationDefaults($filters);

        View::render('records', [
            'title' => 'Farmers Records',
            'alert' => $this->pullFlash(),
            'mode' => 'farmers',
            'farmers' => Farmer::search($filters),
            'selectedFarmer' => !empty($filters['farmer_id']) ? Farmer::find((int) $filters['farmer_id']) : null,
            'filters' => $filters,
            'regions' => Location::regions(),
            'branches' => Location::branches(),
            'provinces' => Location::provinces(),
            'warehouses' => Location::warehouses(),
        ]);
    }

    public function farmerView(array $filters): void
    {
        if (!$this->authorizeRecords()) {
            return;
        }

        $farmer = !empty($filters['id']) ? Farmer::find((int) $filters['id']) : null;

        View::render('farmer-view', [
            'title' => 'Farmer Profile',
            'alert' => $this->pullFlash(),
            'farmer' => $farmer,
            'farmerOrganizations' => FarmerOrganization::all(),
        ]);
    }

    public function transactionRecords(array $filters): void
    {
        if (!$this->authorizeRecords()) {
            return;
        }

        $filters = $this->withUserLocationDefaults($filters);

        View::render('records', [
            'title' => 'Transaction Records',
            'alert' => $this->pullFlash(),
            'mode' => 'transactions',
            'transactions' => Transaction::search($filters),
            'selectedTransaction' => !empty($filters['transaction_id']) ? Transaction::find((int) $filters['transaction_id']) : null,
            'filters' => $filters,
            'regions' => Location::regions(),
            'branches' => Location::branches(),
            'provinces' => Location::provinces(),
            'warehouses' => Location::warehouses(),
        ]);
    }

    public function encodeFarmer(): void
    {
        if (!$this->authorizeEncode()) {
            return;
        }

        View::render('encode-farmer', [
            'title' => 'Encode Farmer Profile',
            'alert' => $this->pullFlash(),
            'nextFarmerKey' => Farmer::nextKeyPreview(),
            'locationDefaults' => $this->currentUserLocationValues(),
            'farmerOrganizations' => FarmerOrganization::all(),
        ]);
    }

    public function individualDelivery(): void
    {
        if (!$this->authorizeEncode()) {
            return;
        }

        View::render('delivery-individual', [
            'title' => 'Individual Delivery',
            'alert' => $this->pullFlash(),
            'farmers' => Farmer::all(),
            'locationDefaults' => $this->currentUserLocationValues(),
        ]);
    }

    public function organizationDelivery(): void
    {
        if (!$this->authorizeEncode()) {
            return;
        }

        View::render('delivery-organization', [
            'title' => 'Farmers Organization Delivery',
            'alert' => $this->pullFlash(),
            'farmers' => Farmer::all(),
            'farmerOrganizations' => FarmerOrganization::all(),
            'locationDefaults' => $this->currentUserLocationValues(),
        ]);
    }

    public function reports(array $filters): void
    {
        if (!$this->authorizeAuthenticated()) {
            return;
        }

        $view = $filters['view'] ?? 'summary';
        $filters = $this->withUserLocationDefaults($filters);
        $filters = $this->withCurrentYearDateDefaults($filters);
        $scope = $filters['scope'] ?? 'region';
        $allowedReportFormats = ['default', 'branch_region', 'sdd_summary', 'monthly_sdd_summary', 'full_list_fwsp', 'ip_group_delivery'];
        $requestedReportFormat = $filters['report_format'] ?? 'default';
        $reportFormat = in_array($requestedReportFormat, $allowedReportFormats, true)
            ? $requestedReportFormat
            : 'default';

        View::render('reports', [
            'title' => $view === 'sectoral' ? 'Sex Disaggregated Data Analytics' : 'Reports',
            'alert' => $this->pullFlash(),
            'view' => $view,
            'scope' => $scope,
            'reportFormat' => $reportFormat,
            'rows' => match ($reportFormat) {
                'branch_region' => Report::summaryByBranchRegion($filters),
                'sdd_summary' => Report::sddSummary($filters),
                'monthly_sdd_summary' => Report::monthlySddSummary($filters),
                'full_list_fwsp' => [
                    'individual' => Report::fullListIndividual($filters),
                    'organizations' => Report::fullListFarmerOrganizations($filters),
                ],
                'ip_group_delivery' => Report::ipGroupDeliveries($filters),
                default => Report::summary($scope, $filters),
            },
            'filters' => $filters,
            'sectoralScore' => $view === 'sectoral' ? Report::sectoralScore($filters) : null,
            'signatories' => $this->isReadOnlyUser() ? [] : Signatory::forUser((int) $_SESSION['user_id']),
        ]);
    }

    public function reportSettings(): void
    {
        if (!$this->authorizeSignatories()) {
            return;
        }

        View::render('report-settings', [
            'title' => 'Report Settings',
            'alert' => $this->pullFlash(),
            'signatories' => Signatory::forUser((int) $_SESSION['user_id']),
        ]);
    }

    public function storeSignatories(array $payload): void
    {
        if (!$this->authorizeSignatories()) {
            return;
        }

        $names = $this->arrayValue($payload['full_name'] ?? []);
        $designations = $this->arrayValue($payload['designation'] ?? []);
        $saved = 0;
        foreach ($names as $index => $fullName) {
            $designation = $designations[$index] ?? '';
            if ($fullName === '' && $designation === '') {
                continue;
            }
            if ($fullName === '' || $designation === '') {
                $this->flash('danger', 'Every signatory requires both a full name and designation.');
                $this->redirect('?page=report-settings');
                return;
            }

            Signatory::create((int) $_SESSION['user_id'], $fullName, $designation);
            $saved++;
        }

        $this->flash($saved > 0 ? 'success' : 'warning', $saved > 0 ? "{$saved} signatory record(s) saved." : 'Enter at least one signatory.');
        $this->redirect('?page=report-settings');
    }

    public function updateSignatory(array $payload): void
    {
        if (!$this->authorizeSignatories()) {
            return;
        }

        $id = (int) ($payload['id'] ?? 0);
        $fullName = $this->clean($payload['full_name'] ?? '');
        $designation = $this->clean($payload['designation'] ?? '');
        if ($id <= 0 || $fullName === '' || $designation === '') {
            $this->flash('danger', 'Full name and designation are required.');
        } else {
            Signatory::update($id, (int) $_SESSION['user_id'], $fullName, $designation);
            $this->flash('success', 'Signatory updated.');
        }
        $this->redirect('?page=report-settings');
    }

    public function deleteSignatory(array $payload): void
    {
        if (!$this->authorizeSignatories()) {
            return;
        }

        Signatory::delete((int) ($payload['id'] ?? 0), (int) $_SESSION['user_id']);
        $this->flash('success', 'Signatory removed from your account.');
        $this->redirect('?page=report-settings');
    }

    public function sectoralReport(array $filters): void
    {
        if (!$this->authorizeAuthenticated()) {
            return;
        }

        $filters = $this->withUserLocationDefaults($filters);

        View::render('sectoral-report', [
            'title' => 'Sex Disaggregated Data Analytics',
            'alert' => $this->pullFlash(),
            'filters' => $filters,
            'sectoralScore' => Report::sectoralScore($filters),
        ]);
    }

    public function account(): void
    {
        if (!$this->authorizeAuthenticated()) {
            return;
        }

        if ($this->isReadOnlyUser()) {
            $this->flash('danger', 'Read-Only User accounts can only access Reports.');
            $this->redirect('?page=reports');
            return;
        }

        $user = !empty($_SESSION['user_id']) ? User::find((int) $_SESSION['user_id']) : null;

        View::render('account', [
            'title' => 'Account Management',
            'alert' => $this->pullFlash(),
            'user' => $user,
        ]);
    }

    public function users(): void
    {
        if (($_SESSION['role'] ?? '') !== 'System Admin') {
            $this->flash('danger', 'Only System Admin can manage user access.');
            $this->redirect();
            return;
        }

        View::render('users', [
            'title' => 'User Control Management',
            'alert' => $this->pullFlash(),
            'users' => User::all(),
            'auditLogs' => Activity::auditLogs(),
            'roles' => self::ROLES,
        ]);
    }

    public function databaseManagement(array $filters): void
    {
        if (($_SESSION['role'] ?? '') !== 'System Admin') {
            $this->flash('danger', 'Only System Admin can view database metadata.');
            $this->redirect();
            return;
        }

        $tables = DatabaseSchema::tables();
        $selectedTable = trim((string) ($filters['table'] ?? ''));
        $schema = $selectedTable !== '' ? DatabaseSchema::describe($selectedTable) : null;

        View::render('database-management', [
            'title' => 'Database Management',
            'alert' => $this->pullFlash(),
            'tables' => $tables,
            'selectedTable' => $selectedTable,
            'schema' => $schema,
        ]);
    }

    public function displaySettings(): void
    {
        if (($_SESSION['role'] ?? '') !== 'System Admin') {
            $this->flash('danger', 'Only System Admin can manage the landing page display.');
            $this->redirect();
            return;
        }

        View::render('display-settings', [
            'title' => 'Display Settings',
            'alert' => $this->pullFlash(),
            'settings' => DisplayPhoto::settings(),
            'photos' => DisplayPhoto::all(),
        ]);
    }

    public function techSupport(): void
    {
        if (!$this->authorizeHelp()) {
            return;
        }

        $isSuperAdmin = ($_SESSION['role'] ?? '') === 'System Admin';

        View::render('tech-support', [
            'title' => 'Tech Support',
            'alert' => $this->pullFlash(),
            'categories' => SupportTicket::CATEGORIES,
            'tickets' => $isSuperAdmin
                ? SupportTicket::allForAdmin()
                : SupportTicket::forReporter((int) $_SESSION['user_id']),
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function userManual(): void
    {
        if (!$this->authorizeHelp()) {
            return;
        }

        View::render('user-manual', [
            'title' => ($_SESSION['role'] ?? '') === 'System Admin' ? 'System Administrator Manual' : "User's Manual",
            'alert' => $this->pullFlash(),
        ]);
    }

    public function storeSupportTicket(array $payload, array $files): void
    {
        if (!$this->authorizeHelp()) {
            return;
        }

        if (($_SESSION['role'] ?? '') === 'System Admin') {
            $this->flash('danger', 'System Admin accounts manage tickets instead of submitting them.');
            $this->redirect('?page=tech-support');
            return;
        }

        $title = $this->clean($payload['title'] ?? '');
        $category = $this->clean($payload['category'] ?? '');
        $description = trim(strip_tags((string) ($payload['description'] ?? ''), '<br>'));

        if ($title === '' || $description === '' || !in_array($category, SupportTicket::CATEGORIES, true)) {
            $this->flash('danger', 'Please complete the ticket title, category, and description.');
            $this->redirect('?page=tech-support');
            return;
        }

        $ticketId = SupportTicket::create([
            'reporter_id' => (int) $_SESSION['user_id'],
            'title' => $title,
            'category' => $category,
            'description' => $description,
            'screenshot_path' => $this->saveSupportScreenshot($files['screenshot'] ?? null),
        ]);

        foreach (SupportTicket::superAdminIds() as $adminId) {
            Notification::add('New tech support ticket submitted: ' . $title . '.', $adminId, 'index.php?page=tech-support');
        }

        Activity::add($_SESSION['user'] . ' submitted support ticket #' . $ticketId . '.');
        $this->flash('success', 'Support ticket submitted to the developer team.');
        $this->redirect('?page=tech-support');
    }

    /** Receives a browser-captured failure. This endpoint intentionally permits anonymous reports. */
    public function storeErrorReport(array $payload): void
    {
        header('Content-Type: application/json');

        $description = trim((string) ($payload['description'] ?? ''));
        $pageUrl = trim((string) ($payload['page_url'] ?? ''));
        $browser = trim((string) ($payload['browser'] ?? ''));
        if ($description === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'The error description is required.']);
            return;
        }

        $description = mb_substr(strip_tags($description), 0, 10000);
        $context = implode("\n", array_filter([
            'Page: ' . mb_substr(strip_tags($pageUrl), 0, 1000),
            'Browser: ' . mb_substr(strip_tags($browser), 0, 1000),
        ], static fn (string $value): bool => trim($value) !== ''));
        $ticketId = SupportTicket::create([
            'reporter_id' => !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
            'title' => 'Automatic error report',
            'category' => 'System Error',
            'description' => $description . ($context !== '' ? "\n\n" . $context : ''),
            'screenshot_path' => null,
        ]);

        foreach (SupportTicket::superAdminIds() as $adminId) {
            Notification::add('New automatic error report #' . $ticketId . ' submitted.', $adminId, 'index.php?page=tech-support');
        }

        if (!empty($_SESSION['user'])) {
            Activity::add($_SESSION['user'] . ' submitted automatic error report #' . $ticketId . '.');
        }

        echo json_encode(['success' => true, 'ticket_id' => $ticketId]);
    }

    public function replySupportTicket(array $payload): void
    {
        if (!$this->authorizeHelp()) {
            return;
        }

        $ticketId = (int) ($payload['ticket_id'] ?? 0);
        $message = $this->clean($payload['message'] ?? '');
        $ticket = SupportTicket::findVisibleTo($ticketId, (int) $_SESSION['user_id'], (string) $_SESSION['role']);

        if (!$ticket || $message === '') {
            $this->flash('danger', 'Please choose a valid ticket and enter a reply.');
            $this->redirect('?page=tech-support');
            return;
        }

        if (($ticket['status'] ?? '') === 'Completed') {
            $this->flash('danger', 'This support ticket is already completed and closed for replies.');
            $this->redirect('?page=tech-support');
            return;
        }

        SupportTicket::addMessage($ticketId, (int) $_SESSION['user_id'], $message);

        if (($_SESSION['role'] ?? '') === 'System Admin') {
            if (!empty($ticket['reporter_id'])) {
                Notification::add('Developer team replied to your ticket: ' . $ticket['title'] . '.', (int) $ticket['reporter_id'], 'index.php?page=tech-support');
            }
        } else {
            foreach (SupportTicket::superAdminIds() as $adminId) {
                Notification::add('User replied to tech support ticket: ' . $ticket['title'] . '.', $adminId, 'index.php?page=tech-support');
            }
        }

        Activity::add($_SESSION['user'] . ' replied to support ticket #' . $ticketId . '.');
        $this->flash('success', 'Reply sent.');
        $this->redirect('?page=tech-support');
    }

    public function completeSupportTicket(array $payload): void
    {
        if (($_SESSION['role'] ?? '') !== 'System Admin') {
            $this->flash('danger', 'Only System Admin can mark support tickets as completed.');
            $this->redirect('?page=tech-support');
            return;
        }

        $ticketId = (int) ($payload['ticket_id'] ?? 0);
        $ticket = SupportTicket::findVisibleTo($ticketId, (int) $_SESSION['user_id'], (string) $_SESSION['role']);
        if (!$ticket) {
            $this->flash('danger', 'Support ticket was not found.');
            $this->redirect('?page=tech-support');
            return;
        }

        SupportTicket::markCompleted($ticketId, (int) $_SESSION['user_id']);
        if (!empty($ticket['reporter_id'])) {
            Notification::add('Your tech support ticket has been marked completed: ' . $ticket['title'] . '.', (int) $ticket['reporter_id'], 'index.php?page=tech-support');
        }
        Activity::add($_SESSION['user'] . ' completed support ticket #' . $ticketId . '.');
        $this->flash('success', 'Support ticket marked as completed.');
        $this->redirect('?page=tech-support');
    }

    public function archiveSupportTicket(array $payload): void
    {
        if (!$this->authorizeHelp()) {
            return;
        }

        $ticketId = (int) ($payload['ticket_id'] ?? 0);
        $ticket = SupportTicket::findVisibleTo($ticketId, (int) $_SESSION['user_id'], (string) $_SESSION['role']);
        if (!$ticket) {
            $this->flash('danger', 'Support ticket was not found.');
            $this->redirect('?page=tech-support');
            return;
        }

        SupportTicket::archiveFor($ticketId, (string) $_SESSION['role']);
        Activity::add($_SESSION['user'] . ' archived support ticket #' . $ticketId . '.');
        $this->flash('success', 'Support ticket archived.');
        $this->redirect('?page=tech-support');
    }

    public function locationLibrary(): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        View::render('location-library', [
            'title' => 'Location Library',
            'alert' => $this->pullFlash(),
            'locations' => Location::libraryRows(),
            'regions' => Location::allRegions(),
            'branches' => Location::allBranches(),
            'provinces' => Location::allProvinces(),
        ]);
    }

    public function centralOfficeLibrary(): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        View::render('central-office-library', [
            'title' => 'Central Office Directory',
            'alert' => $this->pullFlash(),
            'locations' => CentralOffice::libraryRows(),
            'departments' => CentralOffice::departments(),
            'divisions' => CentralOffice::divisions(),
        ]);
    }

    public function farmerOrganizationLibrary(array $filters = []): void
    {
        if (!$this->authorizeRecords()) {
            return;
        }

        $filters = $this->withUserLocationDefaults($filters);
        $classification = ($filters['classification'] ?? 'organizations') === 'indigenous'
            ? 'indigenous'
            : 'organizations';
        $locationFilters = array_intersect_key($filters, array_flip(['region_id', 'branch_id', 'province_id', 'warehouse_id']));
        $editOrganization = !empty($filters['edit_id']) ? FarmerOrganization::find((int) $filters['edit_id']) : null;
        if ($editOrganization && !isset($filters['classification'])) {
            $classification = ($editOrganization['classification_type'] ?? '') === FarmerOrganization::CLASSIFICATION_INDIGENOUS
                ? 'indigenous'
                : 'organizations';
        }
        $isIndigenousTab = $classification === 'indigenous';
        $organizations = array_values(array_filter(
            FarmerOrganization::all($locationFilters),
            fn (array $organization): bool => ($organization['classification_type'] ?? FarmerOrganization::CLASSIFICATION_ORGANIZATION)
                === ($isIndigenousTab ? FarmerOrganization::CLASSIFICATION_INDIGENOUS : FarmerOrganization::CLASSIFICATION_ORGANIZATION)
        ));

        View::render('farmer-organization-library', [
            'title' => 'Farmer Classifications',
            'alert' => $this->pullFlash(),
            'farmerOrganizations' => $organizations,
            'editOrganization' => $editOrganization,
            'activeClassification' => $classification,
            'locationFilters' => $locationFilters,
            'canManageClassifications' => $this->canEncode(),
        ]);
    }

    public function farmerOrganizationView(array $filters): void
    {
        if (!$this->authorizeRecords()) {
            return;
        }

        $id = (int) ($filters['id'] ?? 0);
        $organization = $id > 0 ? FarmerOrganization::find($id) : null;
        $classification = ($organization['classification_type'] ?? '') === FarmerOrganization::CLASSIFICATION_INDIGENOUS
            ? 'indigenous'
            : 'organizations';

        View::render('farmer-organization-view', [
            'title' => 'Farmer Classification Members',
            'alert' => $this->pullFlash(),
            'organization' => $organization,
            'members' => $id > 0 ? FarmerOrganization::members($id) : [],
            'activeClassification' => $classification,
        ]);
    }

    public function storeFarmerOrganization(array $payload): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        $classification = ($payload['classification'] ?? 'organizations') === 'indigenous' ? 'indigenous' : 'organizations';
        $redirectParams = ['page' => 'farmer-organization-library', 'classification' => $classification];
        foreach (['region_id', 'branch_id', 'province_id', 'warehouse_id'] as $key) {
            if (!empty($payload[$key])) {
                $redirectParams[$key] = $payload[$key];
            }
        }
        $redirectUrl = '?' . http_build_query($redirectParams);
        $name = $this->clean($payload['name'] ?? '');
        if ($name === '') {
            $this->flash('danger', 'Farmer organization name is required.');
            $this->redirect($redirectUrl);
            return;
        }

        FarmerOrganization::create(
            $name,
            (int) ($payload['total_members'] ?? 0),
            $this->clean($payload['office_location'] ?? ''),
            $classification === 'indigenous',
            !empty($payload['organization_warehouse_id']) ? (int) $payload['organization_warehouse_id'] : null
        );
        Activity::add('Farmer classification added: ' . $name . '.');
        $this->flash('success', 'Farmer classification saved.');
        $this->redirect($redirectUrl);
    }

    public function updateFarmerOrganization(array $payload): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        $classification = ($payload['classification'] ?? 'organizations') === 'indigenous' ? 'indigenous' : 'organizations';
        $redirectParams = ['page' => 'farmer-organization-library', 'classification' => $classification];
        foreach (['region_id', 'branch_id', 'province_id', 'warehouse_id'] as $key) {
            if (!empty($payload[$key])) {
                $redirectParams[$key] = $payload[$key];
            }
        }
        $redirectUrl = '?' . http_build_query($redirectParams);
        $name = $this->clean($payload['name'] ?? '');
        if ($name === '') {
            $this->flash('danger', 'Farmer organization name is required.');
            $this->redirect($redirectUrl);
            return;
        }

        FarmerOrganization::update(
            (int) ($payload['id'] ?? 0),
            $name,
            (int) ($payload['total_members'] ?? 0),
            $this->clean($payload['office_location'] ?? ''),
            $classification === 'indigenous',
            !empty($payload['organization_warehouse_id']) ? (int) $payload['organization_warehouse_id'] : null
        );
        Activity::add('Farmer classification edited: ' . $name . '.');
        $this->flash('success', 'Farmer classification updated.');
        $this->redirect($redirectUrl);
    }

    public function updateFarmerOrganizationLocation(array $payload): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        $id = (int) ($payload['id'] ?? 0);
        $organization = $id > 0 ? FarmerOrganization::find($id) : null;
        if (!$organization) {
            $this->flash('danger', 'Farmer organization was not found.');
            $this->redirect('?page=farmer-organization-library');
            return;
        }

        FarmerOrganization::update(
            $id,
            $organization['name'],
            (int) ($organization['total_members'] ?? 0),
            $this->clean($payload['office_location'] ?? ''),
            ($organization['classification_type'] ?? '') === FarmerOrganization::CLASSIFICATION_INDIGENOUS,
            !empty($organization['warehouse_id']) ? (int) $organization['warehouse_id'] : null
        );
        Activity::add('Farmer organization office location edited: ' . $organization['name'] . '.');
        $this->flash('success', 'Office location updated.');
        $this->redirect('?page=farmer-organization-view&id=' . $id);
    }

    public function login(array $payload): void
    {
        $username = $this->clean($payload['username'] ?? '');
        $password = (string) ($payload['password'] ?? '');
        $resetUser = User::findByUsername($username);

        if (
            $resetUser
            && ($resetUser['status'] ?? '') === 'Pending'
            && (int) $resetUser['is_active'] !== 1
            && password_verify($password, (string) $resetUser['password_hash'])
        ) {
            $this->flash(
                'warning',
                'Your account is registered but has not been activated yet. Please contact the system administrator to enable account activation.'
            );
            $this->redirect('?show_login=1');
            return;
        }

        if (
            $resetUser
            && (int) $resetUser['is_active'] === 1
            && ($resetUser['password_reset_status'] ?? '') === 'Requested'
        ) {
            $this->flash('warning', 'You still have a pending password change request.');
            $this->redirect('?show_login=1');
            return;
        }

        if (
            $resetUser
            && (int) $resetUser['is_active'] === 1
            && ($resetUser['password_reset_status'] ?? '') === 'Approved'
        ) {
            $_SESSION['password_reset_user_id'] = (int) $resetUser['id'];
            $_SESSION['password_reset_username'] = $resetUser['username'];
            $this->flash('info', 'Your request to change password has been approved. Please change your password here.');
            $this->redirect('?password_reset=approved');
            return;
        }

        $user = User::authenticate($username, $password);

        if (!$user) {
            $this->redirect('?show_login=1&login_error=1');
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_image'] = $user['profile_image'] ?? '';
        $_SESSION['default_location'] = User::locationLabel(User::find((int) $user['id']) ?: $user);
        Activity::add($_SESSION['user'] . ' logged in.');
        $this->flash('success', 'Welcome back, ' . $_SESSION['user'] . '.');
        $this->redirect();
    }

    public function requestPasswordReset(array $payload): void
    {
        $username = $this->clean($payload['username'] ?? '');
        $user = $username !== '' ? User::findByUsername($username) : null;

        if (!$user || (int) $user['is_active'] !== 1) {
            $this->flash('danger', 'Enter an active username so System Admin can review the password reset request.');
            $this->redirect('?forgot_password=1');
            return;
        }

        User::requestPasswordReset((int) $user['id']);

        foreach ($this->superAdminIds() as $adminId) {
            Notification::add(
                'Password reset requested by ' . $user['username'] . ' (' . $user['full_name'] . ').',
                $adminId,
                'index.php?page=users'
            );
        }

        Activity::add('Password reset requested for ' . $user['username'] . '.');
        $this->flash('success', 'Your password reset request was sent to System Admin. You will be able to change your password after approval.');
        $this->redirect();
    }

    public function approvePasswordReset(array $payload): void
    {
        if (($_SESSION['role'] ?? '') !== 'System Admin') {
            $this->flash('danger', 'Only System Admin can approve password resets.');
            $this->redirect('?page=users');
            return;
        }

        $userId = (int) ($payload['user_id'] ?? 0);
        $user = $userId > 0 ? User::find($userId) : null;
        if (!$user) {
            $this->flash('danger', 'User was not found.');
            $this->redirect('?page=users');
            return;
        }

        User::approvePasswordReset($userId);
        Notification::add('Your password reset request was approved. Log in with your username to change your password.', $userId);
        Activity::add('Password reset approved for ' . $user['username'] . '.');
        $this->flash('success', 'Password reset approved for ' . $user['username'] . '.');
        $this->redirect('?page=users');
    }

    public function completePasswordReset(array $payload): void
    {
        $userId = (int) ($_SESSION['password_reset_user_id'] ?? 0);
        $username = $this->clean($payload['username'] ?? '');
        $user = $userId > 0 ? User::find($userId) : null;

        if (
            !$user
            || $username === ''
            || $username !== ($user['username'] ?? '')
            || ($user['password_reset_status'] ?? '') !== 'Approved'
        ) {
            unset($_SESSION['password_reset_user_id'], $_SESSION['password_reset_username']);
            $this->flash('danger', 'Password reset approval was not found. Please submit a new request.');
            $this->redirect();
            return;
        }

        $password = (string) ($payload['password'] ?? '');
        if ($password === '' || $password !== (string) ($payload['password_confirmation'] ?? '')) {
            $this->flash('danger', 'Password confirmation does not match.');
            $this->redirect('?password_reset=approved');
            return;
        }

        User::completePasswordReset($userId, $password);
        unset($_SESSION['password_reset_user_id'], $_SESSION['password_reset_username']);
        Activity::add('Password reset completed for ' . $user['username'] . '.');
        $this->flash('success', 'You have successfully changed your password.');
        $this->redirect('?show_login=1');
    }

    public function register(array $payload): void
    {
        $fullName = $this->clean($payload['full_name'] ?? '');
        $username = $this->clean($payload['username'] ?? '');
        $designation = $this->clean($payload['designation'] ?? '');
        $email = $this->clean($payload['email'] ?? '');
        $contactNumber = $this->clean($payload['contact_number'] ?? '');
        $password = (string) ($payload['password'] ?? '');
        $passwordConfirmation = (string) ($payload['password_confirmation'] ?? '');
        $officeScope = ($payload['office_scope'] ?? '') === 'central' ? 'central' : 'field';

        if ($fullName === '' || $username === '' || $designation === '' || $email === '' || $contactNumber === '' || $password === '' || $passwordConfirmation === '') {
            $this->flash('danger', 'Full name, username/employee number, designation, email, contact number, and both password fields are required.');
            $this->redirect('?show_register=1');
            return;
        }

        if (!preg_match('/^\d{6}$/', $username)) {
            $this->flash('danger', 'Username must be your six digit employee number using numbers only.');
            $this->redirect('?show_register=1');
            return;
        }

        if (User::findByUsername($username) !== null) {
            $this->rejectDuplicateRegistrationUsername($username);
            return;
        }

        if (!preg_match('/^[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}$/i', $email)) {
            $this->flash('danger', 'Enter a valid email address.');
            $this->redirect('?show_register=1');
            return;
        }

        if (!preg_match('/^09\d{9}$/', $contactNumber)) {
            $this->flash('danger', 'Contact number must be 11 digits, for example 09xxxxxxxxx.');
            $this->redirect('?show_register=1');
            return;
        }

        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{9,}$/', $password)) {
            $this->flash('danger', 'Password must be at least 9 characters and include letters and numbers. Special characters are allowed.');
            $this->redirect('?show_register=1');
            return;
        }

        if ($password !== $passwordConfirmation) {
            $this->flash('danger', 'Password confirmation does not match.');
            $this->redirect('?show_register=1');
            return;
        }

        try {
            User::register([
                'full_name' => $fullName,
                'username' => $username,
                'office_scope' => $officeScope,
                'region_id' => $officeScope === 'central' ? '' : $this->clean($payload['region_id'] ?? ''),
                'branch_id' => $officeScope === 'central' ? '' : $this->clean($payload['branch_id'] ?? ''),
                'province_id' => $officeScope === 'central' ? '' : $this->clean($payload['province_id'] ?? ''),
                'warehouse_id' => $officeScope === 'central' ? '' : $this->clean($payload['warehouse_id'] ?? ''),
                'central_department_id' => $officeScope === 'central' ? $this->clean($payload['central_department_id'] ?? '') : '',
                'central_division_id' => $officeScope === 'central' ? $this->clean($payload['central_division_id'] ?? '') : '',
                'central_unit_id' => $officeScope === 'central' ? $this->clean($payload['central_unit_id'] ?? '') : '',
                'designation' => $designation,
                'password' => $password,
                'email' => $email,
                'contact_number' => $contactNumber,
            ]);
        } catch (\PDOException $exception) {
            if ($exception->getCode() === '23000' && User::findByUsername($username) !== null) {
                $this->rejectDuplicateRegistrationUsername($username);
                return;
            }

            throw $exception;
        }
        Activity::add('New user registration submitted for ' . $username . '.');
        Notification::addUserRegistrationPending();
        $this->flash('success', 'Registration submitted for System Admin activation.');
        $this->redirect();
    }

    public function updateAccount(array $payload, array $files): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->flash('danger', 'Please log in to update your account.');
            $this->redirect('?page=account');
            return;
        }

        if (($payload['password'] ?? '') !== ($payload['password_confirmation'] ?? '')) {
            $this->flash('danger', 'Password confirmation does not match.');
            $this->redirect('?page=account');
            return;
        }

        $profileImage = $this->saveProfileImage($files['profile_image'] ?? null);
        User::updateAccount((int) $_SESSION['user_id'], [
            'full_name' => $this->clean($payload['full_name'] ?? ''),
            'email' => $this->clean($payload['email'] ?? ''),
            'contact_number' => $this->clean($payload['contact_number'] ?? ''),
            'designation' => $this->clean($payload['designation'] ?? ''),
            'region_id' => $this->clean($payload['region_id'] ?? ''),
            'branch_id' => $this->clean($payload['branch_id'] ?? ''),
            'province_id' => $this->clean($payload['province_id'] ?? ''),
            'warehouse_id' => $this->clean($payload['warehouse_id'] ?? ''),
            'password' => (string) ($payload['password'] ?? ''),
            'profile_image' => $profileImage,
            'offline_enabled' => !empty($payload['offline_enabled']),
        ]);

        $_SESSION['user'] = $this->clean($payload['full_name'] ?? $_SESSION['user']);
        if ($profileImage) {
            $_SESSION['profile_image'] = $profileImage;
        }
        $updatedUser = User::find((int) $_SESSION['user_id']);
        $_SESSION['default_location'] = $updatedUser ? User::locationLabel($updatedUser) : 'Not set';

        Activity::add($_SESSION['user'] . ' updated account details.');
        $this->flash('success', 'Account updated.');
        $this->redirect('?page=account');
    }

    public function submitDisplayPhoto(array $payload, array $files): void
    {
        if (!$this->authorizeAuthenticated()) return;
        $title = $this->clean($payload['title'] ?? '');
        $photographer = $this->clean($payload['photographer_name'] ?? '');
        $location = $this->clean($payload['location'] ?? '');
        $file = $files['display_photo'] ?? null;
        if ($title === '' || $photographer === '' || !$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->flash('danger', 'Add a title, photographer name, and a 4K image to submit your photo.');
            $this->redirect('?page=account#display-settings');
            return;
        }
        if (($file['size'] ?? 0) > 30 * 1024 * 1024 || !in_array(mime_content_type($file['tmp_name']), ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $this->flash('danger', 'Upload a JPG, PNG, or WebP image no larger than 30 MB.');
            $this->redirect('?page=account#display-settings');
            return;
        }
        $size = @getimagesize($file['tmp_name']);
        if (!$size || min((int) $size[0], (int) $size[1]) < 2160 || max((int) $size[0], (int) $size[1]) < 3840) {
            $this->flash('danger', 'Your photo must be 4K: at least 3,840 × 2,160 pixels (or the portrait equivalent).');
            $this->redirect('?page=account#display-settings');
            return;
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $directory = BASE_PATH . '/assets/uploads/display-submissions';
        if (!is_dir($directory)) mkdir($directory, 0775, true);
        $filename = 'submission-' . $_SESSION['user_id'] . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            $this->flash('danger', 'The photo could not be saved. Please try again.');
            $this->redirect('?page=account#display-settings');
            return;
        }
        DisplayPhoto::create((int) $_SESSION['user_id'], $title, $photographer, $location, 'assets/uploads/display-submissions/' . $filename, (int) $size[0], (int) $size[1]);
        foreach ($this->superAdminIds() as $adminId) Notification::add('New landing photo submission from ' . $_SESSION['user'] . '.', $adminId, 'index.php?page=display-settings');
        Activity::add($_SESSION['user'] . ' submitted a landing page photo for review.');
        $this->flash('success', 'Your 4K photo was submitted for System Admin review.');
        $this->redirect('?page=account#display-settings');
    }

    public function reviewDisplayPhoto(array $payload): void
    {
        if (($_SESSION['role'] ?? '') !== 'System Admin') { $this->redirect(); return; }
        $photo = DisplayPhoto::find((int) ($payload['id'] ?? 0));
        $status = in_array($payload['status'] ?? '', ['Approved', 'Rejected'], true) ? $payload['status'] : 'Rejected';
        if (!$photo) { $this->flash('danger', 'Photo submission was not found.'); $this->redirect('?page=display-settings'); return; }
        $optimized = $status === 'Approved' ? $this->optimizeDisplayPhoto($photo) : null;
        if ($status === 'Approved' && !$optimized) { $this->flash('danger', 'The photo could not be optimized.'); $this->redirect('?page=display-settings'); return; }
        DisplayPhoto::review((int) $photo['id'], $status, max(1, (int) ($payload['position'] ?? 999)), $optimized);
        if (!empty($photo['submitted_by'])) Notification::add('Your landing photo submission “' . $photo['title'] . '” was ' . strtolower($status) . '.', (int) $photo['submitted_by'], 'index.php?page=account#display-settings');
        $this->flash('success', 'Photo ' . strtolower($status) . '.');
        $this->redirect('?page=display-settings');
    }

    public function saveDisplaySettings(array $payload): void
    {
        if (($_SESSION['role'] ?? '') !== 'System Admin') { $this->redirect(); return; }
        DisplayPhoto::updateSettings(min(30, max(3, (int) ($payload['loop_duration'] ?? 7))), !empty($payload['panning_enabled']));
        $this->flash('success', 'Display settings saved.');
        $this->redirect('?page=display-settings');
    }

    public function updateDisplayPhotoPosition(array $payload): void
    {
        if (($_SESSION['role'] ?? '') !== 'System Admin') { $this->redirect(); return; }
        DisplayPhoto::updatePosition((int) ($payload['id'] ?? 0), max(1, (int) ($payload['position'] ?? 999)));
        $this->flash('success', 'Slide position updated.');
        $this->redirect('?page=display-settings');
    }

    public function updateUserAccess(array $payload): void
    {
        if (($_SESSION['role'] ?? '') !== 'System Admin') {
            $this->flash('danger', 'Only System Admin can manage user access.');
            $this->redirect('?page=users');
            return;
        }

        $role = $this->clean($payload['role'] ?? 'Read-Only User');
        if (!in_array($role, self::ROLES, true)) {
            $this->flash('danger', 'Select a valid user role.');
            $this->redirect('?page=users');
            return;
        }

        User::updateAccess(
            (int) ($payload['user_id'] ?? 0),
            $role,
            $this->clean($payload['status'] ?? 'Pending')
        );
        Activity::add('User access updated.');
        $this->flash('success', 'User access updated.');
        $this->redirect('?page=users');
    }

    public function updateUserAccessBulk(array $payload): void
    {
        if (($_SESSION['role'] ?? '') !== 'System Admin') {
            $this->flash('danger', 'Only System Admin can manage user access.');
            $this->redirect('?page=users');
            return;
        }

        $ids = array_map('intval', is_array($payload['user_ids'] ?? null) ? $payload['user_ids'] : []);
        $role = $this->clean($payload['bulk_role'] ?? '') ?: null;
        $status = $this->clean($payload['bulk_status'] ?? '') ?: null;
        if ($ids === []) {
            $this->flash('danger', 'Select at least one user.');
        } elseif (($role !== null && !in_array($role, self::ROLES, true)) || ($status !== null && !in_array($status, ['Pending', 'Active', 'Disabled'], true))) {
            $this->flash('danger', 'Select a valid status or role.');
        } elseif ($role === null && $status === null) {
            $this->flash('danger', 'Choose a status, role, or both to update.');
        } else {
            $updated = User::updateAccessBulk($ids, $role, $status);
            Activity::add('Bulk user access updated for ' . $updated . ' account(s).');
            $this->flash('success', 'User access updated for ' . $updated . ' account(s).');
        }
        $this->redirect('?page=users');
    }

    public function openNotification(array $payload): void
    {
        if (!$this->authorizeAuthenticated()) {
            return;
        }

        $target = Notification::markReadForUser((int) ($payload['notification_id'] ?? 0), (int) $_SESSION['user_id']);
        $this->redirect($target ? substr($target, strlen('index.php')) : '');
    }

    public function clearNotifications(array $payload): void
    {
        if (!$this->authorizeAuthenticated()) {
            return;
        }

        Notification::clearForUser((int) $_SESSION['user_id']);
        $returnTo = (string) ($payload['return_to'] ?? '');

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            return;
        }

        if (preg_match('/^\?page=[A-Za-z0-9_\-]+(&[A-Za-z0-9_\-]+=[A-Za-z0-9_\-%.]+)*$/', $returnTo) !== 1) {
            $returnTo = '';
        }

        $this->redirect($returnTo);
    }

    public function storeLocation(array $payload): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        $type = $this->clean($payload['type'] ?? '');
        $name = $this->clean($payload['name'] ?? '');

        if ($name === '') {
            $this->flash('danger', 'Location name is required.');
            $this->redirect('?page=locations');
            return;
        }

        match ($type) {
            'region' => Location::createRegion($name),
            'branch' => Location::createBranch((int) ($payload['region_id'] ?? 0), $name),
            'province' => Location::createProvince((int) ($payload['branch_id'] ?? 0), $name),
            'warehouse' => Location::createWarehouse((int) ($payload['province_id'] ?? 0), $name),
            default => null,
        };

        Activity::add('Location library updated.');
        $this->flash('success', 'Location added.');
        $this->redirect('?page=locations');
    }

    public function updateLocation(array $payload): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        Location::updateName(
            $this->clean($payload['type'] ?? ''),
            (int) ($payload['id'] ?? 0),
            $this->clean($payload['name'] ?? '')
        );
        Activity::add('Location name edited.');
        $this->flash('success', 'Location updated.');
        $this->redirect('?page=locations');
    }

    public function deleteLocation(array $payload): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        try {
            Location::delete(
                $this->clean($payload['type'] ?? ''),
                (int) ($payload['id'] ?? 0)
            );
        } catch (\RuntimeException $exception) {
            $this->flash('danger', $exception->getMessage());
            $this->redirect('?page=locations');
            return;
        }

        Activity::add('Location deleted from library.');
        $this->flash('success', 'Location deleted.');
        $this->redirect('?page=locations');
    }

    public function storeCentralOfficeLocation(array $payload): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        $type = $this->clean($payload['type'] ?? '');
        $name = $this->clean($payload['name'] ?? '');

        if ($name === '') {
            $this->flash('danger', 'Central office directory name is required.');
            $this->redirect('?page=central-office-directory');
            return;
        }

        match ($type) {
            'department' => CentralOffice::createDepartment($name),
            'division' => CentralOffice::createDivision((int) ($payload['department_id'] ?? 0), $name),
            'unit' => CentralOffice::createUnit((int) ($payload['division_id'] ?? 0), $name),
            default => null,
        };

        Activity::add('Central office directory updated.');
        $this->flash('success', 'Central office directory entry added.');
        $this->redirect('?page=central-office-directory');
    }

    public function updateCentralOfficeLocation(array $payload): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        CentralOffice::updateName(
            $this->clean($payload['type'] ?? ''),
            (int) ($payload['id'] ?? 0),
            $this->clean($payload['name'] ?? '')
        );
        Activity::add('Central office directory name edited.');
        $this->flash('success', 'Central office directory updated.');
        $this->redirect('?page=central-office-directory');
    }

    public function deleteCentralOfficeLocation(array $payload): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        try {
            CentralOffice::delete(
                $this->clean($payload['type'] ?? ''),
                (int) ($payload['id'] ?? 0)
            );
        } catch (\RuntimeException $exception) {
            $this->flash('danger', $exception->getMessage());
            $this->redirect('?page=central-office-directory');
            return;
        }

        Activity::add('Central office directory entry deleted.');
        $this->flash('success', 'Central office directory entry deleted.');
        $this->redirect('?page=central-office-directory');
    }

    public function logout(): void
    {
        Activity::add(($_SESSION['user'] ?? 'User') . ' logged out.');
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->redirect();
    }

    public function storeFarmer(array $payload, array $files): void
    {
        if (!$this->authorizeEncode()) {
            return;
        }

        $photoPath = $this->saveFarmerPhoto($files['farmer_photo'] ?? null);
        $genderOrientation = [];
        $genderSelection = $this->clean($payload['gender_orientation'] ?? '');
        if ($genderSelection !== '') {
            $genderOrientation[] = $genderSelection;
        }
        $genderOrientationOther = $this->clean($payload['gender_orientation_other'] ?? '');
        if ($genderOrientationOther !== '' && in_array('Other', $genderOrientation, true)) {
            $genderOrientation[] = $genderOrientationOther;
        }

        $farmer = [
            'rsbsa' => $this->clean($payload['rsbsa'] ?? ''),
            'first_name' => $this->clean($payload['first_name'] ?? ''),
            'middle_name' => $this->clean($payload['middle_name'] ?? ''),
            'last_name' => $this->clean($payload['last_name'] ?? ''),
            'address' => $this->clean($payload['address'] ?? ''),
            'birthdate' => $this->clean($payload['birthdate'] ?? ''),
            'birthplace' => $this->clean($payload['birthplace'] ?? ''),
            'civil_status' => $this->clean($payload['civil_status'] ?? ''),
            'spouse' => $this->clean($payload['spouse'] ?? ''),
            'dependents' => $this->clean($payload['dependents'] ?? ''),
            'contact' => $this->clean($payload['contact'] ?? ''),
            'email' => $this->clean($payload['email'] ?? ''),
            'sex' => $this->clean($payload['sex'] ?? ''),
            'gender_orientation' => $genderOrientation,
            'sector' => $this->arrayValue($payload['sector'] ?? []),
            'is_ip_group_member' => !empty($payload['is_ip_group_member']),
            'landholding' => $this->arrayValue($payload['landholding'] ?? []),
            'irrigated' => $this->clean($payload['irrigated'] ?? ''),
            'palay_location' => $this->clean($payload['palay_location'] ?? ''),
            'harvest_area' => $this->clean($payload['harvest_area'] ?? ''),
            'average_yield' => $this->clean($payload['average_yield'] ?? ''),
            'organization' => $this->clean($payload['organization'] ?? ''),
            'warehouse_id' => $this->clean($payload['warehouse_id'] ?? ''),
            'photo_path' => $photoPath,
        ];

        if ($farmer['is_ip_group_member'] && !in_array('Indigenous People', $farmer['sector'], true)) {
            $farmer['sector'][] = 'Indigenous People';
        }

        Farmer::create($farmer);
        Activity::add('Farmer profile added for ' . $farmer['first_name'] . ' ' . $farmer['last_name'] . '.');
        Notification::add('New farmer record uploaded: ' . $farmer['rsbsa'] . '.');
        $this->flash('success', 'Farmer profile saved.');
        $this->redirect('?page=encode-farmer');
    }

    public function updateFarmer(array $payload, array $files): void
    {
        if (!$this->authorizeRecords()) {
            return;
        }

        $id = (int) ($payload['farmer_id'] ?? 0);
        $photoPath = $this->saveFarmerPhoto($files['farmer_photo'] ?? null);
        $genderOrientation = [];
        $genderSelection = $this->clean($payload['gender_orientation'] ?? '');
        if ($genderSelection !== '') {
            $genderOrientation[] = $genderSelection;
        }
        $genderOrientationOther = $this->clean($payload['gender_orientation_other'] ?? '');
        if ($genderOrientationOther !== '' && in_array('Other', $genderOrientation, true)) {
            $genderOrientation[] = $genderOrientationOther;
        }

        $farmer = [
            'rsbsa' => $this->clean($payload['rsbsa'] ?? ''),
            'first_name' => $this->clean($payload['first_name'] ?? ''),
            'middle_name' => $this->clean($payload['middle_name'] ?? ''),
            'last_name' => $this->clean($payload['last_name'] ?? ''),
            'address' => $this->clean($payload['address'] ?? ''),
            'birthdate' => $this->clean($payload['birthdate'] ?? ''),
            'birthplace' => $this->clean($payload['birthplace'] ?? ''),
            'civil_status' => $this->clean($payload['civil_status'] ?? ''),
            'spouse' => $this->clean($payload['spouse'] ?? ''),
            'dependents' => $this->clean($payload['dependents'] ?? ''),
            'contact' => $this->clean($payload['contact'] ?? ''),
            'email' => $this->clean($payload['email'] ?? ''),
            'sex' => $this->clean($payload['sex'] ?? ''),
            'gender_orientation' => $genderOrientation,
            'sector' => $this->arrayValue($payload['sector'] ?? []),
            'is_ip_group_member' => !empty($payload['is_ip_group_member']),
            'landholding' => $this->arrayValue($payload['landholding'] ?? []),
            'irrigated' => $this->clean($payload['irrigated'] ?? ''),
            'palay_location' => $this->clean($payload['palay_location'] ?? ''),
            'harvest_area' => $this->clean($payload['harvest_area'] ?? ''),
            'average_yield' => $this->clean($payload['average_yield'] ?? ''),
            'organization' => $this->clean($payload['organization'] ?? ''),
            'warehouse_id' => $this->clean($payload['warehouse_id'] ?? ''),
            'photo_path' => $photoPath,
        ];

        if ($farmer['is_ip_group_member'] && !in_array('Indigenous People', $farmer['sector'], true)) {
            $farmer['sector'][] = 'Indigenous People';
        }

        Farmer::update($id, $farmer);
        Activity::add('Farmer profile updated for ' . $farmer['first_name'] . ' ' . $farmer['last_name'] . '.');
        $this->flash('success', 'Farmer profile updated.');
        $this->redirect('?page=farmer-view&id=' . $id);
    }

    public function storeTransaction(array $payload): void
    {
        if (!$this->authorizeEncode()) {
            return;
        }

        $transaction = [
            'type' => $this->clean($payload['type'] ?? ''),
            'procurement' => $this->clean($payload['procurement'] ?? ''),
            'rsbsa' => $this->clean($payload['rsbsa'] ?? ''),
            'fo_name' => $this->clean($payload['fo_name'] ?? ''),
            'representative' => $this->clean($payload['representative'] ?? ''),
            'members' => $this->clean($payload['members'] ?? ''),
            'farm_area' => $this->clean($payload['farm_area'] ?? ''),
            'delivery_date' => $this->clean($payload['delivery_date'] ?? ''),
            'wsr' => $this->clean($payload['wsr'] ?? ''),
            'price' => $this->clean($payload['price'] ?? ''),
            'net_kg' => $this->clean($payload['net_kg'] ?? ''),
            'bags' => $this->clean($payload['bags'] ?? ''),
            'warehouse_id' => $this->clean($payload['warehouse_id'] ?? ''),
            'delivered_farmer_ids' => array_map('intval', (array) ($payload['delivered_farmer_ids'] ?? [])),
            'client_control_number' => $this->clean($payload['client_control_number'] ?? ''),
        ];

        try {
            $transactionResult = Transaction::create($transaction);
        } catch (\DomainException $exception) {
            $this->flash('danger', $exception->getMessage());
            $this->redirect(($transaction['type'] === 'Farmer Organization') ? '?page=organization-delivery' : '?page=individual-delivery');
            return;
        }

        Activity::add('Warehouse transaction recorded for ' . ($transaction['rsbsa'] ?: $transaction['fo_name']) . '.');
        Notification::add('New palay delivery awaiting manager review.');
        $flashMessage = 'Transaction recorded.';
        if (!empty($transactionResult['reached_annual_limit'])) {
            $deliveryYear = (int) ($transactionResult['delivery_year'] ?? date('Y'));
            $limitMessage = sprintf(
                'Farmer %s has reached the annual %d-bag delivery limit for %d.',
                $transaction['rsbsa'],
                Transaction::MAX_INDIVIDUAL_ANNUAL_BAGS,
                $deliveryYear
            );
            Notification::add(
                $limitMessage,
                !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
                'index.php?page=farmers&q=' . rawurlencode($transaction['rsbsa'])
            );
            $flashMessage .= ' ' . $limitMessage;
        }
        $this->notifyCrossLocationPalaySale($transaction);
        $this->flash('success', $flashMessage);
        $this->redirect(($transaction['type'] === 'Farmer Organization') ? '?page=organization-delivery' : '?page=individual-delivery');
    }

    /** Accepts a queued browser delivery. The database unique control number makes retries idempotent. */
    public function syncOfflineTransaction(array $payload): void
    {
        header('Content-Type: application/json');
        if (!$this->authorizeEncode()) { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Your account is not permitted to upload delivery inputs.']); return; }
        $payload['action'] = 'transaction';
        $transaction = [
            'type' => $this->clean($payload['type'] ?? ''), 'procurement' => $this->clean($payload['procurement'] ?? ''),
            'rsbsa' => $this->clean($payload['rsbsa'] ?? ''), 'fo_name' => $this->clean($payload['fo_name'] ?? ''),
            'representative' => $this->clean($payload['representative'] ?? ''), 'members' => $this->clean($payload['members'] ?? ''),
            'farm_area' => $this->clean($payload['farm_area'] ?? ''), 'delivery_date' => $this->clean($payload['delivery_date'] ?? ''),
            'wsr' => $this->clean($payload['wsr'] ?? ''), 'price' => $this->clean($payload['price'] ?? ''),
            'net_kg' => $this->clean($payload['net_kg'] ?? ''), 'bags' => $this->clean($payload['bags'] ?? ''),
            'warehouse_id' => $this->clean($payload['warehouse_id'] ?? ''), 'client_control_number' => $this->clean($payload['client_control_number'] ?? ''),
            'delivered_farmer_ids' => array_map('intval', (array) ($payload['delivered_farmer_ids'] ?? [])),
        ];
        if ($transaction['client_control_number'] === '') { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Offline control number is missing.']); return; }
        try { $result = Transaction::create($transaction); Activity::add('Offline delivery uploaded: ' . $transaction['client_control_number'] . '.'); echo json_encode(['success' => true, 'duplicate' => $result['duplicate'] ?? false, 'id' => $result['transaction_id'] ?? null]); }
        catch (\Throwable $e) { http_response_code(422); echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
    }

    public function redirect(string $fragment = ''): void
    {
        header('Location: index.php' . $fragment);
    }

    private function metrics(array $farmers, array $transactions, array $notifications): array
    {
        $farmerCount = count($farmers);
        $femaleCount = count(array_filter($farmers, fn (array $farmer) => ($farmer['sex'] ?? '') === 'Female'));
        $maleCount = count(array_filter($farmers, fn (array $farmer) => ($farmer['sex'] ?? '') === 'Male'));
        $totalKg = array_reduce($transactions, fn (float $sum, array $transaction) => $sum + (float) ($transaction['net_kg'] ?? 0), 0.0);
        $todayKg = array_reduce(
            $transactions,
            fn (float $sum, array $transaction) => ($transaction['delivery_date'] ?? '') === date('Y-m-d') ? $sum + (float) ($transaction['net_kg'] ?? 0) : $sum,
            0.0
        );
        $unreadCount = count(array_filter($notifications, fn (array $notification) => empty($notification['read'])));

        return compact('farmerCount', 'femaleCount', 'maleCount', 'totalKg', 'todayKg', 'unreadCount');
    }

    private function authorizeAuthenticated(string $returnTo = ''): bool
    {
        if ($this->isAuthenticated()) {
            return true;
        }

        $this->flash('danger', 'Please log in with an activated account to continue.');
        $this->redirect($returnTo);
        return false;
    }

    private function authorizeEncode(): bool
    {
        if (!$this->isAuthenticated()) {
            $this->flash('danger', 'Please log in with an activated account to select an activity.');
            $this->redirect();
            return false;
        }

        if ($this->canEncode()) {
            return true;
        }

        $this->flash('danger', 'Only Warehouse Personnel and System Admins can encode records.');
        $this->redirect($this->isReadOnlyUser() ? '?page=reports' : '');
        return false;
    }

    private function authorizeRecords(): bool
    {
        if (!$this->isAuthenticated()) {
            $this->flash('danger', 'Please log in with an activated account to view records.');
            $this->redirect();
            return false;
        }

        if (in_array($_SESSION['role'] ?? '', ['Manager', 'Warehouse Personnel', 'System Admin'], true)) {
            return true;
        }

        $this->flash('danger', 'Read-Only Users can only access the Reports page.');
        $this->redirect('?page=reports');
        return false;
    }

    private function withUserLocationDefaults(array $filters): array
    {
        $locationKeys = ['region_id', 'branch_id', 'province_id', 'warehouse_id'];
        foreach ($locationKeys as $key) {
            if (array_key_exists($key, $filters)) {
                return $filters;
            }
        }

        if (empty($_SESSION['user_id'])) {
            return $filters;
        }

        $user = User::find((int) $_SESSION['user_id']);
        if (!$user) {
            return $filters;
        }

        foreach ($locationKeys as $key) {
            if (!empty($user[$key])) {
                $filters[$key] = (string) $user[$key];
            }
        }

        return $filters;
    }

    private function currentUserLocationValues(): array
    {
        if (empty($_SESSION['user_id'])) {
            return [];
        }

        $user = User::find((int) $_SESSION['user_id']);
        if (!$user) {
            return [];
        }

        return [
            'region_id' => $user['region_id'] ?? '',
            'branch_id' => $user['branch_id'] ?? '',
            'province_id' => $user['province_id'] ?? '',
            'warehouse_id' => $user['warehouse_id'] ?? '',
        ];
    }

    private function withCurrentYearDateDefaults(array $filters): array
    {
        if (empty($filters['date_from'])) {
            $filters['date_from'] = date('Y-01-01');
        }

        if (empty($filters['date_to'])) {
            $filters['date_to'] = date('Y-m-d');
        }

        return $filters;
    }

    private function notifyCrossLocationPalaySale(array $transaction): void
    {
        if (($transaction['type'] ?? '') !== 'Individual' || ($transaction['rsbsa'] ?? '') === '') {
            return;
        }

        $farmerId = Farmer::idFromRsbsa($transaction['rsbsa']);
        $farmer = $farmerId ? Farmer::find($farmerId) : null;
        $homeWarehouseId = (int) ($farmer['warehouse_id'] ?? 0);
        $soldWarehouseId = (int) ($transaction['warehouse_id'] ?: (Location::defaultWarehouseId() ?? 0));

        if ($homeWarehouseId <= 0 || $soldWarehouseId <= 0 || $homeWarehouseId === $soldWarehouseId) {
            return;
        }

        $message = sprintf(
            'Farmer %s has sold %s kgs of Palay to %s.',
            $farmer['rsbsa'] ?? $transaction['rsbsa'],
            number_format((float) ($transaction['net_kg'] ?? 0), 2),
            Location::warehouseLabel($soldWarehouseId)
        );

        foreach (User::activeIdsForWarehouse($homeWarehouseId) as $userId) {
            Notification::add($message, $userId);
        }
    }

    private function authorizeLocationLibrary(): bool
    {
        if (!$this->isAuthenticated()) {
            $this->flash('danger', 'Please log in with an activated account to manage locations.');
            $this->redirect();
            return false;
        }

        if ($this->canEncode()) {
            return true;
        }

        $this->flash('danger', 'Only Warehouse Personnel and System Admins can edit the location library.');
        $this->redirect($this->isReadOnlyUser() ? '?page=reports' : '');
        return false;
    }

    private function authorizeHelp(): bool
    {
        if (!$this->authorizeAuthenticated()) {
            return false;
        }

        if (in_array($_SESSION['role'] ?? '', ['Manager', 'Warehouse Personnel', 'System Admin'], true)) {
            return true;
        }

        $this->flash('danger', 'Read-Only User accounts can only access Reports.');
        $this->redirect('?page=reports');
        return false;
    }

    private function authorizeSignatories(): bool
    {
        if (!$this->authorizeAuthenticated()) {
            return false;
        }

        if (!$this->isReadOnlyUser()) {
            return true;
        }

        $this->flash('danger', 'Signatories are not available to Read-Only User accounts.');
        $this->redirect('?page=reports');
        return false;
    }

    private function isAuthenticated(): bool
    {
        return !empty($_SESSION['user_id']) && !empty($_SESSION['role']);
    }

    private function isAjaxRequest(): bool
    {
        return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'fetch';
    }

    private function isReadOnlyUser(): bool
    {
        return ($_SESSION['role'] ?? '') === 'Read-Only User';
    }

    private function canEncode(): bool
    {
        return in_array($_SESSION['role'] ?? '', ['Warehouse Personnel', 'System Admin'], true);
    }

    private function superAdminIds(): array
    {
        return SupportTicket::superAdminIds();
    }

    private function sexDisaggregationOptions(array $farmers): array
    {
        $options = ['Female', 'Male'];

        foreach ($farmers as $farmer) {
            foreach (($farmer['gender_orientation'] ?? []) as $orientation) {
                if ($orientation !== '' && !in_array($orientation, $options, true)) {
                    $options[] = $orientation;
                }
            }
        }

        return $options;
    }

    private function filterFarmersBySexData(array $farmers, array $filters): array
    {
        if ($filters === []) {
            return $farmers;
        }

        return array_values(array_filter($farmers, function (array $farmer) use ($filters): bool {
            if (in_array(($farmer['sex'] ?? ''), $filters, true)) {
                return true;
            }

            foreach (($farmer['gender_orientation'] ?? []) as $orientation) {
                if (in_array($orientation, $filters, true)) {
                    return true;
                }
            }

            return false;
        }));
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = compact('type', 'message');
    }

    private function rejectDuplicateRegistrationUsername(string $username): void
    {
        $_SESSION['registration_username_error'] = [
            'username' => $username,
            'message' => 'The username is already used. Please log in using the username and password.',
        ];
        $this->redirect('?show_register=1');
    }

    private function pullFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return $flash;
    }

    private function clean(mixed $value): string
    {
        return trim(strip_tags((string) $value));
    }

    private function arrayValue(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_map(fn (mixed $item): string => $this->clean($item), $value);
    }

    private function saveProfileImage(?array $file): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        if (($file['size'] ?? 0) > 8 * 1024 * 1024) {
            return null;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return null;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        $directory = BASE_PATH . '/assets/uploads';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = 'profile-' . ($_SESSION['user_id'] ?? 'guest') . '-' . time() . '.' . $extension;
        $target = $directory . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return null;
        }

        return 'assets/uploads/' . $filename;
    }

    private function optimizeDisplayPhoto(array $photo): ?string
    {
        if (!extension_loaded('gd')) return (string) $photo['image_path'];
        $source = BASE_PATH . '/' . ltrim((string) $photo['image_path'], '/');
        $info = @getimagesize($source);
        if (!$info) return null;
        $image = match ($info['mime'] ?? '') {
            'image/jpeg' => @imagecreatefromjpeg($source),
            'image/png' => @imagecreatefrompng($source),
            'image/webp' => @imagecreatefromwebp($source),
            default => false,
        };
        if (!$image) return null;
        $width = imagesx($image); $height = imagesy($image);
        $scale = min(1, 2560 / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        $directory = BASE_PATH . '/assets/uploads/display';
        if (!is_dir($directory)) mkdir($directory, 0775, true);
        $filename = 'display-' . $photo['id'] . '-' . time() . '.webp';
        $saved = imagewebp($canvas, $directory . '/' . $filename, 78);
        imagedestroy($canvas); imagedestroy($image);
        return $saved ? 'assets/uploads/display/' . $filename : null;
    }

    private function saveFarmerPhoto(?array $file): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        if (($file['size'] ?? 0) > 50 * 1024 * 1024) {
            return null;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return null;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            return null;
        }

        $directory = BASE_PATH . '/assets/uploads/farmers';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = 'farmer-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $target = $directory . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return null;
        }

        return 'assets/uploads/farmers/' . $filename;
    }

    private function saveSupportScreenshot(?array $file): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        if (($file['size'] ?? 0) > 8 * 1024 * 1024) {
            return null;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return null;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        $directory = BASE_PATH . '/assets/uploads/support';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = 'ticket-' . ($_SESSION['user_id'] ?? 'user') . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $target = $directory . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return null;
        }

        return 'assets/uploads/support/' . $filename;
    }
}
