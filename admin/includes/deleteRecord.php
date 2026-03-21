<?php

include 'config.php'; // Database Connection


if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    // Perform the delete operation
    $sql = "DELETE FROM users WHERE user_id = $userId";
    if ($conn->query($sql) === TRUE) {
        $msg = "User deleted successfully.";
        header("Location: ../users.php?msg=$msg"); 
        exit();
    } else {
        $error = "Error deleting user: " . $conn->error;
        header("Location: ../users.php?msg=$error"); 
    }
}
?>

 