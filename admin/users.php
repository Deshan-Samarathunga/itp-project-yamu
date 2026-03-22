<?php
require_once __DIR__ . '/../includes/auth.php';
yamu_start_session();
yamu_require_admin('index.php', 'access-denied.php');
include 'includes/config.php';

$page_title = 'User Management';
$roleFilter = yamu_normalize_role($_GET['role'] ?? '');
$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
$search = trim((string) ($_GET['search'] ?? ''));
$allowedStatuses = ['active', 'pending', 'verified', 'suspended', 'rejected', 'deactivated'];
$hasUserRolesTable = yamu_table_exists($conn, 'user_roles');
$currentAdminUserId = (int) ($_SESSION['admin']['user_id'] ?? 0);

function yamu_admin_bind_dynamic_params($stmt, $types, array &$values)
{
    if ($types === '' || empty($values)) {
        return;
    }

    $params = [$types];

    foreach ($values as $index => $value) {
        $params[] = &$values[$index];
    }

    call_user_func_array([$stmt, 'bind_param'], $params);
}

$sql = '';
$types = '';
$params = [];

if ($hasUserRolesTable) {
    $sql = "SELECT u.*, ur_agg.assigned_roles
            FROM users u
            LEFT JOIN (
                SELECT user_id, GROUP_CONCAT(DISTINCT role_key ORDER BY FIELD(role_key, 'customer', 'driver', 'staff', 'admin') SEPARATOR ', ') AS assigned_roles
                FROM user_roles
                GROUP BY user_id
            ) ur_agg ON ur_agg.user_id = u.user_id";

    $conditions = [];

    if (!empty($_GET['role'])) {
        $conditions[] = 'EXISTS (SELECT 1 FROM user_roles urf WHERE urf.user_id = u.user_id AND urf.role_key = ?)';
        $types .= 's';
        $params[] = $roleFilter;
    }

    if (in_array($statusFilter, $allowedStatuses, true)) {
        $conditions[] = '(u.account_status = ? OR EXISTS (SELECT 1 FROM user_roles urs WHERE urs.user_id = u.user_id AND urs.role_status = ?))';
        $types .= 'ss';
        $params[] = $statusFilter;
        $params[] = $statusFilter;
    }

    if ($search !== '') {
        $conditions[] = '(u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)';
        $searchLike = '%' . $search . '%';
        $types .= 'sss';
        $params[] = $searchLike;
        $params[] = $searchLike;
        $params[] = $searchLike;
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
} else {
    $sql = 'SELECT * FROM users';
    $conditions = [];

    if (!empty($_GET['role'])) {
        $conditions[] = 'role = ?';
        $types .= 's';
        $params[] = $roleFilter;
    }

    if (in_array($statusFilter, $allowedStatuses, true)) {
        $conditions[] = 'account_status = ?';
        $types .= 's';
        $params[] = $statusFilter;
    }

    if ($search !== '') {
        $conditions[] = '(full_name LIKE ? OR email LIKE ? OR username LIKE ?)';
        $searchLike = '%' . $search . '%';
        $types .= 'sss';
        $params[] = $searchLike;
        $params[] = $searchLike;
        $params[] = $searchLike;
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
}

$sql .= ' ORDER BY created_at DESC, user_id DESC';
$stmt = $conn->prepare($sql);
$result = false;

if ($stmt) {
    yamu_admin_bind_dynamic_params($stmt, $types, $params);
    $stmt->execute();
    $result = $stmt->get_result();
}
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
            <h2>User Management</h2>
            <div class="main-cards">
                <div class="card">
                    <h3>Users</h3>
                    <div class="card-title">
                        <form action="" method="GET">
                            <div class="search-box">
                                <input type="text" name="search" value="<?php echo yamu_e($search); ?>" placeholder="Search users..." />
                            </div>
                            <select name="role" style="width: 180px;">
                                <option value="">All Roles</option>
                                <option value="customer" <?php echo (isset($_GET['role']) && $_GET['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                                <option value="driver" <?php echo (isset($_GET['role']) && $_GET['role'] === 'driver') ? 'selected' : ''; ?>>Driver</option>
                                <option value="staff" <?php echo (isset($_GET['role']) && $_GET['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                                <option value="admin" <?php echo (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <select name="status" style="width: 180px;">
                                <option value="">All Statuses</option>
                                <option value="active" <?php echo ($statusFilter === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?php echo ($statusFilter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="verified" <?php echo ($statusFilter === 'verified') ? 'selected' : ''; ?>>Verified</option>
                                <option value="suspended" <?php echo ($statusFilter === 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                                <option value="rejected" <?php echo ($statusFilter === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                <option value="deactivated" <?php echo ($statusFilter === 'deactivated') ? 'selected' : ''; ?>>Deactivated</option>
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
                                <th>Primary Role</th>
                                <th>Assigned Roles</th>
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
                                    $statusClass = yamu_badge_class($row['account_status']);
                                    $verificationClass = yamu_badge_class($row['verification_status']);
                                    $assignedRolesLabel = ucfirst($row['role']);

                                    if (!empty($row['assigned_roles'])) {
                                        $assignedRoleParts = array_filter(array_map('trim', explode(',', $row['assigned_roles'])));
                                        $assignedRoleParts = array_map(function ($roleValue) {
                                            return yamu_role_label($roleValue);
                                        }, $assignedRoleParts);
                                        $assignedRolesLabel = implode(', ', $assignedRoleParts);
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo yamu_e($row['user_id']); ?></td>
                                        <td class="image-cell"><img src="../assets/images/uploads/avatar/<?php echo yamu_e($row['profile_pic']); ?>" alt="avatar" class="table-avatar"></td>
                                        <td><?php echo yamu_e($row['full_name']); ?></td>
                                        <td><?php echo yamu_e(ucfirst($row['role'])); ?></td>
                                        <td><?php echo yamu_e($assignedRolesLabel); ?></td>
                                        <td><span class="<?php echo yamu_e($statusClass); ?>"><?php echo yamu_e(ucfirst($row['account_status'])); ?></span></td>
                                        <td><span class="<?php echo yamu_e($verificationClass); ?>"><?php echo yamu_e(ucfirst(str_replace('_', ' ', $row['verification_status']))); ?></span></td>
                                        <td><?php echo yamu_e($row['email']); ?></td>
                                        <td><?php echo yamu_e($row['phone']); ?></td>
                                        <td><?php echo yamu_e($row['license_or_nic']); ?></td>
                                        <td><?php echo yamu_e($row['created_at'] ?: $row['rag_date']); ?></td>
                                        <td class="action-cell">
                                            <div class="table-actions">
                                            <a class="edit-badge" title="Details" href="user-details.php?user_id=<?php echo $row['user_id']; ?>"><i class="ri-eye-line"></i></a>
                                            <a class="edit-badge" title="Roles" href="user-roles.php?user_id=<?php echo $row['user_id']; ?>"><i class="ri-shield-user-line"></i></a>
                                            <a class="edit-badge" title="Verify" href="user-verify.php?user_id=<?php echo $row['user_id']; ?>"><i class="ri-check-double-line"></i></a>
                                            <a class="edit-badge" title="Status" href="user-status.php?user_id=<?php echo $row['user_id']; ?>"><i class="ri-toggle-line"></i></a>
                                            <a class="edit-badge" title="Edit" href="user-edit.php?user_id=<?php echo $row['user_id']; ?>"><i class="ri-pencil-fill"></i></a>
                                            <?php if ((int) $row['user_id'] === $currentAdminUserId) { ?>
                                                <span class="Status-pending-badge">Current admin</span>
                                            <?php } elseif ($row['account_status'] === 'active' || $row['account_status'] === 'verified') { ?>
                                                <form action="includes/user-role-management.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo (int) $row['user_id']; ?>" />
                                                    <input type="hidden" name="account_status" value="suspended" />
                                                    <input type="hidden" name="redirect" value="../users.php" />
                                                    <button class="del-badge" type="submit" name="updateUserStatus" title="Suspend"><i class="ri-pause-fill"></i></button>
                                                </form>
                                            <?php } else { ?>
                                                <form action="includes/user-role-management.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo (int) $row['user_id']; ?>" />
                                                    <input type="hidden" name="account_status" value="active" />
                                                    <input type="hidden" name="redirect" value="../users.php" />
                                                    <button class="edit-badge" type="submit" name="updateUserStatus" title="Activate"><i class="ri-play-fill"></i></button>
                                                </form>
                                            <?php } ?>
                                            <?php if ((int) $row['user_id'] !== $currentAdminUserId) { ?>
                                                <a class="del-badge" title="Delete" href="includes/user-process.php?action=delete&user_id=<?php echo $row['user_id']; ?>"><i class="ri-delete-bin-7-fill"></i></a>
                                            <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='12'>No users found.</td></tr>";
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
