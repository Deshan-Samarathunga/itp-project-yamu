<?php
    session_start();
    include 'config.php'; // Database Connection

    if (isset($_POST['signup'])) {

        $fullName = $_POST["fullName"];
        $email = $_POST["email"];
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Email Exists or not
        $check_email_query = "SELECT email FROM users WHERE email = '$email' LIMIT 1 ";
        $check_email_query_run = mysqli_query($conn, $check_email_query);

        if(mysqli_num_rows($check_email_query_run) > 0) {
            $error = "Email Id Already Exists";
            header("Location: ../signup.php?error=$error");
        } else {
            $query = "INSERT INTO users ( `username`, `password`, `full_name`, `email`, `profile_pic`, `rag_date`) 
                      VALUES ('$username', MD5('$password'),'$fullName','$email', 'avatar.png' , NOW()) ";
            $resalt = mysqli_query($conn,  $query);
    
            if($resalt) {

                $sql_read = "SELECT * FROM users AS u WHERE u.email = '$email' ";
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

                // Authentication successful
                // $_SESSION['email'] = $email;
                $_SESSION['authenticated'] = true; // The session variable to indicate authenticatio

                $msg = "Registration Successful";
                header("Location: ../index.php?msg=$msg");
            }else {
                $error = "Registration Failed";
                header("Location: ../signup.php?error=$error");
            }
        }       
    }
?>