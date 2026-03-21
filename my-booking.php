<?php
    $page_title = "My Bookings"; 
    session_start(); // Start the session
    include 'includes/config.php'; // Database Connection
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
    
    <!-- Accout Dashboard -->
    <section class="profile">
        <?php
           include('includes/alert.php');
        ?>
        <div class="container">
            <div class="row">
                <div class="profile-card card sticky">
                    <div class="avatar">
                        <img src="assets/images/uploads/avatar/<?php echo $_SESSION['user']['avatar'] ?>" alt="avatar">
                    </div>
                    <h4>Monica Lucas</h4>
                    <span><?php echo $_SESSION['user']['email'] ?></span>
                    <ul class="sidenav-list">
                        <li class="sidenav-item">
                            <a href="profile.php" class="nav-links">
                                <i class="ri-user-line"></i>
                                Profile Setting
                            </a>
                        </li>
                        <li class="sidenav-item">
                            <a href="update-password.php" class="nav-links">
                                <i class="ri-lock-line"></i>
                                Change Password
                            </a>
                        </li>
                        <li class="sidenav-item">
                            <a href="my-booking.php" class="nav-links active">
                                <i class="ri-book-line"></i>
                                My Booking
                            </a>
                        </li>
                        <li class="sidenav-item">
                            <a href="profile.php" class="nav-links">
                                <i class="ri-logout-box-line"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="profile-details card">
                    <h3> My Booking</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Bookings No.</th>
                                <th>Vehicle</th>
                                <th>Start Date</th>
                                <th>To Date</th>
                                <th>Total Price (Rs.)</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                        <?php
                            $sql = "SELECT * FROM booking WHERE user_ID = '" . $_SESSION['user']['user_ID'] . "'";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                // output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['booking_id']; ?></td>
                                        <td><?php echo $row['booking_No']; ?></td>
                                        <td><?php echo $row['vehicle_ID']; ?></td>
                                        <td><?php echo $row['start_Data']; ?></td>
                                        <td><?php echo $row['end_Date']; ?></td>
                                        <td><?php echo $row['total']; ?></td>
                                        <td><?php echo $row['booking_Date']; ?></td>
                                        <td>
                                            <?php if($row['status'] === '1') {
                                                    echo "<span class='Status-conpleted-badge'>Confirmed</span>";
                                                } else {
                                                    echo "<span class='Status-pending-badge'>Pending</span>";
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "0 results";
                            }

                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="grid-4">
                <div class="footer-col">
                    <img src="assets/images/logo/logo-full-1.png" alt="logo" class="footer-logo-img">
                    <p>Lorem ipsum dolor sit amet, do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniamquis.</p>
                    <div class="social-icon">
                        <a href="#" title="facebook"> 
                            <i class="ri-facebook-fill"></i>
                        </a>
                        <a href="#" title="instagram"> 
                            <i class="ri-instagram-fill"></i>
                        </a>
                        <a href="#" title="twitter"> 
                            <i class="ri-twitter-fill"></i>
                        </a>
                        <a href="#" title="linkedin"> 
                            <i class="ri-linkedin-fill"></i>
                        </a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Quick links</h4>
                    <ul>
                        <li><a href="#">My Account</a></li>
                        <li><a href="#">Champaigns</a></li>
                        <li><a href="#">Deals and Incentive</a></li>
                        <li><a href="#">Financial Services</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>About Company</h4>
                    <ul>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Partners</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Newsletter</h4>
                    <span>Get our weekly newsletter for latest car news exclusive offers and deals and more.</span>
                    <div class="newsletter-form">
                        <form action="">
                            <input type="email" placeholder="Email Address" required>
                            <input type="submit" value="Subscribe">
                        </form>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <a href="#">Terms and conditions</a>
                <span>All Copyrights Reserved © 2023 - EM</span>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>