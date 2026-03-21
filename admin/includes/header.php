    <?php
        $adminBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/admin/index.php')), '/');
        $siteBasePath = rtrim(str_replace('\\', '/', dirname($adminBasePath)), '/');

        if ($adminBasePath === '') {
            $adminBasePath = '/admin';
        }
    ?>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
            if(isset($page_title)) {
                echo $page_title;
            }
        ?>
    </title>
    <link rel="icon" href="<?php echo $siteBasePath; ?>/assets/images/logo/logo-icon.png" type="image/icon type">

    <!-- Main CSS File -->
    <link rel="stylesheet" href="<?php echo $siteBasePath; ?>/assets/css/style.css">

    <!-- Admin CSS File -->
    <link rel="stylesheet" href="<?php echo $adminBasePath; ?>/assets/css/admin-style.css">
    <!-- Responsive CSS File -->
    <!-- <link rel="stylesheet" href="assets/css/responsive_style.css"> -->

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Ubuntu:wght@400;700&display=swap" rel="stylesheet">

    <!-- Remix Icon CDN -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.2.0/fonts/remixicon.css" rel="stylesheet">
