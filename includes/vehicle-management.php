<?php
require_once __DIR__ . '/auth.php';

function yamu_vehicle_feature_columns()
{
    return [
        'airConditioner' => 'airConditioner',
        'powerdoorlocks' => 'powerdoorLocks',
        'antilockbrakingsys' => 'antiLockBrakingSystem',
        'brakeassist' => 'brakeAssist',
        'powersteering' => 'powerSteering',
        'driverairbag' => 'driverAirbag',
        'passengerairbag' => 'passengerAirbag',
        'powerwindow' => 'powerWindows',
        'cdplayer' => 'CDPlayer',
    ];
}

function yamu_vehicle_normalize_listing_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowed = ['pending', 'approved', 'rejected', 'inactive'];
    return in_array($status, $allowed, true) ? $status : 'pending';
}

function yamu_vehicle_normalize_availability_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowed = ['available', 'booked', 'unavailable'];
    return in_array($status, $allowed, true) ? $status : 'available';
}

function yamu_vehicle_normalize_maintenance_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowed = ['good', 'due soon', 'under maintenance', 'unavailable'];
    return in_array($status, $allowed, true) ? $status : 'good';
}

function yamu_vehicle_fetch($conn, $vehicleId)
{
    $vehicleId = (int) $vehicleId;
    $sql = "SELECT * FROM vehicles WHERE vehicle_id = {$vehicleId} LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }

    return null;
}

function yamu_vehicle_store_image($fieldName, $existingFile = '')
{
    if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
        return $existingFile;
    }

    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $originalName = basename($_FILES[$fieldName]['name']);
    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '', $originalName);
    $newName = uniqid() . '_' . $safeName;
    $targetDirectory = __DIR__ . '/../admin/assets/images/uploads/vehicles/';

    if (!is_dir($targetDirectory)) {
        mkdir($targetDirectory, 0777, true);
    }

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetDirectory . $newName)) {
        return false;
    }

    return $newName;
}

