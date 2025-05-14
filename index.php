<?php
// 检查是否已安装
if (!file_exists('config/config.php')) {
    header('Location: install/index.php');
    exit();
}

require_once 'config/database.php';

try {
    // 获取域名列表
    $stmt = $pdo->query("SELECT * FROM domains WHERE is_active = 1");
    $domains = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 获取联系方式和网站设置
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 确保全局设置可用
    $GLOBALS['settings'] = $settings;
} catch (Exception $e) {
    die("数据库连接错误：" . $e->getMessage());
}

// 设置页面变量
$page_title = $GLOBALS['settings']['site_name'] ?? '哈喽米表';
$base_path = '';

// 添加额外的 JavaScript
$extra_scripts = <<<EOT
<script>
function toggleDetails(id) {
    const details = document.getElementById('details-' + id);
    const overlay = document.getElementById('overlay');
    
    if (details) {
        if (details.classList.contains('active')) {
            details.classList.remove('active');
            details.addEventListener('transitionend', function handler() {
                details.style.display = 'none';
                details.removeEventListener('transitionend', handler);
            });
            overlay.classList.remove('active');
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        } else {
            document.querySelectorAll('.domain-details.active').forEach(el => {
                el.classList.remove('active');
                el.style.display = 'none';
            });
            
            details.style.display = 'block';
            details.offsetHeight;
            details.classList.add('active');
            
            if (window.innerWidth <= 768) {
                overlay.style.display = 'block';
                overlay.offsetHeight; // 
                overlay.classList.add('active');
                details.style.position = 'fixed';
                details.style.left = '50%';
                details.style.top = '50%';
                details.style.transform = 'translate(-50%, -50%) scale(1)';
            } else {
                const rect = details.getBoundingClientRect();
                if (rect.right > window.innerWidth) {
                    details.style.left = 'auto';
                    details.style.right = '0';
                    details.style.transform = 'translateX(0) scale(1)';
                }
                if (rect.bottom > window.innerHeight) {
                    details.style.top = 'auto';
                    details.style.bottom = '100%';
                }
            }
        }
    }
}

document.addEventListener('click', function(event) {
    if (!event.target.closest('.domain-name')) {
        document.querySelectorAll('.domain-details.active').forEach(el => {
            el.classList.remove('active');
            el.addEventListener('transitionend', function handler() {
                el.style.display = 'none';
                el.removeEventListener('transitionend', handler);
            });
        });
        const overlay = document.getElementById('overlay');
        overlay.classList.remove('active');
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.domain-details.active').forEach(el => {
            el.classList.remove('active');
            el.addEventListener('transitionend', function handler() {
                el.style.display = 'none';
                el.removeEventListener('transitionend', handler);
            });
        });
        const overlay = document.getElementById('overlay');
        overlay.classList.remove('active');
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
    }
});
</script>
EOT;

include 'includes/header.php';
?>

<!-- Overlay -->
<div class="overlay" id="overlay"></div>

<div class="container">
    <!-- Logo -->
    <div class="logo-container">
        <?php if (file_exists('images/logo.svg')): ?>
        <img src="images/logo.svg" alt="Logo">
        <?php endif; ?>
    </div>

    <!-- List -->
    <div class="main-content">
        <?php if (!empty($settings['contact_info']) || 
                  !empty($settings['wechat']) || 
                  !empty($settings['qq']) || 
                  !empty($settings['telegram']) || 
                  !empty($settings['email'])): ?>
        <div class="contact-info">
            <?php if (!empty($settings['contact_info'])): ?>
                <div class="contact-text">
                    <?php echo nl2br(htmlspecialchars($settings['contact_info'])); ?>
                </div>
            <?php endif; ?>
            
            <div class="contact-icons">
                <?php if (!empty($settings['wechat'])): ?>
                <div class="contact-item">
                    <button class="contact-icon" onclick="toggleQR('wechat-qr')" title="微信">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 64 64">
