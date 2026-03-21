<?php
include 'config.php'; // Database Connection

// Add new vehicle ================================================================================
if (isset($_POST['carSubmit'])) {
    // Get the form data
    $vehicleTitle = $_POST['vehicleTitle'];
    $vehicleDesc = $_POST['vehicleDesc'];
    $vehicleBrand = $_POST['vehicleBrand'];
    $transmission = $_POST['transmission'];
    $fuelType = $_POST['fuelType'];
    $modelYear = $_POST['modelYear'];
    $engineCap = $_POST['engineCap'];
    $capacity = $_POST['capacity'];
    $price = $_POST['price'];

    // Checkbox values
    $airConditioner = isset($_POST['airConditioner']) ? $_POST['airConditioner'] : 0;
    $powerdoorlocks = isset($_POST['powerdoorLocks']) ? $_POST['powerdoorLocks'] : 0;
    $antilockbrakingsys = isset($_POST['antiLockBrakingSystem']) ? $_POST['antiLockBrakingSystem'] : 0;
    $brakeassist = isset($_POST['brakeAssist']) ? $_POST['brakeAssist'] : 0;
    $powersteering = isset($_POST['powerSteering']) ? $_POST['powerSteering'] : 0;
    $driverairbag = isset($_POST['driverAirbag']) ? $_POST['driverAirbag'] : 0;
    $passengerairbag = isset($_POST['passengerAirbag']) ? $_POST['passengerAirbag'] : 0;
    $powerwindow = isset($_POST['powerWindows']) ? $_POST['powerWindows'] : 0;
    $cdplayer = isset($_POST['CDPlayer']) ? $_POST['CDPlayer'] : 0;

    // File upload
    $targetDirectory = "../assets/images/uploads/vehicles/";

    $vimage1 = $_FILES["vehicleImg1"]["name"];
    $vimage2 = $_FILES["vehicleImg2"]["name"];
    $vimage3 = $_FILES["vehicleImg3"]["name"];
    $vimage4 = $_FILES["vehicleImg4"]["name"];

    $vimage1Tmp = $_FILES['vehicleImg1']['tmp_name'];
    $vimage2Tmp = $_FILES['vehicleImg2']['tmp_name'];
    $vimage3Tmp = $_FILES['vehicleImg3']['tmp_name'];
    $vimage4Tmp = $_FILES['vehicleImg4']['tmp_name'];

    // Set the target directory for uploading the brand logo
    $targetDirectory = "../assets/images/uploads/vehicles/";  

    // Generate a unique filename for the brand logo
    $vehicleFileName1 = uniqid() . '_' . $vimage1;
    $vehicleFileName2 = uniqid() . '_' . $vimage2;
    $vehicleFileName3 = uniqid() . '_' . $vimage3;
    $vehicleFileName4 = uniqid() . '_' . $vimage4;

    // Set the target path for the brand logo
    $targetFilePath1 = $targetDirectory . $vehicleFileName1; 
    $targetFilePath2 = $targetDirectory . $vehicleFileName2;
    $targetFilePath3 = $targetDirectory . $vehicleFileName3;
    $targetFilePath4 = $targetDirectory . $vehicleFileName4;

    move_uploaded_file($vimage1Tmp, $targetFilePath1); 
    move_uploaded_file($vimage2Tmp, $targetFilePath2);
    move_uploaded_file($vimage3Tmp, $targetFilePath3);
    move_uploaded_file($vimage4Tmp, $targetFilePath4);

    // Prepare and execute the SQL query
    $sql = "INSERT INTO `vehicles`(`vehicle_title`, `vehicle_brand`, `vehicle_desc`, `price`, `transmission`, `fuel_type`, `year`, `engine_capacity`, `capacity`, `reg_date`, `airConditioner`, `powerdoorlocks`, `antilockbrakingsys`, `brakeassist`, `powersteering`, `driverairbag`, `passengerairbag`, `powerwindow`, `cdplayer`, `vImg1`, `vImg2`, `vImg3`, `vImg4`)
            VALUES (
                '$vehicleTitle',
                '$vehicleBrand',
                '$vehicleDesc',
                '$price',
                '$transmission',
                '$fuelType',
                '$modelYear',
                '$engineCap',
                '$capacity',
                NOW(),
                '$airConditioner',
                '$powerdoorlocks',
                '$antilockbrakingsys',
                '$brakeassist',
                '$powersteering',
                '$driverairbag',
                '$passengerairbag',
                '$powerwindow',
                '$cdplayer',
                '$vehicleFileName1',
                '$vehicleFileName2',
                '$vehicleFileName3',
                '$vehicleFileName4'
            )";

    if ($conn->query($sql) === TRUE) {
        $msg = "Vehicle added successfully.";
        header('Location: ../vehicle.php?msg=' . urlencode($msg));
        exit();
    } else {
        $error = "Error adding Vehicle: " . $conn->error;
        header('Location: ../vehicle.php?error=' . urlencode($error));
        exit();
    }

} elseif (isset($_POST['updateVehicle'])) {
    // Get the form data
    $vehicleTitle = $_POST['vehicleTitle'];
    $vehicleDesc = $_POST['vehicleDesc'];
    $vehicleBrand = $_POST['vehicleBrand'];
    $transmission = $_POST['transmission'];
    $fuelType = $_POST['fuelType'];
    $modelYear = $_POST['modelYear'];
    $engineCap = $_POST['engineCap'];
    $capacity = $_POST['capacity'];
    $price = $_POST['price'];
    $vehicleId = $_POST['vehicleId']; // Assuming you have a hidden input field for the vehicle ID
    $vehicleStatus = $_POST['vehicleStatus'];

    // Checkbox values
    $airConditioner = isset($_POST['airConditioner']) ? $_POST['airConditioner'] : 0;
    $powerdoorlocks = isset($_POST['powerdoorLocks']) ? $_POST['powerdoorLocks'] : 0;
    $antilockbrakingsys = isset($_POST['antiLockBrakingSystem']) ? $_POST['antiLockBrakingSystem'] : 0;
    $brakeassist = isset($_POST['brakeAssist']) ? $_POST['brakeAssist'] : 0;
    $powersteering = isset($_POST['powerSteering']) ? $_POST['powerSteering'] : 0;
    $driverairbag = isset($_POST['driverAirbag']) ? $_POST['driverAirbag'] : 0;
    $passengerairbag = isset($_POST['passengerAirbag']) ? $_POST['passengerAirbag'] : 0;
    $powerwindow = isset($_POST['powerWindows']) ? $_POST['powerWindows'] : 0;
    $cdplayer = isset($_POST['CDPlayer']) ? $_POST['CDPlayer'] : 0;

    // Prepare and execute the SQL query
    $sql = "UPDATE `vehicles` SET 
                `vehicle_title` = '$vehicleTitle',
                `vehicle_brand` = '$vehicleBrand',
                `vehicle_desc` = '$vehicleDesc',
                `price` = '$price',
                `transmission` = '$transmission',
                `fuel_type` = '$fuelType',
                `year` = '$modelYear',
                `engine_capacity` = '$engineCap',
                `capacity` = '$capacity',
                `reg_date` = NOW(),
                `airConditioner` = '$airConditioner',
                `powerdoorlocks` = '$powerdoorlocks',
                `antilockbrakingsys` = '$antilockbrakingsys',
                `brakeassist` = '$brakeassist',
                `powersteering` = '$powersteering',
                `driverairbag` = '$driverairbag',
                `passengerairbag` = '$passengerairbag',
                `powerwindow` = '$powerwindow',
                `cdplayer` = '$cdplayer',
                `vehicle_status` = '$vehicleStatus'
            WHERE `vehicle_id` = '$vehicleId'";

    if ($conn->query($sql) === TRUE) {
        $msg = "Vehicle updated successfully.";
        header('Location: ../vehicle.php?msg=' . urlencode($msg));
        exit();
    } else {
        $error = "Error updating Vehicle: " . $conn->error;
        header('Location: ../vehicle.php?error=' . urlencode($error));
        exit();
    }
} elseif (isset($_GET['vehicle_id'])) { // Delete vehicle ================================================================================
    $vehicleID = $_GET['vehicle_id'];

    // Perform the delete operation
    $sql = "DELETE FROM vehicles WHERE vehicle_id = $vehicleID";
    if ($conn->query($sql) === TRUE) {
        $msg = "Vehicle deleted successfully.";
        header("Location: ../Vehicle.php?msg=$msg"); 
        exit();
    } else {
        $error = "Error deleting Vehicle: " . $conn->error;
        header("Location: ../Vehicle.php?error=$error"); 
    }
}

?>