<?php
    include 'includes/config.php'; // Database Connection
    $page_title = "Users"; 
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
            <h2>Registered Users</h2>
            <div class="main-cards">
                <div class="card">
                    <h3>Users</h3>
                    <div class="card-title">
                        <div class="search-box">
                            <input type="text" id="myInput" onkeyup="seacrFunction()" placeholder="Search...">
                        </div>
                        <!-- <a href="vehicle-add.html" class="btn main-btn">Add New +</a> -->
                    </div>
                    <table id="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Avatar</th>
                                <th>Email</th>
                                <th>Contact No</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>DOB</th>
                                <th>Reg Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                        <?php
                            $sql = "SELECT * FROM users";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                // output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['user_id']; ?></td>
                                        <td><?php echo $row['full_name']; ?></td>
                                        <td><img src="../assets/images/uploads/avatar/<?php echo $row['profile_pic']; ?>" alt="avatar" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50px"></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['phone']; ?></td>
                                        <td><?php echo $row['address']; ?></td>
                                        <td><?php echo $row['city']; ?></td>
                                        <td><?php echo $row['dob']; ?></td>
                                        <td><?php echo $row['rag_date']; ?></td>
                                        <td>
                                            <a class="del-badge" title="Delete" href="includes/deleteRecord.php?user_id=<?php echo $row['user_id']; ?>"><i class="ri-delete-bin-7-fill"></i></a>
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