<?php
include 'config.php'; // Database Connection

if (isset($_GET['activeBooking'])) { //Confirm Booking
    $bookingID = $_GET['activeBooking'];

    $sql = "UPDATE booking SET `status`= '1' WHERE booking_id = '$bookingID' ";

    if ($conn->query($sql) === TRUE) {
        $msg = "Booking Confirmed successfully.";
        header("Location: ../bookings.php?msg=$msg");
        exit();
    } else {
        $error = "Error Booking vehicle: " . $conn->error;
        header("Location: ../bookings.php?msg=$error");
    }

} elseif ((isset($_GET['deactiveBooking']))) {
    $bookingID = $_GET['deactiveBooking'];

    $sql = "UPDATE booking SET `status`= '0' WHERE booking_id = '$bookingID' ";

    if ($conn->query($sql) === TRUE) {
        $msg = "Booking Deavtivated successfully.";
        header("Location: ../bookings.php?msg=$msg");
        exit();
    } else {
        $error = "Error Booking vehicle: " . $conn->error;
        header("Location: ../bookings.php?msg=$error");
    }

} elseif (isset($_GET['deleteBooking'])) {
    $bookingID = $_GET['deleteBooking'];

    $sql = "DELETE FROM `booking` WHERE booking_id = '$bookingID'";

    if ($conn->query($sql) === TRUE) {
        $msg = "Booking Deleted successfully.";
        header("Location: ../bookings.php?msg=$msg");
        exit();
    } else {
        $error = "Error Deleted Booking: " . $conn->error;
        header("Location: ../bookings.php?msg=$error");
    }
}
?>
