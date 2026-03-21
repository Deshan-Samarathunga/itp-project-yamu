<?php
session_start();
include 'config.php'; // Database Connection

if(isset($_POST['updateProfile'])) {
    $user_id = $_POST['userID'];
    $fullName = $_POST['fullName'];
    $email = $_POST["email"];
    $username = $_POST["username"];
    $tel = $_POST["phone"];
    $dob = $_POST["dob"];
    $address = $_POST["address"];
    $city = $_POST["city"];

    // Profile Image Upload
    $profileImageName = $_FILES['profileImage']['name'];
    $profileImageTmp = $_FILES['profileImage']['tmp_name'];

    // Check if a profile image was uploaded
    if($profileImageName) {
        $profileImageNewName = uniqid() . '_' . $profileImageName;
        $profileImageDestination = '../assets/images/uploads/avatar/'.$profileImageNewName;

        // Move the uploaded file to the desired destination
        if(move_uploaded_file($profileImageTmp, $profileImageDestination)) {
            // Update the user's profile image in the database
            $sql = "UPDATE users 
                        SET username = '$username',
                            full_name = '$fullName',
                            email = '$email',
                            address = '$address',
                            city = '$city',
                            phone = '$tel',
                            dob = '$dob',
                            profile_pic = '$profileImageNewName'
                        WHERE user_id = '$user_id';";
            $sql_run = mysqli_query($conn, $sql);

            if ($sql_run) {
                $sql_read = "SELECT * FROM users AS u WHERE u.user_id = '$user_id' ";
                $sql_read_run = mysqli_query($conn, $sql_read);

                while ($row = mysqli_fetch_assoc($sql_read_run)) {
                    $array = [
                        'user_ID' => $row['user_id'],
                        'username' => $row['username'],
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
            } 

            $msg = "Profile Updated Successfully";
            header("Location: ../profile.php?msg=$msg");
            exit();
        } else {
            $msg = "Failed to move the uploaded file.";
        }
    } else {
        // If no profile image was uploaded, update the user's profile without changing the image
        $sql = "UPDATE users 
        SET username = '$username',
            full_name = '$fullName',
            email = '$email',
            address = '$address',
            city = '$city',
            phone = '$tel',
            dob = '$dob'
        WHERE user_id = '$user_id';";
        $sql_run = mysqli_query($conn, $sql);
        
        if ($sql_run) {
            $sql_read = "SELECT * FROM users AS u WHERE u.user_id = '$user_id' ";
            $sql_read_run = mysqli_query($conn, $sql_read);

            while ($row = mysqli_fetch_assoc($sql_read_run)) {
                $array = [
                    'user_ID' => $row['user_id'],
                    'username' => $row['username'],
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
        }

        $msg = "Profile Updated Successfully";
        header("Location: ../profile.php?msg=$msg");
        exit();
    }

    // If an error occurred, redirect back to the profile page with an error message
    header("Location: ../profile.php?error=$msg");
    exit();
}
?>
