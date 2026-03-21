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
            <!-- Allert Box -->
            <?php
                include('../includes/alert.php');
            ?>
            <h2>New Bookings</h2>
            <div class="main-cards">
                <div class="card">
                    <h3>Bookings Info</h3>
                    <div class="card-title">
                        <div class="search-box">
                            <input type="text" id="myInput" onkeyup="seacrFunction()" placeholder="Search...">
                        </div>
                    </div>
                    <table id="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Bookings No.</th>
                                <th>Vehicle ID</th>
                                <th>User ID</th>
                                <th>Start Date</th>
                                <th>To Date</th>
                                <th>Total Price (Rs.)</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php
                            $sql = "SELECT * FROM booking";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                // output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['booking_id']; ?></td>
                                        <td><?php echo $row['booking_No']; ?></td>
                                        <td><?php echo $row['vehicle_ID']; ?></td>
                                        <td><?php echo $row['user_ID']; ?></td>
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
                                        <td>
                                            <a href="includes/booking-process.php?activeBooking=<?php echo $row['booking_id']; ?>" class="edit-badge" title="confirm"><i class="ri-check-fill"></i></a>
                                            <a href="includes/booking-process.php?deactiveBooking=<?php echo $row['booking_id']; ?>" class="del-badge" title="confirm"><i class="ri-close-fill"></i></a>
                                            <a href="includes/booking-process.php?deleteBooking=<?php echo $row['booking_id']; ?>" class="del-badge" title="Delete"><i class="ri-delete-bin-7-fill"></i></a>
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
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2023 EM</div>
            <div class="footer__signature">Made with love by pure genius</div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Search Bar

        function seacrFunction() {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("myInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("table");
        tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[1];
                if (td) {
                txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>