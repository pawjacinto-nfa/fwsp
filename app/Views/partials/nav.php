<?php
$currentUserId = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
$notifications = $currentUserId ? \App\Models\Notification::all($currentUserId) : [];
$unreadNotifications = $currentUserId ? \App\Models\Notification::unreadCount($currentUserId) : 0;
?>
<nav class="navbar navbar-expand-lg sticky-top app-nav">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand app-logo" href="index.php">
            <img src="assets/images/nfa-website-banner.png" alt="National Food Authority Farmer-Seller Registry">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav main-menu ms-lg-auto me-lg-3 mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link home-link" href="index.php">Home</a></li>
                <?php if (in_array($_SESSION['role'] ?? '', ['Warehouse Supervisor', 'Regional/Branch Manager', 'Super Admin'], true)): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="index.php?page=encode-farmer" role="button" data-bs-toggle="dropdown" aria-expanded="false">Encode</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=encode-farmer">Farmer Profile</a></li>
                            <li><a class="dropdown-item" href="index.php?page=individual-delivery">Individual Delivery</a></li>
                            <li><a class="dropdown-item" href="index.php?page=organization-delivery">Farmers Organization Delivery</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') !== 'Viewer'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="index.php?page=records" role="button" data-bs-toggle="dropdown" aria-expanded="false">Records</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=farmers">Farmers</a></li>
                            <li><a class="dropdown-item" href="index.php?page=farmer-organization-library">Farmer Organizations</a></li>
                            <li><a class="dropdown-item" href="index.php?page=transactions">Transactions</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'] ?? '', ['Warehouse Supervisor', 'Regional/Branch Manager', 'Super Admin'], true)): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="index.php?page=locations" role="button" data-bs-toggle="dropdown" aria-expanded="false">Library</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=locations">Location Library</a></li>
                            <li><a class="dropdown-item" href="index.php?page=central-office-directory">Central Office Directory</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="index.php?page=reports" role="button" data-bs-toggle="dropdown" aria-expanded="false">Reports</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=reports">Summary Report</a></li>
                            <li><a class="dropdown-item" href="index.php?page=sectoral-report">SDD Analytics</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="index.php?page=tech-support" role="button" data-bs-toggle="dropdown" aria-expanded="false">Support</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=tech-support">Tech Support</a></li>
                            <?php if (($_SESSION['role'] ?? '') === 'Super Admin'): ?>
                                <li><a class="dropdown-item" href="index.php?page=users">User Control</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($_SESSION['user'])): ?>
                    <div class="dropdown user-notification-dropdown">
                        <button class="user-chip" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Open notifications and account menu">
                            <span class="user-avatar-wrap">
                                <?php if (!empty($_SESSION['profile_image'])): ?>
                                    <img src="<?= e($_SESSION['profile_image']) ?>" alt="">
                                <?php else: ?>
                                    <span class="user-avatar-fallback"><?= e(substr($_SESSION['user'], 0, 1)) ?></span>
                                <?php endif; ?>
                                <?php if ($unreadNotifications > 0): ?>
                                    <span class="notification-alert-badge" data-notification-badge aria-label="<?= e($unreadNotifications) ?> unread notifications"><?= e($unreadNotifications > 99 ? '99+' : $unreadNotifications) ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="user-chip-text">
                                <strong><?= e($_SESSION['user']) ?></strong>
                                <small><?= e($_SESSION['default_location'] ?? 'Not set') ?></small>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-menu">
                            <div class="notification-menu-head">
                                <strong>Notifications</strong>
                                <div class="notification-menu-actions">
                                    <?php if ($unreadNotifications > 0): ?>
                                        <span><?= number_format($unreadNotifications) ?> unread</span>
                                    <?php endif; ?>
                                    <?php if ($notifications !== []): ?>
                                        <form method="post" data-notifications-clear-form>
                                            <input type="hidden" name="action" value="notifications-clear">
                                            <input type="hidden" name="return_to" value="<?= e($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '') ?>">
                                            <button type="submit">Clear all</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="notification-list" data-notification-list>
                                <?php foreach ($notifications as $notification): ?>
                                    <a class="notification-item <?= empty($notification['read']) ? 'is-unread' : '' ?>" href="index.php?notification_id=<?= e($notification['id']) ?>">
                                        <p><?= e($notification['message']) ?></p>
                                        <small><?= e($notification['time']) ?></small>
                                    </a>
                                <?php endforeach; ?>
                                <?php if ($notifications === []): ?>
                                    <div class="notification-empty">No notifications yet.</div>
                                <?php endif; ?>
                            </div>
                            <div class="notification-menu-footer">
                                <a href="index.php?page=account">Edit Profile Settings</a>
                            </div>
                        </div>
                    </div>
                    <form method="post">
                        <input type="hidden" name="action" value="logout">
                        <button class="btn btn-sm btn-success" type="submit">Logout</button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-sm btn-success" type="button" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                    <button class="btn btn-sm btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<button class="mode-toggle floating-mode-toggle" type="button" id="themeToggle" aria-label="Toggle visual contrast"><span aria-hidden="true"></span></button>
