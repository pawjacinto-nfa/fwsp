<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

use App\Controllers\DashboardController;

$controller = new DashboardController();
$action = $_POST['action'] ?? 'dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    match ($action) {
        'login' => $controller->login($_POST),
        'logout' => $controller->logout(),
        'register' => $controller->register($_POST),
        'password-reset-request' => $controller->requestPasswordReset($_POST),
        'password-reset-approve' => $controller->approvePasswordReset($_POST),
        'password-reset-complete' => $controller->completePasswordReset($_POST),
        'account' => $controller->updateAccount($_POST, $_FILES),
        'user-access' => $controller->updateUserAccess($_POST),
        'location-add' => $controller->storeLocation($_POST),
        'location-update' => $controller->updateLocation($_POST),
        'location-delete' => $controller->deleteLocation($_POST),
        'central-office-add' => $controller->storeCentralOfficeLocation($_POST),
        'central-office-update' => $controller->updateCentralOfficeLocation($_POST),
        'central-office-delete' => $controller->deleteCentralOfficeLocation($_POST),
        'farmer-organization-add' => $controller->storeFarmerOrganization($_POST),
        'farmer-organization-update' => $controller->updateFarmerOrganization($_POST),
        'farmer-organization-location-update' => $controller->updateFarmerOrganizationLocation($_POST),
        'farmer' => $controller->storeFarmer($_POST, $_FILES),
        'farmer-update' => $controller->updateFarmer($_POST, $_FILES),
        'transaction' => $controller->storeTransaction($_POST),
        'support-ticket' => $controller->storeSupportTicket($_POST, $_FILES),
        'support-ticket-reply' => $controller->replySupportTicket($_POST),
        'support-ticket-complete' => $controller->completeSupportTicket($_POST),
        'support-ticket-archive' => $controller->archiveSupportTicket($_POST),
        'notifications-clear' => $controller->clearNotifications($_POST),
        default => $controller->redirect(),
    };

    exit;
}

if (isset($_GET['notification_id'])) {
    $controller->openNotification($_GET);
    exit;
}

match ($_GET['page'] ?? 'dashboard') {
    'records' => $controller->records($_GET),
    'farmers' => $controller->farmerRecords($_GET),
    'farmer-view' => $controller->farmerView($_GET),
    'transactions' => $controller->transactionRecords($_GET),
    'encode-farmer' => $controller->encodeFarmer(),
    'individual-delivery' => $controller->individualDelivery(),
    'organization-delivery' => $controller->organizationDelivery(),
    'reports' => $controller->reports($_GET),
    'sectoral-report' => $controller->sectoralReport($_GET),
    'account' => $controller->account(),
    'users' => $controller->users(),
    'tech-support' => $controller->techSupport(),
    'locations' => $controller->locationLibrary(),
    'central-office-directory' => $controller->centralOfficeLibrary(),
    'farmer-organization-library' => $controller->farmerOrganizationLibrary($_GET),
    'farmer-organization-view' => $controller->farmerOrganizationView($_GET),
    default => $controller->index(),
};
