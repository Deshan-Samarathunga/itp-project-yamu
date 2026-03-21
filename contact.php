<?php
    $page_title = "Contact"; 
    session_start(); // Start the session
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

     <!-- Page Banner Section -->
     <section class="banner-page">
        <h2>Contact Us</h2>
        <div class="banner-link">
                <a href="index.php">Home</a> &gt; <a href="contact.php">Contact Us</a>
         </div>
    </section>
 
    <!-- Contact Us -->
    <section class="contact">
        <div class="container">
            <div class="row">
                <div class="col-contact1">

                    <form>
                        <h2>Get in Touch with Us</h2>
                        
                        <label for="f_name" name="FullName">Full Name<span>*</span></label>
                        <input type="Full_Name" placeholder="Enter Your Full Name" id="f_name" required> 
                
                        <label for="e_mail" name="EMail">E-Mail<span>*</span></label>
                        <input type="E-mail" placeholder="Enter Your E-mail" id="e_mail" required>
                    
                        <label for="p_number" name="phoneNumber">Phone Number<span>*</span></label>
                        <input type="Phone_number" placeholder="Enter Your phone Number" id="p_number" required>
                    
                        <label for="message" name="message">Message<span>*</span></label>  
                        <textarea row="10" cols="50" required></textarea>

                        <button type="sumbmit" class="btn main-btn" id="send">Send Message</button>
                
                    </form>

                </div>
                <div class="col-contact2">
                    <h2>Contact Info</h2>
                    <table>
                        <tr>
                            <td><img width="50px" src="assets/images/location.png" class="location1"> 
                            </td>
                            <td>Location :<br> Dehiwala, Colombo.</td>
                        </tr>
                        <tr>
                            <td><img width="50px" src="assets/images/phone.png" class="phone1">
                            </td>
                            <td>Contact Number :<br> 964-622-3903</td>
                        </tr>
                        <tr>
                            <td><img width="50px" src="assets/images/mail.png" class="mail"></td>
                            <td>E-mail :<br> carzo@contact.com</td>
                        </tr>
                </table>
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14236.05504441497!2d79.86969128404569!3d6.834866173546113!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae25a8d6ec1f8c3%3A0x47810f3e7d084753!2sDehiwala-Mount%20Lavinia!5e0!3m2!1sen!2slk!4v1685131915124!5m2!1sen!2slk" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>


            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php
        include('includes/footer.php');
    ?>

    <script src="assets/js/main.js"></script>
</body>
</html>