<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover,user-scalable=no">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $base_path; ?>images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $base_path; ?>images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $base_path; ?>images/favicon-16x16.png">
    <title><?php 
        $site_name = htmlspecialchars($GLOBALS['settings']['site_name'] ?? '哈喽米表');
        if (isset($page_title) && $page_title != $site_name) {
            echo htmlspecialchars($page_title) . ' - ' . $site_name;
        } else {
            echo $site_name;
        }
    ?></title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <?php if (isset($is_admin) && $is_admin): ?>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/admin.css">
    <?php endif; ?>
</head>
<body>
    <?php if (!isset($is_login_page)): ?>
    <!-- Theme Toggle -->
    <button class="theme-toggle desktop-only" id="themeToggle" aria-label="Theme Toggle">
        <svg class="sun" viewBox="0 0 24 24">
            <path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM12 21.75a.75.75 0 01-.75-.75v-2.25a.75.75 0 011.5 0V21a.75.75 0 01-.75.75zM3 12a.75.75 0 01.75-.75h2.25a.75.75 0 010 1.5H3.75A.75.75 0 013 12zM21 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5h2.25A.75.75 0 0121 12zM5.47 5.47a.75.75 0 011.06 0l1.59 1.59a.75.75 0 11-1.06 1.06L5.47 6.53a.75.75 0 010-1.06zM18.53 5.47a.75.75 0 010 1.06l-1.59 1.59a.75.75 0 01-1.06-1.06l1.59-1.59a.75.75 0 011.06 0zM5.47 18.53a.75.75 0 010-1.06l1.59-1.59a.75.75 0 111.06 1.06l-1.59 1.59a.75.75 0 01-1.06 0zM18.53 18.53a.75.75 0 01-1.06 0l-1.59-1.59a.75.75 0 111.06-1.06l1.59 1.59a.75.75 0 010 1.06z"/>
        </svg>
        <svg class="moon" viewBox="0 0 24 24" style="display: none;">
            <path d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
        </svg>
    </button>
    <?php endif; ?>
</body>
</html> 