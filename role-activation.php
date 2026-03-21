<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();

    if (!carzo_is_user_authenticated()) {
        carzo_redirect_with_message('signin.php', 'error', 'Please sign in to continue');
    }

    include 'includes/config.php';
    $page_title = "Role Activation";
    $currentUser = carzo_current_user();
    $userId = (int) ($currentUser['user_ID'] ?? 0);
    $assignments = carzo_fetch_user_roles(
        $conn,
        $userId,
        $currentUser['primary_role'] ?? $currentUser['role'] ?? 'customer',
        $currentUser['account_status'] ?? 'active',
        $currentUser['verification_status'] ?? 'verified'
    );
    $assignedRoles = array_keys($assignments);
    $isAdminActor = carzo_is_admin_authenticated() && (int) ($_SESSION['admin']['user_id'] ?? 0) === $userId;
    $availableRoles = array_values(array_filter(carzo_fetch_available_roles($conn), function ($role) use ($assignedRoles, $isAdminActor) {
        if (in_array($role['role_key'], $assignedRoles, true)) {
            return false;
        }

        if ($role['role_key'] === 'admin' && !$isAdminActor) {
            return false;
        }

        return true;
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
                    <?php if (empty($availableRoles)) { ?>
                        <p>All available roles are already assigned to your account.</p>
                        <a href="role-switch.php" class="btn main-btn">Back to Role Switch</a>
                    <?php } else { ?>
                        <form action="includes/role-management.php" method="POST" class="signup-form" onsubmit="return validateActivationForm();">
                            <div class="form-group">
                                <label for="role">Select Role to Activate:</label>
                                <select name="role" id="role" onchange="toggleRoleFields()" required>
                                    <?php foreach ($availableRoles as $role) { ?>
                                        <option value="<?php echo carzo_e($role['role_key']); ?>">
                                            <?php echo carzo_e($role['role_name']); ?>
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
            const storeNameInput = document.getElementById('store_name');

            driverFields.style.display = role === 'driver' ? 'block' : 'none';
            staffFields.style.display = role === 'staff' ? 'block' : 'none';

            if (licenseInput) {
                licenseInput.required = role === 'driver';
            }
            if (storeNameInput) {
                storeNameInput.required = role === 'staff';
            }
        }

        function validateActivationForm() {
            const role = document.getElementById('role').value;
            if (role === 'driver') {
                const license = document.getElementById('driving_license_number').value.trim();
                if (license === '') {
                    alert('Driving license number is required for driver role activation.');
                    return false;
                }
            }
            if (role === 'staff') {
                const storeName = document.getElementById('store_name').value.trim();
                if (storeName === '') {
                    alert('Store name is required for staff role activation.');
                    return false;
                }
            }
            return true;
        }

        toggleRoleFields();
    </script>
</body>
</html>
