<?php
session_start();
include 'config.php'; // Database Connection

if (isset($_POST['UpdatePassword'])) {
    $user_id = $_POST['userID'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST["confirm_password"];

    // Retrieve the current password from the database
    $query = "SELECT password FROM Users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $currentPasswordFromDatabase = $row['password'];

        // Verify if the current password matches
        if (md5($currentPassword) === $currentPasswordFromDatabase) {

            // Check if the new password and confirm password match
            if ($newPassword === $confirmPassword) {

                // Update the password in the database
                $sql_password = "UPDATE Users SET password = md5('$newPassword') WHERE user_id = '$user_id' ";
                $sql_password_run = mysqli_query($conn, $sql_password);

                if ($sql_password_run) {
                    $msg = "Password Updated Successfully";
                    header("Location: ../update-password.php?msg=$msg");
                    exit();
                } else {
                    $error = "Password Update failed";
                    header("Location: ../update-password.php?error=$error");
                    exit();
                }

            } else {
                $error = "Password confirmation failed. Please try again";
                header("Location: ../update-password.php?error=$error");
                exit();
            }
        } else {
            $error = "Invalid current password. Please try again";
            header("Location: ../update-password.php?error=$error");
            exit();
        }
    } else {
        $error = "User not found";
        header("Location: ../update-password.php?error=$error");
        exit();
    }
}
?>
