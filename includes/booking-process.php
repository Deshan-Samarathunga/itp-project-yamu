<?php
include 'config.php'; // Database Connection

if (isset($_POST['booking'])) {
    // Retrieve the form data
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $userID = $_POST['userID'];
    $vehicleID = $_POST['vehicleID'];
    $priceInput = $_POST['priceInput'];

    // Generate a random Booking number
    $randomNumber = rand(10000, 99999); // Generate a random number
    $bookingNumber = 'BOOK' .  $randomNumber; // Combine the timestamp and random number to create the booking number

    $query = "INSERT INTO `booking` (`booking_No`, `user_ID`, `vehicle_ID`, `start_Data`, `end_Date`, `total`, `booking_Date`) 
              VALUES ('$bookingNumber', '$userID', '$vehicleID', '$startDate', '$endDate', '$priceInput', NOW())";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $msg = "Booking successful.";
        header('Location: ../my-booking.php?msg=' . $msg);
        exit();
    } else {
        $error = "Booking failed";
        header("Location: ../vehical-details.php?vehicle_id=$vehicleID?error=$error");
        exit();
    }
}
?>
