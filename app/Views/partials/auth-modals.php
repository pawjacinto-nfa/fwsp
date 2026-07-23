<div class="modal fade auth-modal" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="login-modal-stack">
            <?php if (isset($_GET['login_error'])): ?>
                <div class="login-credential-alert" role="alert" data-login-credential-alert>
                    <span>Incorrect credentials</span>
                    <strong data-login-error-countdown>5</strong>
                </div>
            <?php endif; ?>
            <form method="post" class="modal-content login-modal-content">
                <input type="hidden" name="action" value="login">
                <div class="modal-header login-modal-header">
                    <div class="login-modal-brand">
                        <img src="/fwsp/assets/images/farmer-seller-registry-logo-transparent.png" alt="Farmer Seller Registry logo">
                        <h2 class="modal-title">Farmer Seller Registry</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Username</label>
                    <input required name="username" class="form-control mb-3" data-remember-username>
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" data-password-field>
                        <button class="btn btn-outline-secondary" type="button" data-password-toggle aria-label="Show password" title="Show password">&#128065;</button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="remember_me" id="rememberLogin" value="1" data-remember-login>
                            <label class="form-check-label" for="rememberLogin">Remember me</label>
                        </div>
                        <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" data-bs-dismiss="modal">Forgot password?</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success w-100" type="submit">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade auth-modal" id="activityLoginRequiredModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5">Login Required</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">You must be logged in to do this action.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" type="button" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade auth-modal" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
            <input type="hidden" name="action" value="password-reset-request">
            <div class="modal-header">
                <h2 class="modal-title fs-5">Reset password</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Enter your active username. The System Administrator will be notified with your request.</p>
                <label class="form-label">Username</label>
                <input required name="username" class="form-control" data-forgot-username>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" type="submit">Send Reset Request</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade auth-modal" id="changePasswordModal" tabindex="-1" aria-hidden="true" data-force-open="<?= !empty($_SESSION['password_reset_user_id']) ? 'true' : 'false' ?>">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
            <input type="hidden" name="action" value="password-reset-complete">
            <input type="hidden" name="username" value="<?= e($_SESSION['password_reset_username'] ?? '') ?>">
            <div class="modal-header">
                <h2 class="modal-title fs-5">Change password</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Your request to change password has been approved. Please change your password here.</p>
                <label class="form-label">New Password</label>
                <div class="input-group mb-3">
                    <input required type="password" name="password" class="form-control" data-password-field>
                    <button class="btn btn-outline-secondary" type="button" data-password-toggle aria-label="Show password" title="Show password">&#128065;</button>
                </div>
                <label class="form-label">Confirm New Password</label>
                <div class="input-group">
                    <input required type="password" name="password_confirmation" class="form-control" data-password-field>
                    <button class="btn btn-outline-secondary" type="button" data-password-toggle aria-label="Show password" title="Show password">&#128065;</button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" type="submit">Change Password</button>
            </div>
        </form>
    </div>
</div>

<?php
$registrationUsernameError = $_SESSION['registration_username_error'] ?? null;
unset($_SESSION['registration_username_error']);
$duplicateRegistrationUsername = is_array($registrationUsernameError)
    ? (string) ($registrationUsernameError['username'] ?? '')
    : '';
$duplicateRegistrationMessage = is_array($registrationUsernameError)
    ? (string) ($registrationUsernameError['message'] ?? '')
    : '';
?>
<div class="modal fade auth-modal" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" class="modal-content">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="office_scope" value="field" data-registration-office-scope>
            <div class="modal-header">
                <h2 class="modal-title fs-5">Registration</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3" role="alert">
                    Please take note that your username is your six digit employee number. Keep your password in mind for future logins.
                    Passwords must be at least 9 characters and include letters and numbers. Special characters are allowed.
                </div>
                <label class="form-label">Full Name</label>
                <input required name="full_name" class="form-control mb-3">
                <div class="mb-3">
                    <label class="form-label">Username (Employee Number)</label>
                    <input required name="username" class="form-control<?= $duplicateRegistrationMessage !== '' ? ' is-invalid' : '' ?>" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" value="<?= e($duplicateRegistrationUsername) ?>" title="Enter your six digit employee number using numbers only." data-registration-username<?= $duplicateRegistrationMessage !== '' ? ' aria-describedby="registrationUsernameError" aria-invalid="true"' : '' ?>>
                    <?php if ($duplicateRegistrationMessage !== ''): ?>
                        <div class="invalid-feedback d-block" id="registrationUsernameError" data-registration-username-error><?= e($duplicateRegistrationMessage) ?></div>
                    <?php endif; ?>
                </div>
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#registerFieldOffice" data-registration-scope-tab="field" aria-selected="true">Field Office</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#registerCentralOffice" data-registration-scope-tab="central" aria-selected="false">Central Office</button>
                    </li>
                </ul>
                <div class="tab-content mb-3">
                    <div class="tab-pane fade show active" id="registerFieldOffice" role="tabpanel" data-registration-scope-panel="field">
                        <div class="row g-3">
                            <?php
                            $locationClass = 'col-md-6';
                            $locationRequired = false;
                            $locationRequiredLevels = [];
                            $locationIncludeAll = false;
                            $locationLabelWarehouse = 'Facility Name';
                            require BASE_PATH . '/app/Views/partials/location-selects.php';
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="registerCentralOffice" role="tabpanel" data-registration-scope-panel="central">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Department</label>
                                <select disabled name="central_department_id" class="form-select" data-central-office-level="department">
                                    <option value="">Select</option>
                                    <?php foreach (\App\Models\CentralOffice::departments() as $department): ?>
                                        <option value="<?= e($department['id']) ?>"><?= e($department['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Division</label>
                                <select disabled name="central_division_id" class="form-select" data-central-office-level="division">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Service/Unit</label>
                                <select disabled name="central_unit_id" class="form-select" data-central-office-level="unit">
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <label class="form-label">Designation</label>
                <input required name="designation" class="form-control mb-3">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input required type="email" name="email" class="form-control" pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}" title="Enter a valid email address.">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input required name="contact_number" class="form-control" inputmode="numeric" pattern="09[0-9]{9}" minlength="11" maxlength="11" placeholder="09xxxxxxxxx" title="Enter an 11 digit contact number, for example 09xxxxxxxxx.">
                    </div>
                </div>
                <label class="form-label">Password</label>
                <div class="input-group mb-3">
                    <input required type="password" name="password" class="form-control" pattern="(?=.*[A-Za-z])(?=.*[0-9]).{9,}" minlength="9" title="Password must be at least 9 characters and include letters and numbers. Special characters are allowed." data-password-field>
                    <button class="btn btn-outline-secondary" type="button" data-password-hold-toggle aria-label="Hold to show password" title="Hold to show password">&#128065;</button>
                </div>
                <label class="form-label">Password Confirmation</label>
                <div class="input-group">
                    <input required type="password" name="password_confirmation" class="form-control" pattern="(?=.*[A-Za-z])(?=.*[0-9]).{9,}" minlength="9" title="Password confirmation must be at least 9 characters and include letters and numbers. Special characters are allowed." data-password-field>
                    <button class="btn btn-outline-secondary" type="button" data-password-hold-toggle aria-label="Hold to show password confirmation" title="Hold to show password">&#128065;</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning">Submit Request</button>
            </div>
        </form>
    </div>
</div>
