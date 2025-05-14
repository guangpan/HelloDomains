    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> 
        <?php if(!empty($GLOBALS['settings']['site_domain'])): ?>
            <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . htmlspecialchars($GLOBALS['settings']['site_domain']); ?>" target="_blank" class="footer-link">
                <?php echo htmlspecialchars($GLOBALS['settings']['site_name'] ?? '哈喽米表'); ?>
            </a>
        <?php else: ?>
            <?php echo htmlspecialchars($GLOBALS['settings']['site_name'] ?? '哈喽米表'); ?>
        <?php endif; ?>
        <?php echo htmlspecialchars($GLOBALS['settings']['site_footer'] ?? 'All Rights Reserved.'); ?></p>
    </footer>

    <style>
    .footer {
        text-align: center;
        padding: 20px 0;
        margin-top: 30px;
        color: #666;
        font-size: 14px;
    }
    .footer-link {
        color: #3498db;
        text-decoration: none;
        transition: color 0.2s;
        position: relative;
    }
    .footer-link:hover {
        color: #2980b9;
    }
    .footer-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 1px;
        bottom: -2px;
        left: 0;
        background-color: #2980b9;
        transition: width 0.3s;
    }
    .footer-link:hover::after {
        width: 100%;
    }
    html[data-theme="dark"] .footer {
        color: #aaa;
    }
    html[data-theme="dark"] .footer-link {
        color: #60a5fa;
    }
    html[data-theme="dark"] .footer-link:hover {
        color: #93c5fd;
    }
    html[data-theme="dark"] .footer-link::after {
        background-color: #93c5fd;
    }
    </style>

    <?php if (!isset($is_login_page)): ?>
        <script src="<?php echo $base_path; ?>js/admin.js"></script>
    <?php else: ?>
        <script>
        // 初始化主题状态
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        </script>
    <?php endif; ?>

    <?php if (isset($extra_scripts)): ?>
        <?php echo $extra_scripts; ?>
    <?php endif; ?>
</body>
</html> 