<path d="M 22.818359 8.453125 C 10.817359 8.454125 1 16.635281 1 26.863281 C 1 32.727281 4.1356406 37.772641 9.1816406 41.181641 L 6 49 L 15.044922 44.044922 C 17.362922 44.862922 20.090359 45.271484 22.818359 45.271484 C 23.411359 45.271484 23.997125 45.244125 24.578125 45.203125 C 27.200125 51.942125 34.582437 56.816406 43.273438 56.816406 C 45.534438 56.816406 47.747234 56.504625 49.865234 55.890625 L 55.931641 59.289062 C 56.237641 59.460062 56.574156 59.544922 56.910156 59.544922 C 57.352156 59.544922 57.793297 59.397375 58.154297 59.109375 C 58.790297 58.603375 59.057078 57.763375 58.830078 56.984375 L 57.294922 51.724609 C 60.938922 48.575609 63 44.305359 63 39.818359 C 63 30.685359 54.594516 23.232516 44.103516 22.853516 C 41.931516 14.580516 33.198359 8.453125 22.818359 8.453125 z M 15 17 C 16.65 17 18 18.35 18 20 C 18 21.65 16.65 23 15 23 C 13.35 23 12 21.65 12 20 C 12 18.35 13.35 17 15 17 z M 31 17 C 32.65 17 34 18.35 34 20 C 34 21.65 32.65 23 31 23 C 29.35 23 28 21.65 28 20 C 28 18.35 29.35 17 31 17 z M 43.271484 26.818359 C 51.943484 26.818359 59 32.650359 59 39.818359 C 59 43.513359 57.156547 46.912578 53.810547 49.392578 C 53.137547 49.891578 52.845078 50.756547 53.080078 51.560547 L 53.621094 53.412109 L 51.068359 51.982422 C 50.766359 51.813422 50.430797 51.728516 50.091797 51.728516 C 49.878797 51.728516 49.663031 51.761078 49.457031 51.830078 C 47.518031 52.476078 45.379484 52.818359 43.271484 52.818359 C 34.599484 52.818359 27.544922 46.986359 27.544922 39.818359 C 27.544922 32.650359 34.599484 26.818359 43.271484 26.818359 z M 37 35 A 2 2 0 0 0 37 39 A 2 2 0 0 0 37 35 z M 50.074219 35 C 48.968219 35 48 35.933 48 37 C 48 38.067 48.968219 39 50.074219 39 C 51.180219 39 52.148437 38.067 52.148438 37 C 52.148438 35.933 51.180219 35 50.074219 35 z"></path>
</svg>                    </button>
                    <div id="wechat-qr" class="qr-details">
                        <h3>微信</h3>
                        <?php if (!empty($settings['wechat_qr'])): ?>
                        <img src="<?php echo htmlspecialchars($settings['wechat_qr']); ?>" alt="WeChat QR">
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars($settings['wechat']); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($settings['qq'])): ?>
                <div class="contact-item">
                    <button class="contact-icon" onclick="toggleQR('qq-qr')" title="QQ">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 32 32">
