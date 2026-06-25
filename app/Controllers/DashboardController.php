<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Activity;
use App\Models\CentralOffice;
use App\Models\Farmer;
use App\Models\FarmerOrganization;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Report;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;

final class DashboardController
{
    public function index(): void
    {
        if ($this->isViewer()) {
            $this->redirect('?page=reports');
            return;
        }

        View::render('dashboard', [
            'title' => "Farmer's Who Sold Palay to NFA",
            'alert' => $this->pullFlash(),
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
        $reportFormat = ($filters['report_format'] ?? 'default') === 'branch_region' ? 'branch_region' : 'default';

        View::render('reports', [
            'title' => $view === 'sectoral' ? 'Sex Disaggregated Data Analytics' : 'Reports',
            'alert' => $this->pullFlash(),
            'view' => $view,
            'scope' => $scope,
            'reportFormat' => $reportFormat,
            'rows' => $reportFormat === 'branch_region' ? Report::summaryByBranchRegion($filters) : Report::summary($scope, $filters),
            'filters' => $filters,
            'sectoralScore' => $view === 'sectoral' ? Report::sectoralScore($filters) : null,
        ]);
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

        $user = !empty($_SESSION['user_id']) ? User::find((int) $_SESSION['user_id']) : null;

        View::render('account', [
            'title' => 'Account Management',
            'alert' => $this->pullFlash(),
            'user' => $user,
        ]);
    }

    public function users(): void
    {
        if (($_SESSION['role'] ?? '') !== 'Super Admin') {
            $this->flash('danger', 'Only Super Admin can manage user access.');
            $this->redirect();
            return;
        }

        View::render('users', [
            'title' => 'User Control Management',
            'alert' => $this->pullFlash(),
            'users' => User::all(),
            'auditLogs' => Activity::auditLogs(),
            'roles' => ['Super Admin', 'Regional/Branch Manager', 'Warehouse Supervisor', 'Viewer'],
        ]);
    }

    public function techSupport(): void
    {
        if (!$this->authorizeAuthenticated()) {
            return;
        }

        $isSuperAdmin = ($_SESSION['role'] ?? '') === 'Super Admin';

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

    public function storeSupportTicket(array $payload, array $files): void
    {
        if (!$this->authorizeAuthenticated('?page=tech-support')) {
            return;
        }

        if (($_SESSION['role'] ?? '') === 'Super Admin') {
            $this->flash('danger', 'Super Admin accounts manage tickets instead of submitting them.');
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

    public function replySupportTicket(array $payload): void
    {
        if (!$this->authorizeAuthenticated('?page=tech-support')) {
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

        if (($_SESSION['role'] ?? '') === 'Super Admin') {
            Notification::add('Developer team replied to your ticket: ' . $ticket['title'] . '.', (int) $ticket['reporter_id'], 'index.php?page=tech-support');
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
        if (($_SESSION['role'] ?? '') !== 'Super Admin') {
            $this->flash('danger', 'Only Super Admin can mark support tickets as completed.');
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
        Notification::add('Your tech support ticket has been marked completed: ' . $ticket['title'] . '.', (int) $ticket['reporter_id'], 'index.php?page=tech-support');
        Activity::add($_SESSION['user'] . ' completed support ticket #' . $ticketId . '.');
        $this->flash('success', 'Support ticket marked as completed.');
        $this->redirect('?page=tech-support');
    }

    public function archiveSupportTicket(array $payload): void
    {
        if (!$this->authorizeAuthenticated('?page=tech-support')) {
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
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        View::render('farmer-organization-library', [
            'title' => 'Farmer Organizations',
            'alert' => $this->pullFlash(),
            'farmerOrganizations' => FarmerOrganization::all(),
            'editOrganization' => !empty($filters['edit_id']) ? FarmerOrganization::find((int) $filters['edit_id']) : null,
        ]);
    }

    public function farmerOrganizationView(array $filters): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        $id = (int) ($filters['id'] ?? 0);

        View::render('farmer-organization-view', [
            'title' => 'Farmer Organization Members',
            'alert' => $this->pullFlash(),
            'organization' => $id > 0 ? FarmerOrganization::find($id) : null,
            'members' => $id > 0 ? FarmerOrganization::members($id) : [],
        ]);
    }

    public function storeFarmerOrganization(array $payload): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        $name = $this->clean($payload['name'] ?? '');
        if ($name === '') {
            $this->flash('danger', 'Farmer organization name is required.');
            $this->redirect('?page=farmer-organization-library');
            return;
        }

        FarmerOrganization::create(
            $name,
            (int) ($payload['total_members'] ?? 0)
        );
        Activity::add('Farmer organization added: ' . $name . '.');
        $this->flash('success', 'Farmer organization saved.');
        $this->redirect('?page=farmer-organization-library');
    }

    public function updateFarmerOrganization(array $payload): void
    {
        if (!$this->authorizeLocationLibrary()) {
            return;
        }

        $name = $this->clean($payload['name'] ?? '');
        if ($name === '') {
            $this->flash('danger', 'Farmer organization name is required.');
            $this->redirect('?page=farmer-organization-library');
            return;
        }

        FarmerOrganization::update(
            (int) ($payload['id'] ?? 0),
            $name,
            (int) ($payload['total_members'] ?? 0),
            $this->clean($payload['office_location'] ?? '')
        );
        Activity::add('Farmer organization edited: ' . $name . '.');
        $this->flash('success', 'Farmer organization updated.');
        $this->redirect('?page=farmer-organization-library');
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
            $this->clean($payload['office_location'] ?? '')
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
            $this->flash('danger', 'Enter an active username so Super Admin can review the password reset request.');
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
        $this->flash('success', 'Your password reset request was sent to Super Admin. You will be able to change your password after approval.');
        $this->redirect();
    }

    public function approvePasswordReset(array $payload): void
    {
        if (($_SESSION['role'] ?? '') !== 'Super Admin') {
            $this->flash('danger', 'Only Super Admin can approve password resets.');
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
        if (($payload['password'] ?? '') !== ($payload['password_confirmation'] ?? '')) {
            $this->flash('danger', 'Password confirmation does not match.');
            $this->redirect();
            return;
        }

        User::register([
            'full_name' => $this->clean($payload['full_name'] ?? ''),
            'username' => $this->clean($payload['username'] ?? ''),
            'office_scope' => ($payload['office_scope'] ?? '') === 'central' ? 'central' : 'field',
            'region_id' => ($payload['office_scope'] ?? '') === 'central' ? '' : $this->clean($payload['region_id'] ?? ''),
            'branch_id' => ($payload['office_scope'] ?? '') === 'central' ? '' : $this->clean($payload['branch_id'] ?? ''),
            'province_id' => ($payload['office_scope'] ?? '') === 'central' ? '' : $this->clean($payload['province_id'] ?? ''),
            'warehouse_id' => ($payload['office_scope'] ?? '') === 'central' ? '' : $this->clean($payload['warehouse_id'] ?? ''),
            'central_department_id' => ($payload['office_scope'] ?? '') === 'central' ? $this->clean($payload['central_department_id'] ?? '') : '',
            'central_division_id' => ($payload['office_scope'] ?? '') === 'central' ? $this->clean($payload['central_division_id'] ?? '') : '',
            'central_unit_id' => ($payload['office_scope'] ?? '') === 'central' ? $this->clean($payload['central_unit_id'] ?? '') : '',
            'designation' => $this->clean($payload['designation'] ?? ''),
            'password' => (string) ($payload['password'] ?? ''),
            'email' => $this->clean($payload['email'] ?? ''),
            'contact_number' => $this->clean($payload['contact_number'] ?? ''),
        ]);
        Activity::add('New user registration submitted for ' . $this->clean($payload['username'] ?? '') . '.');
        Notification::addUserRegistrationPending();
        $this->flash('success', 'Registration submitted for Super Admin activation.');
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

    public function updateUserAccess(array $payload): void
    {
        if (($_SESSION['role'] ?? '') !== 'Super Admin') {
            $this->flash('danger', 'Only Super Admin can manage user access.');
            $this->redirect('?page=users');
            return;
        }

        User::updateAccess(
            (int) ($payload['user_id'] ?? 0),
            $this->clean($payload['role'] ?? 'Viewer'),
            $this->clean($payload['status'] ?? 'Pending')
        );
        Activity::add('User access updated.');
        $this->flash('success', 'User access updated.');
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
            'landholding' => $this->arrayValue($payload['landholding'] ?? []),
            'irrigated' => $this->clean($payload['irrigated'] ?? ''),
            'palay_location' => $this->clean($payload['palay_location'] ?? ''),
            'harvest_area' => $this->clean($payload['harvest_area'] ?? ''),
            'average_yield' => $this->clean($payload['average_yield'] ?? ''),
            'organization' => $this->clean($payload['organization'] ?? ''),
            'warehouse_id' => $this->clean($payload['warehouse_id'] ?? ''),
            'photo_path' => $photoPath,
        ];

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
            'landholding' => $this->arrayValue($payload['landholding'] ?? []),
            'irrigated' => $this->clean($payload['irrigated'] ?? ''),
            'palay_location' => $this->clean($payload['palay_location'] ?? ''),
            'harvest_area' => $this->clean($payload['harvest_area'] ?? ''),
            'average_yield' => $this->clean($payload['average_yield'] ?? ''),
            'organization' => $this->clean($payload['organization'] ?? ''),
            'warehouse_id' => $this->clean($payload['warehouse_id'] ?? ''),
            'photo_path' => $photoPath,
        ];

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
        ];

        try {
            Transaction::create($transaction);
        } catch (\DomainException $exception) {
            $this->flash('danger', $exception->getMessage());
            $this->redirect(($transaction['type'] === 'Farmer Organization') ? '?page=organization-delivery' : '?page=individual-delivery');
            return;
        }

        Activity::add('Warehouse transaction recorded for ' . ($transaction['rsbsa'] ?: $transaction['fo_name']) . '.');
        Notification::add('New palay delivery awaiting manager review.');
        $this->notifyCrossLocationPalaySale($transaction);
        $this->flash('success', 'Transaction recorded.');
        $this->redirect(($transaction['type'] === 'Farmer Organization') ? '?page=organization-delivery' : '?page=individual-delivery');
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

        $this->flash('danger', 'Only Warehouse Supervisors, Regional/Branch Managers, and Super Admins can encode records.');
        $this->redirect($this->isViewer() ? '?page=reports' : '');
        return false;
    }

    private function authorizeRecords(): bool
    {
        if (!$this->isAuthenticated()) {
            $this->flash('danger', 'Please log in with an activated account to view records.');
            $this->redirect();
            return false;
        }

        if (!$this->isViewer()) {
            return true;
        }

        $this->flash('danger', 'Viewers can only access the Reports page.');
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

        $this->flash('danger', 'Only Warehouse Supervisors, Regional/Branch Managers, and Super Admins can edit the location library.');
        $this->redirect($this->isViewer() ? '?page=reports' : '');
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

    private function isViewer(): bool
    {
        return ($_SESSION['role'] ?? '') === 'Viewer';
    }

    private function canEncode(): bool
    {
        return in_array($_SESSION['role'] ?? '', ['Warehouse Supervisor', 'Regional/Branch Manager', 'Super Admin'], true);
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

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
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
