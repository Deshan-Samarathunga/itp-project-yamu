<?php
    $page_title = "Signin";
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
                    <form action="includes/signin-process.php" method="POST" class="signup-form">
                        <h3>Welcome Back</h3>
                        <p>Please Enter your Details</p>
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input type="email" name="email" id="email" placeholder="Enter Email Address" required/>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" placeholder="Enter Password" required/>
                            <i class="ri-eye-off-line" id="toggle-eye" onclick="togglePassword()"></i>
                        </div>
                        <div class="row">
                            <div class="form-group-row">
                                <input type="checkbox" name="terms" id="terms">
                                <label for="terms"> 
                                    <p>Remember Me</p>
                                </label>
                            </div>
                            <a href="#">Forgot password?</a>
                        </div>
                        <div class="form-submit">
                            <input type="submit" value="Sign In" class="btn main-btn" name="signin" id="signin" />
                        </div>
                    </form>
                    <p>No account yet? <a href="signup.php">Sign Up</a></p>
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
            
    </script>

    <script src="assets/js/main.js"></script>
</body>
</html>