<path d="M 16 5.886719 C 22.085938 5.886719 22.394531 11.324219 22.730469 12.394531 C 22.730469 12.394531 23.210938 12.929688 23.324219 13.746094 C 23.398438 14.273438 23.097656 14.871094 23.097656 14.871094 C 23.097656 14.871094 25.042969 17.492188 25.042969 19.550781 C 25.042969 20.835938 24.664063 21.5 24.222656 21.5 C 23.777344 21.5 23.128906 20.140625 23.128906 20.140625 C 23.128906 20.140625 22.113281 22.308594 21.605469 22.621094 C 21.097656 22.929688 23.4375 23.269531 23.4375 24.28125 C 23.4375 25.296875 21.578125 25.746094 20.058594 25.746094 C 18.535156 25.746094 16.113281 24.957031 16.113281 24.957031 L 15.238281 24.929688 C 15.238281 24.929688 14.5625 25.886719 11.773438 25.886719 C 8.984375 25.886719 7.773438 25.128906 7.773438 24.226563 C 7.773438 23.011719 9.550781 22.847656 9.550781 22.847656 C 9.550781 22.847656 8.417969 22.53125 7.460938 19.851563 C 7.460938 19.851563 6.796875 21.296875 5.859375 21.296875 C 5.859375 21.296875 5.464844 21.0625 5.464844 19.746094 C 5.464844 17.023438 7.421875 15.695313 8.265625 14.878906 C 8.265625 14.878906 8.125 14.523438 8.199219 14.082031 C 8.28125 13.589844 8.574219 13.292969 8.574219 13.292969 C 8.574219 13.292969 8.464844 12.703125 8.875 12.226563 C 8.957031 10.902344 9.914063 5.886719 16 5.886719 M 16 3.886719 C 9.601563 3.886719 7.332031 8.476563 6.929688 11.554688 C 6.738281 11.929688 6.628906 12.316406 6.585938 12.679688 C 6.433594 12.96875 6.296875 13.324219 6.226563 13.746094 C 6.207031 13.851563 6.195313 13.960938 6.1875 14.0625 C 5.078125 15.082031 3.464844 16.820313 3.464844 19.746094 C 3.464844 21.777344 4.210938 22.644531 4.839844 23.015625 L 5.308594 23.296875 L 5.859375 23.296875 C 5.875 23.296875 5.890625 23.296875 5.910156 23.296875 C 5.820313 23.582031 5.773438 23.890625 5.773438 24.226563 C 5.773438 25.085938 6.207031 27.890625 11.773438 27.890625 C 13.8125 27.890625 15.085938 27.449219 15.867188 26.976563 C 16.6875 27.222656 18.605469 27.746094 20.054688 27.746094 C 23.324219 27.746094 25.4375 26.386719 25.4375 24.28125 C 25.4375 23.90625 25.363281 23.574219 25.242188 23.277344 C 26.207031 22.839844 27.039063 21.710938 27.039063 19.550781 C 27.039063 17.65625 25.992188 15.667969 25.28125 14.535156 C 25.335938 14.210938 25.355469 13.847656 25.304688 13.472656 C 25.1875 12.632813 24.851563 11.964844 24.582031 11.546875 C 24.574219 11.507813 24.566406 11.46875 24.558594 11.429688 C 23.511719 6.421875 20.628906 3.886719 16 3.886719 Z"></path>
</svg>                    </button>
                    <div id="qq-qr" class="qr-details">
                        <h3>QQ</h3>
                        <?php if (!empty($settings['qq_qr'])): ?>
                        <img src="<?php echo htmlspecialchars($settings['qq_qr']); ?>" alt="QQ QR">
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars($settings['qq']); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($settings['telegram'])): ?>
                <a href="https://t.me/<?php echo ltrim($settings['telegram'], '@'); ?>" target="_blank" class="contact-icon" title="Telegram">
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 50 50">
<path d="M46.137,6.552c-0.75-0.636-1.928-0.727-3.146-0.238l-0.002,0C41.708,6.828,6.728,21.832,5.304,22.445	c-0.259,0.09-2.521,0.934-2.288,2.814c0.208,1.695,2.026,2.397,2.248,2.478l8.893,3.045c0.59,1.964,2.765,9.21,3.246,10.758	c0.3,0.965,0.789,2.233,1.646,2.494c0.752,0.29,1.5,0.025,1.984-0.355l5.437-5.043l8.777,6.845l0.209,0.125	c0.596,0.264,1.167,0.396,1.712,0.396c0.421,0,0.825-0.079,1.211-0.237c1.315-0.54,1.841-1.793,1.896-1.935l6.556-34.077	C47.231,7.933,46.675,7.007,46.137,6.552z M22,32l-3,8l-3-10l23-17L22,32z"></path>
</svg>                </a>
                <?php endif; ?>
                
                <?php if (!empty($settings['email'])): ?>
                <a href="mailto:<?php echo htmlspecialchars($settings['email']); ?>" class="contact-icon" title="Email">
                    <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php endif; ?>
        
    <div class="search-container">
        <div class="search-wrapper">
            <svg class="search-icon" viewBox="0 0 24 24">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
            <input type="text" id="domainSearch" placeholder="Search for domain you're interested in." class="search-input">
        </div>
    </div>

        
        <div class="domain-list">
            <?php foreach($domains as $domain): ?>
            <?php 
            $hasDetails = ($domain['show_price'] && $domain['price']) || 
                         ($domain['show_notes'] && $domain['notes']);
            ?>
            <div class="domain-item">
                <p class="domain-name" <?php echo $hasDetails ? 'onclick="toggleDetails('.$domain['id'].')"' : ''; ?>>
                    <span class="<?php echo $hasDetails ? 'has-details' : ''; ?>">
                        <?php echo htmlspecialchars($domain['domain_name']); ?>
                    </span>
                </p>
                <?php if($hasDetails): ?>
                <div id="details-<?php echo $domain['id']; ?>" class="domain-details">
                    <?php if($domain['show_price'] && $domain['price']): ?>
                        <p class="price">价格: ￥<?php echo number_format($domain['price'], 2); ?></p>
                    <?php endif; ?>
                    <?php if($domain['show_notes'] && $domain['notes']): ?>
                        <p class="notes">备注: <?php echo htmlspecialchars($domain['notes']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>

<script>
function toggleQR(id) {
    const qrElement = document.getElementById(id);
    const allQRs = document.querySelectorAll('.qr-details');
    
    // 关闭其他打开的二维码
    allQRs.forEach(el => {
        if (el.id !== id) {
            el.classList.remove('active');
        }
    });
    
    // 切换当前二维码的显示状态
    qrElement.classList.toggle('active');
}

// 点击其他地方关闭二维码
document.addEventListener('click', function(event) {
    if (!event.target.closest('.contact-item')) {
        document.querySelectorAll('.qr-details').forEach(el => {
            el.classList.remove('active');
        });
    }
});

// ESC 键关闭二维码
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.qr-details').forEach(el => {
            el.classList.remove('active');
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('domainSearch');
    const domainItems = document.querySelectorAll('.domain-item');
    
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        
        domainItems.forEach(item => {
            const domainName = item.querySelector('.domain-name span').textContent.toLowerCase();
            if (domainName.includes(searchTerm)) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    });
});
</script> 