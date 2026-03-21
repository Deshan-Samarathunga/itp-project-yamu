<?php
    require_once __DIR__ . '/../includes/auth.php';
    carzo_start_session();
    carzo_require_admin('index.php');
    include 'includes/config.php';
    $page_title = "Users";

    $roleFilter = carzo_normalize_role($_GET['role'] ?? '');
    $statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
    $search = trim((string) ($_GET['search'] ?? ''));
    $allowedStatuses = ['active', 'pending', 'suspended'];

    $conditions = [];

    if (!empty($_GET['role'])) {
        $conditions[] = "role = '" . carzo_escape($conn, $roleFilter) . "'";
    }

    if (in_array($statusFilter, $allowedStatuses, true)) {
        $conditions[] = "account_status = '" . carzo_escape($conn, $statusFilter) . "'";
    }

    if ($search !== '') {
        $escapedSearch = carzo_escape($conn, $search);
        $conditions[] = "(full_name LIKE '%{$escapedSearch}%' OR email LIKE '%{$escapedSearch}%' OR username LIKE '%{$escapedSearch}%')";
    }

    $sql = 'SELECT * FROM users';

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY created_at DESC, user_id DESC';
    $result = $conn->query($sql);
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

        <?php
            include('includes/aside.php');
        ?>


        <main class="main">
            <?php
                include('../includes/alert.php');
            ?>
            <h2>Registered Users</h2>
            <div class="main-cards">
                <div class="card">
                    <h3>Users</h3>
                    <div class="card-title">
                        <form action="" method="GET">
                            <div class="search-box">
                                <input type="text" name="search" value="<?php echo carzo_e($search); ?>" placeholder="Search users..." />
                            </div>
                            <select name="role" style="width: 180px;">
                                <option value="">All Roles</option>
                                <option value="customer" <?php echo (isset($_GET['role']) && $_GET['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                                <option value="driver" <?php echo (isset($_GET['role']) && $_GET['role'] === 'driver') ? 'selected' : ''; ?>>Driver</option>
                                <option value="admin" <?php echo (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <select name="status" style="width: 180px;">
                                <option value="">All Statuses</option>
                                <option value="active" <?php echo ($statusFilter === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?php echo ($statusFilter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="suspended" <?php echo ($statusFilter === 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                            </select>
                            <button type="submit" class="btn second-btn">Filter</button>
                            <a href="users.php" class="btn second-btn">Reset</a>
                            <a href="user-add.php" class="btn main-btn">Add New +</a>
                        </form>
                    </div>
                    <div class="table-wrap">
                    <table id="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Avatar</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Verification</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>License / NIC</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                        <?php
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $statusClass = carzo_badge_class($row['account_status']);
                                    $verificationClass = carzo_badge_class($row['verification_status']);
                                    ?>
                                    <tr>
                                        <td><?php echo carzo_e($row['user_id']); ?></td>
                                        <td class="image-cell"><img src="../assets/images/uploads/avatar/<?php echo carzo_e($row['profile_pic']); ?>" alt="avatar" class="table-avatar"></td>
                                        <td><?php echo carzo_e($row['full_name']); ?></td>
                                        <td><?php echo carzo_e(ucfirst($row['role'])); ?></td>
                                        <td><span class="<?php echo carzo_e($statusClass); ?>"><?php echo carzo_e(ucfirst($row['account_status'])); ?></span></td>
                                        <td><span class="<?php echo carzo_e($verificationClass); ?>"><?php echo carzo_e(ucfirst(str_replace('_', ' ', $row['verification_status']))); ?></span></td>
                                        <td><?php echo carzo_e($row['email']); ?></td>
                                        <td><?php echo carzo_e($row['phone']); ?></td>
                                        <td><?php echo carzo_e($row['license_or_nic']); ?></td>
                                        <td><?php echo carzo_e($row['created_at'] ?: $row['rag_date']); ?></td>
                                        <td class="action-cell">
                                            <div class="table-actions">
                                            <a class="edit-badge" title="Edit" href="user-edit.php?user_id=<?php echo $row['user_id']; ?>"><i class="ri-pencil-fill"></i></a>
                                            <?php if ($row['account_status'] === 'active') { ?>
                                                <a class="del-badge" title="Suspend" href="includes/user-process.php?action=suspend&user_id=<?php echo $row['user_id']; ?>"><i class="ri-pause-fill"></i></a>
                                            <?php } else { ?>
                                                <a class="edit-badge" title="Activate" href="includes/user-process.php?action=activate&user_id=<?php echo $row['user_id']; ?>"><i class="ri-play-fill"></i></a>
                                            <?php } ?>
                                            <?php if ($row['role'] === 'driver') { ?>
                                                <a class="edit-badge" title="Approve Driver" href="includes/user-process.php?action=approve-driver&user_id=<?php echo $row['user_id']; ?>"><i class="ri-check-fill"></i></a>
                                                <a class="del-badge" title="Reject Driver" href="includes/user-process.php?action=reject-driver&user_id=<?php echo $row['user_id']; ?>"><i class="ri-close-fill"></i></a>
                                            <?php } ?>
                                            <a class="del-badge" title="Delete" href="includes/user-process.php?action=delete&user_id=<?php echo $row['user_id']; ?>"><i class="ri-delete-bin-7-fill"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='11'>No users found.</td></tr>";
                            }

                        ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2023 EM</div>
            <div class="footer__signature">Made with love by pure genius</div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
