<?php require_once __DIR__ . '/../../includes/auth.php'; ?>
<?php $adminProfileUrl = !empty($_SESSION['admin']['user_id']) ? 'user-edit.php?user_id=' . (int) $_SESSION['admin']['user_id'] : 'profile-setting.php'; ?>
<header class="header">
    <div class="header-greeting">
        <h3>Hello, <?php echo carzo_e($_SESSION['admin']['username']) ?></h3>
        <!-- <p>Today is Monday, 20 October 2023</p> -->
    </div>
    <div class="header-avatar subnav">
        <img src="../assets/images/uploads/avatar/<?php echo carzo_e($_SESSION['admin']['avatar']) ?>" alt="avatar">
        <ul class="subnav-content">
            <li><a href="<?php echo $adminProfileUrl; ?>"><i class="ri-settings-2-line"></i> Profile Setting</a></li>
            <!-- <li><a href="#"><i class="ri-lock-line"></i> Change Password</a></li> -->
            <li class="logout"><a href="loguot.php"><i class="ri-login-box-line"></i> Log Out</a></li>
        </ul>
        <span><?php echo carzo_e($_SESSION['admin']['username']) ?></span>
    </div>
</header>
