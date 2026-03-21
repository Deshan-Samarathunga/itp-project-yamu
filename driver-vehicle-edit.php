<?php
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/vehicle-management.php';
    carzo_start_session();
carzo_require_user_roles(['driver'], 'signin.php', ['active', 'pending', 'verified'], 'access-denied.php');
    include 'includes/config.php';
    $page_title = "Driver Vehicles";

    $vehicleId = isset($_GET['vehicle_id']) ? (int) $_GET['vehicle_id'] : 0;
    $vehicle = carzo_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle || (int) ($vehicle['owner_user_id'] ?? 0) !== (int) ($_SESSION['user']['user_ID'] ?? 0)) {
        carzo_redirect_with_message('driver-vehicles.php', 'error', 'Vehicle not found');
    }

    $brandResult = mysqli_query($conn, "SELECT * FROM brands WHERE brand_status = 1 ORDER BY brand_name ASC");
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
                    $currentAccountPage = 'driver-vehicles';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <form action="includes/driver-vehicle-process.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <h3>Edit Vehicle Listing</h3>
                        <input type="hidden" name="vehicleId" value="<?php echo $vehicle['vehicle_id']; ?>" />
                        <div class="form-group">
                            <label for="vehicleTitle">Vehicle Title:</label>
                            <input type="text" name="vehicleTitle" id="vehicleTitle" value="<?php echo carzo_e($vehicle['vehicle_title']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="vehicleBrand">Brand:</label>
                            <select name="vehicleBrand" id="vehicleBrand" required>
                                <option value="<?php echo carzo_e($vehicle['vehicle_brand']); ?>"><?php echo carzo_e($vehicle['vehicle_brand']); ?></option>
                                <?php while ($brandResult && $brand = mysqli_fetch_assoc($brandResult)) { ?>
                                    <option value="<?php echo carzo_e($brand['brand_name']); ?>"><?php echo carzo_e($brand['brand_name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="vehicleDesc">Vehicle Overview:</label>
                            <textarea name="vehicleDesc" id="vehicleDesc" rows="5"><?php echo carzo_e($vehicle['vehicle_desc']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="location">Location:</label>
                            <input type="text" name="location" id="location" value="<?php echo carzo_e($vehicle['location']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="registration_number">Registration No:</label>
                            <input type="text" name="registration_number" id="registration_number" value="<?php echo carzo_e($vehicle['registration_number']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="transmission">Transmission:</label>
                            <select name="transmission" id="transmission" required>
                                <option value="<?php echo carzo_e($vehicle['transmission']); ?>"><?php echo carzo_e($vehicle['transmission']); ?></option>
                                <option value="Automatic">Automatic</option>
                                <option value="Manual">Manual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fuelType">Fuel Type:</label>
                            <select name="fuelType" id="fuelType" required>
                                <option value="<?php echo carzo_e($vehicle['fuel_type']); ?>"><?php echo carzo_e($vehicle['fuel_type']); ?></option>
                                <option value="Diesel">Diesel</option>
                                <option value="Petrol">Petrol</option>
                                <option value="Electric">Electric</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="Gas">Gas</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="modelYear">Model Year:</label>
                            <input type="number" name="modelYear" id="modelYear" value="<?php echo carzo_e($vehicle['year']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="engineCap">Engine Capacity (CC):</label>
                            <input type="number" name="engineCap" id="engineCap" value="<?php echo carzo_e($vehicle['engine_capacity']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="capacity">Seat Capacity:</label>
                            <input type="number" name="capacity" id="capacity" value="<?php echo carzo_e($vehicle['capacity']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="price">Daily Price:</label>
                            <input type="number" step="0.01" name="price" id="price" value="<?php echo carzo_e($vehicle['price']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="listing_status_display">Listing Status:</label>
                            <input type="text" id="listing_status_display" value="<?php echo carzo_e(ucfirst($vehicle['listing_status'])); ?>" readonly />
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
                            <input type="date" name="service_date" id="service_date" value="<?php echo carzo_e($vehicle['service_date']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="next_service_date">Next Service Date:</label>
                            <input type="date" name="next_service_date" id="next_service_date" value="<?php echo carzo_e($vehicle['next_service_date']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="service_cost">Service Cost:</label>
                            <input type="number" step="0.01" name="service_cost" id="service_cost" value="<?php echo carzo_e($vehicle['service_cost']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="service_notes">Service Notes:</label>
                            <textarea name="service_notes" id="service_notes"><?php echo carzo_e($vehicle['service_notes']); ?></textarea>
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
                        <input type="submit" value="Update Listing" class="btn main-btn" name="updateDriverVehicle" />
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
</body>
</html>



