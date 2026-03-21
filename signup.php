<?php
    $page_title = "Signup";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        include('includes/header.php');
    ?>

</head>
<body>

    <?php
        include('includes/menu.php');
    ?>

        <section class="register">
            <!-- Allert Box -->
            <?php
                include('includes/alert.php');
            ?>
            <div class="container">
                <div class="signup-content">
                    <form action="includes/signup-process.php" method="POST" class="signup-form" onsubmit="return checkPassword()">
                        <h3>Create an Account</h3>
                        <p>Lets start with <span>CARZO</span></p>
                        <div class="form-group">
                            <label for="fullName">Full Name:</label>
                            <input type="text" name="fullName" id="fullName" placeholder="Enter Name" required/>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input type="email" name="email" id="email" placeholder="Enter Email Address" required/>
                        </div>
                        <div class="form-group">
                            <label for="username">Choose a Username:</label>
                            <input type="text" name="username" id="username" placeholder="Enter Username" required/>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" placeholder="Enter Password" required/>
                            <i class="ri-eye-off-line" id="toggle-eye" onclick="togglePassword()"></i>
                        </div>
                        <div class="form-group">
                            <label for="conPassword">Re-enter Password:</label>
                            <input type="password" name="conPassword" id="conPassword"placeholder="Re-enter Password" required/>
                        </div>
                        <div class="form-group-row">
                            <input type="checkbox" name="terms" id="terms" required />
                            <label for="terms"> 
                                <p>I accept the <a href="#">Terms of Services</a></p>
                            </label>
                        </div>
                        <div class="form-submit">
                            <input type="submit" value="Sign Up" class="btn main-btn" name="signup" id="submit" />
                        </div>
                    </form>
                    <p>Already have an account? <a href="signin.php">Sign In</a></p>
                    <hr>
                    <p>Sign in with Social Media Accounts</p>
                    <div class="social-btn">
                        <a href="#" class="btn">
                            <img src="assets/images/google.png" alt="">
                            Sign in with Google
                        </a>
                        <a href="#" class="btn">
                            <img src="assets/images/fb.png" alt="">
                            Continue with Facebook
                        </a>
                    </div>
                </div>
            </div>
        </section>    

    <!-- Footer Section -->
    <?php
        include('includes/footer.php');
    ?>

    <script>
        // Show Password
        function togglePassword() {
            const password = document.getElementById("password");
            const inputIcon = document.getElementById("toggle-eye");

            if (password.type === "password") {
                password.type = "text";
                inputIcon.classList.remove('ri-eye-off-line');
                inputIcon.classList.add('ri-eye-line');
            } else {
                password.type = "password";
                inputIcon.classList.add('ri-eye-off-line');
                inputIcon.classList.remove('ri-eye-line');
            }
        }

        // Check Password
        function checkPassword() {
            const password = document.getElementById("password").value;
            const rePassword = document.getElementById("conPassword").value;

            if(password != rePassword) {
                alert("Password Mismatch!");
                return false;
            } else {
                alert("Success!");
                return true;
            }
        }
            
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>