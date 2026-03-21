<?php
session_start();
include 'config.php'; // Database Connection

if(isset($_POST['signin'])) {
    $email = $_POST['email'];   
    $password = $_POST['password'];

    $sql = "SELECT * FROM users AS u WHERE u.email = '$email' AND u.password = MD5('$password')";
    $query = mysqli_query($conn, $sql);

    if(mysqli_num_rows($query) > 0) {
        // Process the retrieved data
        while ($row = mysqli_fetch_assoc($query)) {
            $array = [
                'user_ID' => $row['user_id'],
                'username' => $row['username'],
                'password' => $row['password'],
                'email' => $row['email'],
                'name' => $row['full_name'],
                'address' => $row['address'],
                'city' => $row['city'],
                'phone' => $row['phone'],
                'dob' => $row['dob'],
                'avatar' => $row['profile_pic'],
            ];
            $_SESSION['user'] = $array;

        }
        // Authentication successful
        $_SESSION['authenticated'] = true; // The session variable to indicate authentication
        $msg = "Signin Successful";
        header("Location: ../index.php?msg=$msg"); // Redirect to welcome page 
        exit();
    } else {
        // Authentication failed
        $error = "Invalid username or password";
        header("Location: ../signin.php?error=$error"); // Redirect to signin page 
    }
}
?>
