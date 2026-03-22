<?php
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/vehicle-management.php';
    yamu_start_session();
    yamu_require_admin('index.php');
    include 'includes/config.php';
    $page_title = "Vehicles-Edit";

    $vehicleId = isset($_GET['vehicle_id']) ? (int) $_GET['vehicle_id'] : 0;
    $vehicle = yamu_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle) {
        yamu_redirect_with_message('vehicle.php', 'error', 'Vehicle not found');
    }

    $brandResult = mysqli_query($conn, "SELECT * FROM brands WHERE brand_status = 1 ORDER BY brand_name ASC");
    if (yamu_table_exists($conn, 'user_roles')) {
        $ownerResult = mysqli_query(
            $conn,
            "SELECT DISTINCT u.user_id, u.full_name, u.email, 'staff' AS role
             FROM users u
             INNER JOIN user_roles ur
                 ON ur.user_id = u.user_id
                AND ur.role_key = 'staff'
             WHERE ur.role_status IN ('active', 'verified')
               AND ur.verification_status IN ('approved', 'verified')
               AND u.account_status IN ('active', 'verified')
             ORDER BY u.full_name ASC"
        );
    } else {
        $ownerResult = mysqli_query(
            $conn,
            "SELECT user_id, full_name, email, role
             FROM users
             WHERE role = 'staff'
               AND account_status IN ('active', 'verified')
             ORDER BY full_name ASC"
        );
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('includes/header.php'); ?>
</head>
<body>

    <div class="grid-container">
        <?php include('includes/menu.php'); ?>
        <?php include('includes/aside.php'); ?>

        <main class="main">
            <?php include('../includes/alert.php'); ?>
            <h2>Edit Vehicle</h2>

            <div class="main-cards">
                <div class="card">
                    <form action="includes/vehicle-process.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <p>Basic Info</p>
                        <input type="hidden" name="vehicleId" value="<?php echo $vehicle['vehicle_id']; ?>">
                        <div class="form-group">
                            <label for="owner_user_id">Owner:</label>
                            <select name="owner_user_id" id="owner_user_id" required>
                                <?php if (!$ownerResult || mysqli_num_rows($ownerResult) === 0) { ?>
                                    <option value="">No approved staff accounts available</option>
                                <?php } ?>
                                <?php while ($ownerResult && $owner = mysqli_fetch_assoc($ownerResult)) { ?>
                                    <option value="<?php echo $owner['user_id']; ?>" <?php echo ((int) $vehicle['owner_user_id'] === (int) $owner['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo yamu_e($owner['full_name'] . ' (' . ucfirst($owner['role']) . ') - ' . $owner['email']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="vehicleTitle">Vehicle Title:</label>
                            <input type="text" name="vehicleTitle" value="<?php echo yamu_e($vehicle['vehicle_title']); ?>" id="vehicleTitle" required />
                        </div>
                        <div class="form-group">
                            <label for="vehicleDesc">Vehicle Overview:</label>
                            <textarea name="vehicleDesc" id="vehicleDesc" cols="30" rows="5"><?php echo yamu_e($vehicle['vehicle_desc']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="vehicleBrand">Brand:</label>
                            <select name="vehicleBrand" id="vehicleBrand" required>
                                <option value="<?php echo yamu_e($vehicle['vehicle_brand']); ?>"><?php echo yamu_e($vehicle['vehicle_brand']); ?></option>
                                <?php while ($brandResult && $brand = mysqli_fetch_assoc($brandResult)) { ?>
                                    <option value="<?php echo yamu_e($brand['brand_name']); ?>"><?php echo yamu_e($brand['brand_name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="location">Location:</label>
                            <input type="text" name="location" id="location" value="<?php echo yamu_e($vehicle['location']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="registration_number">Registration No:</label>
                            <input type="text" name="registration_number" id="registration_number" value="<?php echo yamu_e($vehicle['registration_number']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="transmission">Transmission:</label>
                            <select name="transmission" id="transmission" required>
                                <option value="<?php echo yamu_e($vehicle['transmission']); ?>"><?php echo yamu_e($vehicle['transmission']); ?></option>
                                <option value="Automatic">Automatic</option>
                                <option value="Manual">Manual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fuelType">Fuel Type:</label>
                            <select name="fuelType" id="fuelType" required>
                                <option value="<?php echo yamu_e($vehicle['fuel_type']); ?>"><?php echo yamu_e($vehicle['fuel_type']); ?></option>
                                <option value="Diesel">Diesel</option>
                                <option value="Petrol">Petrol</option>
                                <option value="Electric">Electric</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="Gas">Gas</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="modelYear">Model Year:</label>
                            <input type="number" name="modelYear" value="<?php echo yamu_e($vehicle['year']); ?>" id="modelYear" required />
                        </div>
                        <div class="form-group">
                            <label for="engineCap">Engine Capacity (CC):</label>
                            <input type="number" name="engineCap" value="<?php echo yamu_e($vehicle['engine_capacity']); ?>" id="engineCap" required />
                        </div>
                        <div class="form-group">
                            <label for="capacity">Seat Capacity:</label>
                            <input type="number" name="capacity" value="<?php echo yamu_e($vehicle['capacity']); ?>" id="capacity" required />
                        </div>
                        <div class="form-group">
                            <label for="price">Daily Price:</label>
                            <input type="number" step="0.01" name="price" value="<?php echo yamu_e($vehicle['price']); ?>" id="price" required />
                        </div>
                        <div class="form-group">
                            <label for="listing_status">Listing Status:</label>
                            <select name="listing_status" id="listing_status">
                                <option value="approved" <?php echo ($vehicle['listing_status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="pending" <?php echo ($vehicle['listing_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="inactive" <?php echo ($vehicle['listing_status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                <option value="rejected" <?php echo ($vehicle['listing_status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="availability_status">Availability:</label>
                            <select name="availability_status" id="availability_status">
                                <option value="available" <?php echo ($vehicle['availability_status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                                <option value="booked" <?php echo ($vehicle['availability_status'] === 'booked') ? 'selected' : ''; ?>>Booked</option>
                                <option value="unavailable" <?php echo ($vehicle['availability_status'] === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="maintenance_status">Maintenance Status:</label>
                            <select name="maintenance_status" id="maintenance_status">
                                <option value="good" <?php echo ($vehicle['maintenance_status'] === 'good') ? 'selected' : ''; ?>>Good</option>
                                <option value="due soon" <?php echo ($vehicle['maintenance_status'] === 'due soon') ? 'selected' : ''; ?>>Due Soon</option>
                                <option value="under maintenance" <?php echo ($vehicle['maintenance_status'] === 'under maintenance') ? 'selected' : ''; ?>>Under Maintenance</option>
                                <option value="unavailable" <?php echo ($vehicle['maintenance_status'] === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="service_date">Service Date:</label>
                            <input type="date" name="service_date" id="service_date" value="<?php echo yamu_e($vehicle['service_date']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="next_service_date">Next Service Date:</label>
                            <input type="date" name="next_service_date" id="next_service_date" value="<?php echo yamu_e($vehicle['next_service_date']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="service_cost">Service Cost:</label>
                            <input type="number" step="0.01" name="service_cost" id="service_cost" value="<?php echo yamu_e($vehicle['service_cost']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="service_notes">Service Notes:</label>
                            <textarea name="service_notes" id="service_notes"><?php echo yamu_e($vehicle['service_notes']); ?></textarea>
                        </div>

                        <p>Vehicle Features</p>
                        <div class="grid-4">
                            <div class="accessories"><input type="checkbox" name="airConditioner" value="1" id="airConditioner" <?php echo ($vehicle['airConditioner'] === '1') ? 'checked' : ''; ?> /><label for="airConditioner">Air Conditioner</label></div>
                            <div class="accessories"><input type="checkbox" name="powerdoorLocks" value="1" id="powerdoorLocks" <?php echo ($vehicle['powerdoorlocks'] === '1') ? 'checked' : ''; ?> /><label for="powerdoorLocks">Power Door Locks</label></div>
                            <div class="accessories"><input type="checkbox" name="antiLockBrakingSystem" value="1" id="antiLockBrakingSystem" <?php echo ($vehicle['antilockbrakingsys'] === '1') ? 'checked' : ''; ?> /><label for="antiLockBrakingSystem">AntiLock Braking System</label></div>
                            <div class="accessories"><input type="checkbox" name="brakeAssist" value="1" id="brakeAssist" <?php echo ($vehicle['brakeassist'] === '1') ? 'checked' : ''; ?> /><label for="brakeAssist">Brake Assist</label></div>
                            <div class="accessories"><input type="checkbox" name="powerSteering" value="1" id="powerSteering" <?php echo ($vehicle['powersteering'] === '1') ? 'checked' : ''; ?> /><label for="powerSteering">Power Steering</label></div>
                            <div class="accessories"><input type="checkbox" name="driverAirbag" value="1" id="driverAirbag" <?php echo ($vehicle['driverairbag'] === '1') ? 'checked' : ''; ?> /><label for="driverAirbag">Driver Airbag</label></div>
                            <div class="accessories"><input type="checkbox" name="passengerAirbag" value="1" id="passengerAirbag" <?php echo ($vehicle['passengerairbag'] === '1') ? 'checked' : ''; ?> /><label for="passengerAirbag">Passenger Airbag</label></div>
                            <div class="accessories"><input type="checkbox" name="powerWindows" value="1" id="powerWindows" <?php echo ($vehicle['powerwindow'] === '1') ? 'checked' : ''; ?> /><label for="powerWindows">Power Windows</label></div>
                            <div class="accessories"><input type="checkbox" name="CDPlayer" value="1" id="CDPlayer" <?php echo ($vehicle['cdplayer'] === '1') ? 'checked' : ''; ?> /><label for="CDPlayer">CD Player</label></div>
                        </div>

                        <p>Update Images</p>
                        <div class="grid-3">
                            <div class="accessories"><label for="vehicleImg1">Image 1:</label><input type="file" name="vehicleImg1" id="vehicleImg1" /></div>
                            <div class="accessories"><label for="vehicleImg2">Image 2:</label><input type="file" name="vehicleImg2" id="vehicleImg2" /></div>
                            <div class="accessories"><label for="vehicleImg3">Image 3:</label><input type="file" name="vehicleImg3" id="vehicleImg3" /></div>
                            <div class="accessories"><label for="vehicleImg4">Image 4:</label><input type="file" name="vehicleImg4" id="vehicleImg4" /></div>
                        </div>
                        <input type="reset" value="Cancel" class="btn second-btn" />
                        <input type="submit" value="Update Vehicle" class="btn main-btn" name="updateVehicle" id="updateVehicle" />
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
</body>
</html>
