<?php
session_start();
include 'config.php'; // Database Connection

if(isset($_POST['signin'])) {
    $email = $_POST['email'];   
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE email = '$email' AND password = MD5('$password')";
    $query = mysqli_query($conn, $sql);

    if(mysqli_num_rows($query) > 0) {
        // Process the retrieved data
        while ($row = mysqli_fetch_assoc($query)) {
            $array = [
                'admin_id' => $row['admin_id'],
                'username' => $row['username'],
                'password' => $row['password'],
                'email' => $row['email'],
                'name' => $row['name'],
                'address' => $row['address'],
                'city' => $row['city'],
                'phone' => $row['phone'],
                'avatar' => $row['profile_pic'],
            ];
            $_SESSION['admin'] = $array;
        }

        $msg = "Signin Successful";
        header("Location: ../dashboard.php?msg=$msg"); // Redirect to dashboard  
        exit();
    } else {
        // Authentication failed
        $error = "Invalid email or password";
        header("Location: ../index.php?error=$error"); // Redirect to signin page 
    }
}
?>
