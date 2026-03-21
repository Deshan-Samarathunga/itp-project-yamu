<?php
    include 'includes/config.php'; // Database Connection
    $page_title = "Vehicles"; 
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
            <h2>Manage Vehicles</h2>
            <div class="main-cards">
                <div class="card">
                    <h3>Listed Vehicles</h3>
                    <div class="card-title">
                        <div class="search-box">
                            <input type="text" id="myInput" onkeyup="seacrFunction()" placeholder="Search...">
                        </div>
                        <a href="vehicle-add.php" class="btn main-btn">Add New +</a>
                    </div>
                    <table id="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Vehicle Title</th>
                                <th>Brand</th>
                                <th>Price Per day (RS)</th>
                                <th>Fuel Type</th>
                                <th>Model Year</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                        <?php
                            $sql = "SELECT * FROM vehicles";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                // output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['vehicle_id']; ?></td>
                                        <td><img src="assets/images/uploads/vehicles/<?php echo $row['vImg1']; ?>" alt="avatar" style="width: 80px; height: 80px; object-fit: cover;"></td>
                                        <td><?php echo $row['vehicle_title']; ?></td>
                                        <td><?php echo $row['vehicle_brand']; ?></td>
                                        <td><?php echo $row['price']; ?></td>
                                        <td><?php echo $row['fuel_type']; ?></td>
                                        <td><?php echo $row['year']; ?></td>
                                        <td>
                                            <?php if($row['vehicle_status'] === '1') {
                                                    echo "<span class='Status-active-badge'>Active</span>";
                                                } else {
                                                    echo "<span class='Status-pending-badge'>Deactive</span>";
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="vehicle-edit.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="edit-badge" title="Edit"><i class="ri-pencil-fill"></i></a>
                                            <a href="includes/vehicle-process.php ? vehicle_id=<?php echo $row['vehicle_id']; ?>" class="del-badge" title="Delete"><i class="ri-delete-bin-7-fill"></i></a>
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
                td = tr[i].getElementsByTagName("td")[2];
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