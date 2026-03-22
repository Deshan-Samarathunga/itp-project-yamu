<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
yamu_require_assigned_user_role(['customer'], 'signin.php', ['active', 'verified'], 'access-denied.php');
include 'includes/config.php';

if (yamu_is_admin_panel_role(yamu_current_user_role())) {
    yamu_redirect_with_message('role-switch.php', 'error', 'Switch to your customer role to request provider access');
}

$page_title = 'Role Activation';
$currentUser = yamu_current_user();
$userId = (int) ($currentUser['user_ID'] ?? 0);
$assignments = yamu_fetch_user_roles(
    $conn,
    $userId,
    $currentUser['primary_role'] ?? $currentUser['role'] ?? 'customer',
    $currentUser['account_status'] ?? 'active',
    $currentUser['verification_status'] ?? 'verified'
);
$assignedRoles = array_keys($assignments);
$availableRoles = array_values(array_filter(yamu_fetch_available_roles($conn), function ($role) use ($assignedRoles) {
    if (in_array($role['role_key'], $assignedRoles, true)) {
        return false;
    }

    return in_array($role['role_key'], ['customer', 'driver', 'staff'], true);
}));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('includes/header.php'); ?>
</head>
<body>
    <?php include('includes/menu.php'); ?>

    <section class="profile">
        <?php include('includes/alert.php'); ?>
        <div class="container">
            <div class="row">
                <?php
                    $currentAccountPage = 'role-activation';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3>Role Activation</h3>
                    <p>Customer accounts can request driver or staff access here. Admin roles must be seeded in the database and managed outside public self-service flows.</p>
                    <?php if (empty($availableRoles)) { ?>
                        <p>All available roles are already assigned to your account.</p>
                        <a href="role-switch.php" class="btn main-btn">Back to Role Switch</a>
                    <?php } else { ?>
                        <form action="includes/role-management.php" method="POST" class="signup-form" onsubmit="return validateActivationForm();">
                            <div class="form-group">
                                <label for="role">Select Role to Activate:</label>
                                <select name="role" id="role" onchange="toggleRoleFields()" required>
                                    <?php foreach ($availableRoles as $role) { ?>
                                        <option value="<?php echo yamu_e($role['role_key']); ?>">
                                            <?php echo yamu_e($role['role_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div id="driver-fields" style="display: none;">
                                <div class="form-group">
                                    <label for="driving_license_number">Driving License Number:</label>
                                    <input type="text" name="driving_license_number" id="driving_license_number" />
                                </div>
                                <div class="form-group">
                                    <label for="license_expiry_date">License Expiry Date:</label>
                                    <input type="date" name="license_expiry_date" id="license_expiry_date" />
                                </div>
                                <div class="form-group">
                                    <label for="nic_id">NIC / ID:</label>
                                    <input type="text" name="nic_id" id="nic_id" />
                                </div>
                                <div class="form-group">
                                    <label for="service_area">Service Area / Location:</label>
                                    <input type="text" name="service_area" id="service_area" />
                                </div>
                                <div class="form-group">
                                    <label for="provider_details">Provider Details:</label>
                                    <textarea name="provider_details" id="provider_details" rows="4" placeholder="Describe your driving service, experience, and coverage."></textarea>
                                </div>
                            </div>

                            <div id="staff-fields" style="display: none;">
                                <div class="form-group">
                                    <label for="store_name">Store Name:</label>
                                    <input type="text" name="store_name" id="store_name" />
                                </div>
                                <div class="form-group">
                                    <label for="store_owner">Store Owner / Contact Person:</label>
                                    <input type="text" name="store_owner" id="store_owner" />
                                </div>
                                <div class="form-group">
                                    <label for="business_registration_number">Business Registration Number:</label>
                                    <input type="text" name="business_registration_number" id="business_registration_number" />
                                </div>
                                <div class="form-group">
                                    <label for="store_address">Store Address:</label>
                                    <textarea name="store_address" id="store_address"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="store_contact_number">Store Contact Number:</label>
                                    <input type="text" name="store_contact_number" id="store_contact_number" />
                                </div>
                                <div class="form-group">
                                    <label for="store_email">Store Email:</label>
                                    <input type="email" name="store_email" id="store_email" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="activation_notes">Notes (Optional):</label>
                                <textarea name="activation_notes" id="activation_notes" placeholder="Add a short note for role activation review"></textarea>
                            </div>
                            <input type="submit" value="Activate Role" class="btn main-btn" name="activateRole" />
                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
    <script>
        function toggleRoleFields() {
            const role = document.getElementById('role').value;
            const driverFields = document.getElementById('driver-fields');
            const staffFields = document.getElementById('staff-fields');
            const licenseInput = document.getElementById('driving_license_number');
            const nicInput = document.getElementById('nic_id');
            const serviceAreaInput = document.getElementById('service_area');
            const providerDetailsInput = document.getElementById('provider_details');
            const storeNameInput = document.getElementById('store_name');
            const storeOwnerInput = document.getElementById('store_owner');
            const businessRegInput = document.getElementById('business_registration_number');
            const storeAddressInput = document.getElementById('store_address');
            const storeContactInput = document.getElementById('store_contact_number');
            const storeEmailInput = document.getElementById('store_email');

            driverFields.style.display = role === 'driver' ? 'block' : 'none';
            staffFields.style.display = role === 'staff' ? 'block' : 'none';

            if (licenseInput) {
                licenseInput.required = role === 'driver';
            }
            if (nicInput) {
                nicInput.required = role === 'driver';
            }
            if (serviceAreaInput) {
                serviceAreaInput.required = role === 'driver';
            }
            if (providerDetailsInput) {
                providerDetailsInput.required = role === 'driver';
            }
            if (storeNameInput) {
                storeNameInput.required = role === 'staff';
            }
            if (storeOwnerInput) {
                storeOwnerInput.required = role === 'staff';
            }
            if (businessRegInput) {
                businessRegInput.required = role === 'staff';
            }
            if (storeAddressInput) {
                storeAddressInput.required = role === 'staff';
            }
            if (storeContactInput) {
                storeContactInput.required = role === 'staff';
            }
            if (storeEmailInput) {
                storeEmailInput.required = role === 'staff';
            }
        }

        function validateActivationForm() {
            const role = document.getElementById('role').value;
            if (role === 'driver') {
                const license = document.getElementById('driving_license_number').value.trim();
                const nic = document.getElementById('nic_id').value.trim();
                const serviceArea = document.getElementById('service_area').value.trim();
                const providerDetails = document.getElementById('provider_details').value.trim();
                if (license === '' || nic === '' || serviceArea === '' || providerDetails === '') {
                    alert('Please complete all required driver application fields.');
                    return false;
                }
            }
            if (role === 'staff') {
                const storeName = document.getElementById('store_name').value.trim();
                const storeOwner = document.getElementById('store_owner').value.trim();
                const businessReg = document.getElementById('business_registration_number').value.trim();
                const storeAddress = document.getElementById('store_address').value.trim();
                const storeContact = document.getElementById('store_contact_number').value.trim();
                const storeEmail = document.getElementById('store_email').value.trim();
                if (storeName === '' || storeOwner === '' || businessReg === '' || storeAddress === '' || storeContact === '' || storeEmail === '') {
                    alert('Please complete all required staff application fields.');
                    return false;
                }
            }
            return true;
        }

        toggleRoleFields();
    </script>
</body>
</html>
