<?php
$page_title = "Booking";
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

    <div class="grid-container">
        <!-- Navbar -->
        <?php
        include('includes/menu.php');
        ?>

        <!-- Aside Section -->
        <?php
        include('includes/aside.php');
        ?>

        <main class="main">
            <h2>Booking Details</h2>

            <?php
            $bookingID = $_GET['bookingID'];

            if ($bookingID) {
                $sql = " SELECT * FROM booking AS b, vehicles AS v, users AS u WHERE u.user_id=b.user_ID AND b.vehicle_ID=v.vehicle_id;";
                $result = mysqli_query($conn, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    $record = mysqli_fetch_assoc($result);

                    ?>
                    <div class="main-cards">
                        <div class="card">
                            <div class="card-title">
                                <?php echo $record['booking_No']; ?> Booking Details
                            </div>
                            <table>
                                <h4 class="table-title">User Details</h4>
                                <tr>
                                    <th>Booking No.</th>
                                    <td><?php echo $record['booking_No']; ?></td>
                                    <th>Name</th>
                                    <td><?php echo $record['full_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?php echo $record['email']; ?></td>
                                    <th>Contact No</th>
                                    <td><?php echo $record['phone']; ?></td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td><?php echo $record['address']; ?></td>
                                    <th>City</th>
                                    <td><?php echo $record['city']; ?></td>
                                </tr>
                            </table>
                            <table>
                                <br><br>
                                <h4 class="table-title">Booking Details</h4>
                                <tr>
                                    <th>Vehicle Name</th>
                                    <td><?php echo $record['vehicle_title']; ?></td>
                                    <th>Booking Date</th>
                                    <td><?php echo $record['booking_Date']; ?></td>
                                </tr>
                                <tr>
                                    <th>From Date</th>
                                    <td><?php echo $record['start_Data']; ?></td>
                                    <th>To Date</th>
                                    <td><?php echo $record['end_Date']; ?></td>
                                </tr>
                                <tr>
                                    <th>Total Days</th>
                                    <td><?php echo $record['total']; ?></td>
                                    <th>Rent Per Day</th>
                                    <td><?php echo $record['price']; ?></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <th>Grand Total</th>
                                    <td><?php echo $record['total']; ?></td>
                                </tr>
                                <tr>
                                    <th>Booking Status</th>
                                    <td>
                                        <?php
                                            if($record['status'] == '1') {
                                                echo "Confirmed";
                                            } else {
                                                echo "Cancelled";
                                            }
                                        ?>
                                    </td>
                                    <th>Last Update Date</th>
                                    <td></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2023 EM</div>
            <div class="footer__signature">Made with love by pure genius</div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
</body>

</html>