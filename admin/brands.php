<?php
    include 'includes/config.php'; // Database Connection
    $page_title = "Brands"; 
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
            <h2>Manage Brands</h2>

            <div class="main-cards">
                <div class="card">
                    <h3>Listed Brands</h3>
                    <div class="card-title">
                        <div class="search-box">
                            <input type="text" id="myInput" onkeyup="seacrFunction()" placeholder="Search...">
                        </div>
                        <a href="brand-add.php" class="btn main-btn">Add New +</a>
                    </div>
                    <table id="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Brand Name</th>
                                <th>Brand Logo</th>
                                <th>Creation Date</th>
                                <th>Update Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                        <?php
                            // $sql = "SELECT Item_id, Item_name, Item_description, Price, Quantity FROM item";
                            $sql = "SELECT * FROM brands";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                // output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['brand_id']; ?></td>
                                        <td><?php echo $row['brand_name']; ?></td>
                                        <td><img src="assets/images/uploads/brands/<?php echo $row['brand_logo']; ?>" alt="avatar" style="width: 60px; height: 60px; object-fit: contain;"></td>
                                        <td><?php echo $row['creation_date']; ?></td>
                                        <td><?php echo $row['update_date']; ?></td>
                                        <td>
                                            <?php if($row['brand_status'] === '1') {
                                                    echo "<span class='Status-active-badge'>Active</span>";
                                                } else {
                                                    echo "<span class='Status-pending-badge'>Deactive</span>";
                                                }
                                            ?>
                                        <td>
                                            <a href="brand-edit.php?brand_id=<?php echo $row['brand_id']; ?>" class="edit-badge" title="Edit"><i class="ri-pencil-fill"></i></a>
                                            <a class="del-badge" title="Delete" href="includes/brand-process.php?brand_id=<?php echo $row['brand_id']; ?>"><i class="ri-delete-bin-7-fill"></i></a>
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