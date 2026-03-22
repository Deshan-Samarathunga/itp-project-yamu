<?php
    require_once __DIR__ . '/../includes/auth.php';
    yamu_start_session();
    yamu_require_admin('index.php');
    $page_title = "Vehicles-add"; 
    include 'includes/config.php';
    $brandResult = mysqli_query($conn, "SELECT * FROM brands WHERE brand_status = 1 ORDER BY brand_name ASC");
    $ownerResult = mysqli_query($conn, "SELECT user_id, full_name, email, role FROM users WHERE role IN ('admin','staff','driver') AND account_status IN ('active','pending') ORDER BY role, full_name ASC");
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
            <h2>Post A Vehicle</h2>

            <div class="main-cards">
                <div class="card">
                    <form action="includes/vehicle-process.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <p>Basic Info</p>
                        <div class="form-group">
                            <label for="owner_user_id">Owner:</label>
                            <select name="owner_user_id" id="owner_user_id" required>
                                <option value="">--Select Owner--</option>
                                <?php while ($ownerResult && $owner = mysqli_fetch_assoc($ownerResult)) { ?>
                                    <option value="<?php echo $owner['user_id']; ?>">
                                        <?php echo yamu_e($owner['full_name'] . ' (' . ucfirst($owner['role']) . ') - ' . $owner['email']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="vehicleTitle">Vehicle Title:</label>
                            <input type="text" name="vehicleTitle" id="vehicleTitle" placeholder="Enter Vehicle Title" required />
                        </div>
                        <div class="form-group">
                            <label for="vehicleDesc">Vehicle Overview:</label>
                            <textarea name="vehicleDesc" id="vehicleDesc" cols="30" rows="5"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="vehicleBrand">Brand:</label>
                            <select name="vehicleBrand" id="vehicleBrand" required>
                                <option value="">--Select a Brand--</option>
                                <?php while ($brandResult && $brand = mysqli_fetch_assoc($brandResult)) { ?>
                                    <option value="<?php echo yamu_e($brand['brand_name']); ?>"><?php echo yamu_e($brand['brand_name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="location">Location:</label>
                            <input type="text" name="location" id="location" placeholder="Enter Vehicle Location" required />
                        </div>
                        <div class="form-group">
                            <label for="registration_number">Registration No:</label>
                            <input type="text" name="registration_number" id="registration_number" placeholder="Enter Registration Number" required />
                        </div>
                        <div class="form-group">
                            <label for="transmission">Transmission:</label>
                            <select name="transmission" id="transmission" required>
                                <option value="">--Select Transmission--</option>
                                <option value="Automatic">Automatic</option>
                                <option value="Manual">Manual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fuelType">Fuel Type:</label>
                            <select name="fuelType" id="fuelType" required>
                                <option value="">--Select Fuel Type--</option>
                                <option value="Diesel">Diesel</option>
                                <option value="Petrol">Petrol</option>
                                <option value="Electric">Electric</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="Gas">Gas</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="modelYear">Model Year:</label>
                            <input type="number" name="modelYear" id="modelYear" required />
                        </div>
                        <div class="form-group">
                            <label for="engineCap">Engine Capacity (CC):</label>
                            <input type="number" name="engineCap" id="engineCap" required />
                        </div>
                        <div class="form-group">
                            <label for="capacity">Seat Capacity:</label>
                            <input type="number" name="capacity" id="capacity" required />
                        </div>
                        <div class="form-group">
                            <label for="price">Daily Price:</label>
                            <input type="number" step="0.01" name="price" id="price" required />
                        </div>
                        <div class="form-group">
                            <label for="listing_status">Listing Status:</label>
                            <select name="listing_status" id="listing_status">
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="availability_status">Availability:</label>
                            <select name="availability_status" id="availability_status">
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                                <option value="unavailable">Unavailable</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="maintenance_status">Maintenance Status:</label>
                            <select name="maintenance_status" id="maintenance_status">
                                <option value="good">Good</option>
                                <option value="due soon">Due Soon</option>
                                <option value="under maintenance">Under Maintenance</option>
                                <option value="unavailable">Unavailable</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="service_date">Service Date:</label>
                            <input type="date" name="service_date" id="service_date" />
                        </div>
                        <div class="form-group">
                            <label for="next_service_date">Next Service Date:</label>
                            <input type="date" name="next_service_date" id="next_service_date" />
                        </div>
                        <div class="form-group">
                            <label for="service_cost">Service Cost:</label>
                            <input type="number" step="0.01" name="service_cost" id="service_cost" />
                        </div>
                        <div class="form-group">
                            <label for="service_notes">Service Notes:</label>
                            <textarea name="service_notes" id="service_notes"></textarea>
                        </div>

                        <p>Vehicle Features</p>
                        <div class="grid-4">
                            <div class="accessories"><input type="checkbox" name="airConditioner" value="1" id="airConditioner" /><label for="airConditioner">Air Conditioner</label></div>
                            <div class="accessories"><input type="checkbox" name="powerdoorLocks" value="1" id="powerdoorLocks" /><label for="powerdoorLocks">Power Door Locks</label></div>
                            <div class="accessories"><input type="checkbox" name="antiLockBrakingSystem" value="1" id="antiLockBrakingSystem" /><label for="antiLockBrakingSystem">AntiLock Braking System</label></div>
                            <div class="accessories"><input type="checkbox" name="brakeAssist" value="1" id="brakeAssist" /><label for="brakeAssist">Brake Assist</label></div>
                            <div class="accessories"><input type="checkbox" name="powerSteering" value="1" id="powerSteering" /><label for="powerSteering">Power Steering</label></div>
                            <div class="accessories"><input type="checkbox" name="driverAirbag" value="1" id="driverAirbag" /><label for="driverAirbag">Driver Airbag</label></div>
                            <div class="accessories"><input type="checkbox" name="passengerAirbag" value="1" id="passengerAirbag" /><label for="passengerAirbag">Passenger Airbag</label></div>
                            <div class="accessories"><input type="checkbox" name="powerWindows" value="1" id="powerWindows" /><label for="powerWindows">Power Windows</label></div>
                            <div class="accessories"><input type="checkbox" name="CDPlayer" value="1" id="CDPlayer" /><label for="CDPlayer">CD Player</label></div>
                        </div>

                        <p>Upload Images</p>
                        <div class="grid-3">
                            <div class="accessories"><label for="vehicleImg1">Image 1:</label><input type="file" name="vehicleImg1" id="vehicleImg1" required /></div>
                            <div class="accessories"><label for="vehicleImg2">Image 2:</label><input type="file" name="vehicleImg2" id="vehicleImg2" required /></div>
                            <div class="accessories"><label for="vehicleImg3">Image 3:</label><input type="file" name="vehicleImg3" id="vehicleImg3" required /></div>
                            <div class="accessories"><label for="vehicleImg4">Image 4:</label><input type="file" name="vehicleImg4" id="vehicleImg4" required /></div>
                        </div>
                        <input type="reset" value="Cancel" class="btn second-btn" />
                        <input type="submit" value="Submit" class="btn main-btn" name="carSubmit" id="carSubmit" />
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