function yamu_vehicle_collect_payload($conn, $ownerUserId, $submittedByRole = 'driver', $existingVehicle = null)
{
    $vehicleTitle = yamu_escape($conn, $_POST['vehicleTitle'] ?? '');
    $vehicleDesc = yamu_escape($conn, $_POST['vehicleDesc'] ?? '');
    $vehicleBrand = yamu_escape($conn, $_POST['vehicleBrand'] ?? '');
    $transmission = yamu_escape($conn, $_POST['transmission'] ?? '');
    $fuelType = yamu_escape($conn, $_POST['fuelType'] ?? '');
    $modelYear = (int) ($_POST['modelYear'] ?? 0);
    $engineCap = yamu_escape($conn, $_POST['engineCap'] ?? '');
    $capacity = (int) ($_POST['capacity'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $location = yamu_escape($conn, $_POST['location'] ?? '');
    $registrationNumber = yamu_escape($conn, $_POST['registration_number'] ?? '');
    $listingStatus = yamu_vehicle_normalize_listing_status($_POST['listing_status'] ?? ($existingVehicle['listing_status'] ?? (yamu_is_admin_panel_role($submittedByRole) ? 'approved' : 'pending')));
    $availabilityStatus = yamu_vehicle_normalize_availability_status($_POST['availability_status'] ?? ($existingVehicle['availability_status'] ?? 'available'));
    $maintenanceStatus = yamu_vehicle_normalize_maintenance_status($_POST['maintenance_status'] ?? ($existingVehicle['maintenance_status'] ?? 'good'));
    $serviceDate = trim((string) ($_POST['service_date'] ?? ''));
    $nextServiceDate = trim((string) ($_POST['next_service_date'] ?? ''));
    $serviceNotes = yamu_escape($conn, $_POST['service_notes'] ?? '');
    $serviceCost = trim((string) ($_POST['service_cost'] ?? ''));
    $serviceCost = $serviceCost === '' ? null : (float) $serviceCost;

    if (!yamu_is_admin_panel_role($submittedByRole) && $listingStatus === 'approved') {
        $listingStatus = 'pending';
    }

    if ($maintenanceStatus === 'under maintenance' || $maintenanceStatus === 'unavailable') {
        $availabilityStatus = 'unavailable';
    }

    $vehicleStatus = $listingStatus === 'approved' ? 1 : 0;

    $images = [];
    $imageFields = ['vehicleImg1', 'vehicleImg2', 'vehicleImg3', 'vehicleImg4'];
    $existingImages = [
        'vehicleImg1' => $existingVehicle['vImg1'] ?? '',
        'vehicleImg2' => $existingVehicle['vImg2'] ?? '',
        'vehicleImg3' => $existingVehicle['vImg3'] ?? '',
        'vehicleImg4' => $existingVehicle['vImg4'] ?? '',
    ];

    foreach ($imageFields as $fieldName) {
        $storedName = yamu_vehicle_store_image($fieldName, $existingImages[$fieldName]);

        if ($storedName === false) {
            return [false, 'Failed to upload vehicle images'];
        }

        $images[$fieldName] = $storedName;
    }

    foreach ($images as $fieldName => $value) {
        if ($value === '') {
            return [false, 'Please upload all four vehicle images'];
        }
    }

    $features = [];

    foreach (yamu_vehicle_feature_columns() as $columnName => $fieldName) {
        $features[$columnName] = isset($_POST[$fieldName]) ? 1 : 0;
    }

    if ($vehicleTitle === '' || $vehicleBrand === '' || $transmission === '' || $fuelType === '' || $location === '' || $registrationNumber === '') {
        return [false, 'Please complete all required vehicle fields'];
    }

    return [[
        'owner_user_id' => (int) $ownerUserId,
        'vehicle_title' => $vehicleTitle,
        'vehicle_desc' => $vehicleDesc,
        'vehicle_brand' => $vehicleBrand,
        'transmission' => $transmission,
        'fuel_type' => $fuelType,
        'year' => $modelYear,
        'engine_capacity' => $engineCap,
        'capacity' => $capacity,
        'price' => $price,
        'location' => $location,
        'registration_number' => $registrationNumber,
        'listing_status' => $listingStatus,
        'availability_status' => $availabilityStatus,
        'maintenance_status' => $maintenanceStatus,
        'service_date' => $serviceDate !== '' ? "'" . yamu_escape($conn, $serviceDate) . "'" : 'NULL',
        'next_service_date' => $nextServiceDate !== '' ? "'" . yamu_escape($conn, $nextServiceDate) . "'" : 'NULL',
        'service_notes' => $serviceNotes,
        'service_cost' => $serviceCost === null ? 'NULL' : $serviceCost,
        'vehicle_status' => $vehicleStatus,
        'approved_by' => yamu_is_admin_panel_role($submittedByRole) && $listingStatus === 'approved'
            ? (int) ($_SESSION['admin']['user_id'] ?? $ownerUserId)
            : (($existingVehicle && !empty($existingVehicle['approved_by'])) ? (int) $existingVehicle['approved_by'] : 'NULL'),
        'approved_at' => yamu_is_admin_panel_role($submittedByRole) && $listingStatus === 'approved'
            ? 'NOW()'
            : (($existingVehicle && !empty($existingVehicle['approved_at'])) ? "'" . yamu_escape($conn, $existingVehicle['approved_at']) . "'" : 'NULL'),
        'images' => $images,
        'features' => $features,
    ], null];
}

function yamu_vehicle_save($conn, array $payload, $vehicleId = null)
{
    $features = $payload['features'];
    $images = $payload['images'];

    $sqlValues = [
        "`owner_user_id` = " . (int) $payload['owner_user_id'],
        "`vehicle_title` = '" . $payload['vehicle_title'] . "'",
        "`vehicle_brand` = '" . $payload['vehicle_brand'] . "'",
        "`vehicle_desc` = '" . $payload['vehicle_desc'] . "'",
        "`price` = " . $payload['price'],
        "`transmission` = '" . $payload['transmission'] . "'",
        "`fuel_type` = '" . $payload['fuel_type'] . "'",
        "`year` = " . (int) $payload['year'],
        "`engine_capacity` = '" . $payload['engine_capacity'] . "'",
        "`capacity` = " . (int) $payload['capacity'],
        "`location` = '" . $payload['location'] . "'",
        "`registration_number` = '" . $payload['registration_number'] . "'",
        "`airConditioner` = " . (int) $features['airConditioner'],
        "`powerdoorlocks` = " . (int) $features['powerdoorlocks'],
        "`antilockbrakingsys` = " . (int) $features['antilockbrakingsys'],
        "`brakeassist` = " . (int) $features['brakeassist'],
        "`powersteering` = " . (int) $features['powersteering'],
        "`driverairbag` = " . (int) $features['driverairbag'],
        "`passengerairbag` = " . (int) $features['passengerairbag'],
        "`powerwindow` = " . (int) $features['powerwindow'],
        "`cdplayer` = " . (int) $features['cdplayer'],
        "`vImg1` = '" . yamu_escape($conn, $images['vehicleImg1']) . "'",
        "`vImg2` = '" . yamu_escape($conn, $images['vehicleImg2']) . "'",
        "`vImg3` = '" . yamu_escape($conn, $images['vehicleImg3']) . "'",
        "`vImg4` = '" . yamu_escape($conn, $images['vehicleImg4']) . "'",
        "`vehicle_status` = " . (int) $payload['vehicle_status'],
        "`listing_status` = '" . yamu_escape($conn, $payload['listing_status']) . "'",
        "`availability_status` = '" . yamu_escape($conn, $payload['availability_status']) . "'",
        "`maintenance_status` = '" . yamu_escape($conn, $payload['maintenance_status']) . "'",
        "`service_date` = " . $payload['service_date'],
        "`next_service_date` = " . $payload['next_service_date'],
        "`service_notes` = '" . $payload['service_notes'] . "'",
        "`service_cost` = " . $payload['service_cost'],
        "`approved_by` = " . $payload['approved_by'],
        "`approved_at` = " . $payload['approved_at'],
        "`updated_at` = NOW()",
    ];

    if ($vehicleId === null) {
        $sqlValues[] = "`reg_date` = NOW()";
        $sql = "INSERT INTO `vehicles` SET " . implode(", ", $sqlValues);
    } else {
        $sqlValues[] = "`updatation_date` = NOW()";
        $sql = "UPDATE `vehicles` SET " . implode(", ", $sqlValues) . " WHERE `vehicle_id` = " . (int) $vehicleId;
    }

    return mysqli_query($conn, $sql);
}
