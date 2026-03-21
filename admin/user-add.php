<?php
    require_once __DIR__ . '/../includes/auth.php';
    carzo_start_session();
    carzo_require_admin('index.php');
    $page_title = "Users";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        include('includes/header.php');
    ?>
</head>
<body>

    <div class="grid-container">
        <?php
            include('includes/menu.php');
        ?>

        <?php
            include('includes/aside.php');
        ?>

        <main class="main">
            <?php
                include('../includes/alert.php');
            ?>
            <h2>Create User</h2>

            <div class="main-cards">
                <div class="card">
                    <form action="includes/user-process.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <div class="form-group">
                            <label for="fullName">Full Name:</label>
                            <input type="text" name="fullName" id="fullName" placeholder="Enter Full Name" required />
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input type="email" name="email" id="email" placeholder="Enter Email Address" required />
                        </div>
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" name="username" id="username" placeholder="Enter Username" required />
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" placeholder="Enter Password" required />
                        </div>
                        <div class="form-group">
                            <label for="role">Role:</label>
                            <select name="role" id="role" onchange="toggleDriverFields()">
                                <option value="customer">Customer</option>
                                <option value="driver">Driver</option>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="account_status">Account Status:</label>
                            <select name="account_status" id="account_status">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="suspended">Suspended</option>
                                <option value="rejected">Rejected</option>
                                <option value="deactivated">Deactivated</option>
                            </select>
                        </div>
                        <div class="form-group" id="verification-row">
                            <label for="verification_status">Verification Status:</label>
                            <select name="verification_status" id="verification_status">
                                <option value="verified">Verified</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="unverified">Unverified</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="tel" name="phone" id="phone" placeholder="Enter Phone Number" />
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth:</label>
                            <input type="date" name="dob" id="dob" />
                        </div>
                        <div class="form-group">
                            <label for="city">City:</label>
                            <input type="text" name="city" id="city" placeholder="Enter City" />
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <textarea name="address" id="address" placeholder="Enter Address"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="profileImage">Profile Image:</label>
                            <input type="file" name="profileImage" id="profileImage" class="custom" />
                        </div>
                        <div id="driver-fields">
                            <div class="form-group">
                                <label for="license_or_nic">License / NIC:</label>
                                <input type="text" name="license_or_nic" id="license_or_nic" placeholder="Enter License Number or NIC" />
                            </div>
                            <div class="form-group">
                                <label for="bio">Driver Bio:</label>
                                <textarea name="bio" id="bio" placeholder="Tell customers a little about this driver"></textarea>
                            </div>
                        </div>
                        <input type="reset" value="Cancel" class="btn second-btn" />
                        <input type="submit" value="Create User" class="btn main-btn" name="createUser" />
                    </form>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2023 EM</div>
            <div class="footer__signature">Made with love by pure genius</div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function toggleDriverFields() {
            const role = document.getElementById("role").value;
            const driverFields = document.getElementById("driver-fields");
            const verificationRow = document.getElementById("verification-row");
            const accountStatus = document.getElementById("account_status");
            const verificationStatus = document.getElementById("verification_status");
            const licenseInput = document.getElementById("license_or_nic");

            if (role === "driver") {
                driverFields.style.display = "block";
                verificationRow.style.display = "flex";
                licenseInput.required = true;

                if (accountStatus.value === "active") {
                    accountStatus.value = "pending";
                }

                if (verificationStatus.value === "verified") {
                    verificationStatus.value = "pending";
                }
            } else if (role === "staff") {
                driverFields.style.display = "none";
                verificationRow.style.display = "flex";
                licenseInput.required = false;

                if (accountStatus.value === "active") {
                    accountStatus.value = "pending";
                }

                if (verificationStatus.value === "verified") {
                    verificationStatus.value = "pending";
                }
            } else {
                driverFields.style.display = "none";
                verificationRow.style.display = "none";
                licenseInput.required = false;
                verificationStatus.value = "verified";

                if (accountStatus.value === "pending") {
                    accountStatus.value = "active";
                }
            }
        }

        toggleDriverFields();
    </script>
</body>
</html>
