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
$isPublicScheduleStatus = $_SERVER['REQUEST_METHOD'] === 'GET'
    && ($_GET['page'] ?? '') === 'schedule-status';

if (!$isPublicScheduleStatus && $controller->enforceMaintenanceLogout($action === 'maintenance-status')) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    match ($action) {
        'login' => $controller->login($_POST),
        'logout' => $controller->logout(),
        'register' => $controller->register($_POST),
        'password-reset-request' => $controller->requestPasswordReset($_POST),
        'password-reset-check' => $controller->checkPasswordResetApproval($_POST),
        'password-reset-approve' => $controller->approvePasswordReset($_POST),
        'password-reset-complete' => $controller->completePasswordReset($_POST),
        'account' => $controller->updateAccount($_POST, $_FILES),
        'account-deactivate' => $controller->deactivateAccount($_POST),
        'display-photo-submit' => $controller->submitDisplayPhoto($_POST, $_FILES),
        'display-photo-review' => $controller->reviewDisplayPhoto($_POST),
        'display-photo-position' => $controller->updateDisplayPhotoPosition($_POST),
        'display-settings-save' => $controller->saveDisplaySettings($_POST),
        'user-access' => $controller->updateUserAccess($_POST),
        'user-access-bulk' => $controller->updateUserAccessBulk($_POST),
        'maintenance-mode' => $controller->updateMaintenanceMode($_POST),
        'module-maintenance' => $controller->updateModuleMaintenance($_POST),
        'maintenance-status' => $controller->maintenanceStatus(),
        'location-add' => $controller->storeLocation($_POST),
        'location-update' => $controller->updateLocation($_POST),
        'location-delete' => $controller->deleteLocation($_POST),
        'central-office-add' => $controller->storeCentralOfficeLocation($_POST),
        'central-office-update' => $controller->updateCentralOfficeLocation($_POST),
        'central-office-delete' => $controller->deleteCentralOfficeLocation($_POST),
        'farmer-organization-add' => $controller->storeFarmerOrganization($_POST),
        'farmer-organization-update' => $controller->updateFarmerOrganization($_POST),
        'farmer-organization-delete' => $controller->deleteFarmerOrganization($_POST),
        'farmer-organization-location-update' => $controller->updateFarmerOrganizationLocation($_POST),
        'farmer' => $controller->storeFarmer($_POST, $_FILES),
        'farmer-update' => $controller->updateFarmer($_POST, $_FILES),
        'transaction' => $controller->storeTransaction($_POST),
        'transaction-update' => $controller->updateTransaction($_POST),
        'delivery-schedule' => $controller->storeDeliverySchedule($_POST),
        'delivery-schedule-status' => $controller->updateDeliveryScheduleStatus($_POST),
        'delivery-day-status' => $controller->updateDeliveryScheduleDayStatus($_POST),
        'farmer-delete' => $controller->deleteFarmer($_POST),
        'transaction-delete' => $controller->deleteTransaction($_POST),
        'offline-sync-transaction' => $controller->syncOfflineTransaction($_POST),
        'error-report' => $controller->storeErrorReport($_POST),
        'support-ticket' => $controller->storeSupportTicket($_POST, $_FILES),
        'support-ticket-reply' => $controller->replySupportTicket($_POST),
        'support-ticket-complete' => $controller->completeSupportTicket($_POST),
        'support-ticket-archive' => $controller->archiveSupportTicket($_POST),
        'support-ticket-bulk' => $controller->bulkSupportTickets($_POST),
        'notifications-clear' => $controller->clearNotifications($_POST),
        'notification-preferences' => $controller->saveNotificationPreferences($_POST),
        'signatory-add' => $controller->storeSignatories($_POST),
        'signatory-update' => $controller->updateSignatory($_POST),
        'signatory-delete' => $controller->deleteSignatory($_POST),
        default => $controller->redirect(),
    };

    exit;
}

if (isset($_GET['duplicate_check'])) {
    $controller->duplicateCheck($_GET);
    exit;
}

if (isset($_GET['notification_id'])) {
    $controller->openNotification($_GET);
    exit;
}

match ($_GET['page'] ?? 'dashboard') {
    'schedule-status' => $controller->deliverySchedulePublicStatus($_GET),
    'records' => $controller->records($_GET),
    'farmers' => $controller->farmerRecords($_GET),
    'farmer-view' => $controller->farmerView($_GET),
    'transactions' => $controller->transactionRecords($_GET),
    'encode-farmer' => $controller->encodeFarmer(),
    'individual-delivery' => $controller->individualDelivery(),
    'organization-delivery' => $controller->organizationDelivery(),
    'delivery-schedules' => $controller->deliverySchedules($_GET),
    'delivery-schedule-confirmation' => $controller->deliveryScheduleConfirmation($_GET),
    'reports' => $controller->reports($_GET),
    'sectoral-report' => $controller->sectoralReport($_GET),
    'report-settings' => $controller->reportSettings(),
    'account' => $controller->account(),
    'notifications' => $controller->notifications(),
    'users' => $controller->users(),
    'system-maintenance' => $controller->systemMaintenance($_GET),
    'database-management' => $controller->databaseManagement($_GET),
    'display-settings' => $controller->displaySettings(),
    'tech-support' => $controller->techSupport(),
    'tech-support-archive' => $controller->archivedTechSupport(),
    'user-manual' => $controller->userManual(),
    'locations' => $controller->locationLibrary(),
    'central-office-directory' => $controller->centralOfficeLibrary(),
    'farmer-organization-library' => $controller->farmerOrganizationLibrary($_GET),
    'farmer-organization-view' => $controller->farmerOrganizationView($_GET),
    default => $controller->index(),
};
