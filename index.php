<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

use App\Controllers\DashboardController;

$action = $_POST['action'] ?? 'dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(419);
        if (strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'fetch') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Your session expired. Refresh the page and try again.']);
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Your session expired. Refresh the page and try again.',
            ];
            header('Location: index.php');
        }
        exit;
    }
}

$controller = new DashboardController();

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
        'signatory-add' => $controller->storeSignatories($_POST),
        'signatory-update' => $controller->updateSignatory($_POST),
        'signatory-delete' => $controller->deleteSignatory($_POST),
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
    'report-settings' => $controller->reportSettings(),
    'account' => $controller->account(),
    'users' => $controller->users(),
    'database-management' => $controller->databaseManagement($_GET),
    'tech-support' => $controller->techSupport(),
    'user-manual' => $controller->userManual(),
    'locations' => $controller->locationLibrary(),
    'central-office-directory' => $controller->centralOfficeLibrary(),
    'farmer-organization-library' => $controller->farmerOrganizationLibrary($_GET),
    'farmer-organization-view' => $controller->farmerOrganizationView($_GET),
    default => $controller->index(),
};
