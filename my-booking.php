<?php
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/booking-management.php';
    yamu_start_session();
yamu_require_user_roles(['customer'], 'signin.php', ['active', 'verified'], 'access-denied.php');
    $page_title = "My Bookings"; 
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
                <?php
                    $currentAccountPage = 'booking';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3> My Booking</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Bookings No.</th>
                                <th>Service</th>
                                <th>Provider</th>
                                <th>Start Date</th>
                                <th>To Date</th>
                                <th>Amount (Rs.)</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                        <?php
                            $sql = "SELECT b.*, COALESCE(v.vehicle_title, 'Driver Service') AS service_name, d.full_name AS driver_name,
                                           (SELECT MAX(payment_id) FROM payments p WHERE p.booking_id = b.booking_id) AS latest_payment_id,
                                           (SELECT COUNT(*) FROM reviews r WHERE r.booking_id = b.booking_id AND r.customer_id = b.user_ID) AS review_count,
                                           (SELECT COUNT(*) FROM complaints c WHERE c.booking_id = b.booking_id AND c.complainant_user_id = b.user_ID) AS dispute_count
                                    FROM booking b
                                    LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_ID
                                    LEFT JOIN users d ON d.user_id = b.driver_id
                                    WHERE b.user_ID = '" . (int) $_SESSION['user']['user_ID'] . "'
                                    ORDER BY b.created_at DESC, b.booking_id DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                // output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    $status = yamu_booking_normalize_status($row['booking_status'] ?? 'pending');
                                    ?>
                                    <tr>
                                        <td><?php echo $row['booking_No']; ?></td>
                                        <td><?php echo yamu_e($row['service_name']); ?></td>
                                        <td><?php echo yamu_e($row['driver_name'] ?: 'Provider'); ?></td>
                                        <td><?php echo $row['start_Data']; ?></td>
                                        <td><?php echo $row['end_Date']; ?></td>
                                        <td>
                                            <?php echo yamu_money($row['total']); ?><br>
                                            <small>Final: <?php echo yamu_money($row['final_amount'] ?: $row['total']); ?></small>
                                        </td>
                                        <td>
                                            <span class="<?php echo yamu_e(yamu_badge_class($row['payment_status'])); ?>">
                                                <?php echo yamu_e(ucfirst($row['payment_status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="<?php echo yamu_e(yamu_badge_class($status)); ?>">
                                                <?php echo yamu_e(ucfirst($status)); ?>
                                            </span>
                                        </td>
                                        <td class="action-cell">
                                            <div class="table-actions">
                                                <?php if (in_array($status, ['pending', 'confirmed'], true)) { ?>
                                                    <a href="includes/booking-process.php?cancelBooking=<?php echo $row['booking_id']; ?>" class="del-badge" title="Cancel"><i class="ri-close-fill"></i></a>
                                                <?php } ?>
                                                <?php if (yamu_booking_normalize_payment_status($row['payment_status']) !== 'paid' && in_array($status, ['pending', 'confirmed'], true)) { ?>
                                                    <a href="booking-payment.php?booking_id=<?php echo $row['booking_id']; ?>" class="edit-badge" title="Pay"><i class="ri-bank-card-line"></i></a>
                                                <?php } ?>
                                                <?php if ((int) ($row['latest_payment_id'] ?? 0) > 0 && yamu_booking_normalize_payment_status($row['payment_status']) === 'paid') { ?>
                                                    <a href="invoice.php?payment_id=<?php echo (int) $row['latest_payment_id']; ?>" class="edit-badge" title="Invoice"><i class="ri-file-text-line"></i></a>
                                                <?php } ?>
                                                <a href="booking-dispute.php?booking_id=<?php echo $row['booking_id']; ?>" class="edit-badge" title="Dispute"><i class="ri-chat-3-line"></i></a>
                                                <?php if ($status === 'completed' && (int) ($row['review_count'] ?? 0) === 0) { ?>
                                                    <a href="booking-review.php?booking_id=<?php echo $row['booking_id']; ?>" class="edit-badge" title="Review"><i class="ri-star-line"></i></a>
                                                <?php } elseif ((int) ($row['review_count'] ?? 0) > 0) { ?>
                                                    <span class="Status-conpleted-badge">Reviewed</span>
                                                <?php } ?>
                                            </div>
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
                    <img src="assets/images/logo/logo-full.png" alt="Yamu logo" class="footer-logo-img">
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



