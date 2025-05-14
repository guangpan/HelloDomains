document.addEventListener('DOMContentLoaded', function() {
    // 菜单相关变量
    const menuToggle = document.getElementById('menuToggle');
    const actionMenu = document.getElementById('actionMenu');
    const mobileThemeToggle = document.getElementById('mobileThemeToggle');
    const desktopThemeToggle = document.getElementById('themeToggle');
    
    // 菜单切换
    if (menuToggle && actionMenu) {
        // 阻止菜单内部点击事件冒泡
        actionMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // 菜单按钮点击事件
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.toggle('active');
            actionMenu.classList.toggle('show');
        });

        // 点击其他地方关闭菜单
        document.addEventListener('click', function(e) {
            if (!menuToggle.contains(e.target) && !actionMenu.contains(e.target)) {
                menuToggle.classList.remove('active');
                actionMenu.classList.remove('show');
            }
        });
    }

    // 更新主题图标
    function updateThemeUI(theme) {
        // 桌面端图标更新
        if (desktopThemeToggle) {
            const sunIcon = desktopThemeToggle.querySelector('.sun');
            const moonIcon = desktopThemeToggle.querySelector('.moon');
            if (theme === 'dark') {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            } else {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            }
        }

        // 移动端开关更新
        if (mobileThemeToggle) {
            const switchHandle = mobileThemeToggle.querySelector('.switch-handle');
            if (theme === 'dark') {
                mobileThemeToggle.querySelector('.toggle-switch').style.backgroundColor = '#4CAF50';
                switchHandle.style.transform = 'translateX(20px)';
            } else {
                mobileThemeToggle.querySelector('.toggle-switch').style.backgroundColor = '';
                switchHandle.style.transform = 'translateX(0)';
            }
        }
    }

    // 主题切换
    function toggleTheme(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeUI(newTheme);
    }

    // 初始化主题状态
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeUI(savedTheme);

    // 绑定主题切换事件
    if (mobileThemeToggle) {
        mobileThemeToggle.addEventListener('click', toggleTheme);
    }
    if (desktopThemeToggle) {
        desktopThemeToggle.addEventListener('click', toggleTheme);
    }

    // 监听系统主题变化
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    prefersDarkScheme.addEventListener('change', (e) => {
        const newTheme = e.matches ? 'dark' : 'light';
        if (!localStorage.getItem('theme')) {
            document.documentElement.setAttribute('data-theme', newTheme);
            updateThemeUI(newTheme);
        }
    });

    // 批量操作相关代码（仅在 index.php 页面执行）
    if (document.getElementById('batchForm')) {
        initBatchOperations();
    }
});

// 处理批量操作
function handleBatchAction(action, form) {
    const checkedBoxes = document.querySelectorAll('input[name="selected[]"]:checked');
    
    if (checkedBoxes.length === 0) {
        alert('请选择要操作的域名');
        return false;
    }

    if (!action) {
        alert('请选择要执行的操作');
        return false;
    }

    const confirmMessage = {
        'delete': '确定要删除选中的域名吗？',
        'show': '确定要显示选中的域名吗？',
        'hide': '确定要隐藏选中的域名吗？',
        'show_price': '确定要显示选中域名的价格吗？',
        'hide_price': '确定要隐藏选中域名的价格吗？',
        'show_notes': '确定要显示选中域名的备注吗？',
        'hide_notes': '确定要隐藏选中域名的备注吗？'
    }[action];

    if (confirm(confirmMessage)) {
        form.submit();
        return true;
    }
    return false;
}

// 批量操作初始化函数
function initBatchOperations() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('input[name="selected[]"]');
    const batchAction = document.getElementById('batchAction');
    const batchActionMobile = document.getElementById('batchActionMobile');
    const batchButtonDesktop = document.getElementById('batchButtonDesktop');
    const batchButtonMobile = document.getElementById('batchButtonMobile');
    const batchForm = document.getElementById('batchForm');

    // 更新批量操作按钮状态
    function updateBatchButtons() {
        const checkedBoxes = document.querySelectorAll('input[name="selected[]"]:checked');
        const hasSelection = checkedBoxes.length > 0;
        const hasAction = batchAction.value || batchActionMobile?.value;
        
        if (batchActionMobile && batchAction.value !== batchActionMobile.value) {
            if (batchAction === document.activeElement) {
                batchActionMobile.value = batchAction.value;
            } else {
                batchAction.value = batchActionMobile.value;
            }
        }

        if (batchButtonDesktop) {
            batchButtonDesktop.disabled = !hasSelection || !hasAction;
        }
        if (batchButtonMobile) {
            batchButtonMobile.disabled = !hasSelection || !hasAction;
        }
    }

    // 绑定事件
    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBatchButtons();
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBatchButtons);
    });

    batchAction?.addEventListener('change', updateBatchButtons);
    batchActionMobile?.addEventListener('change', updateBatchButtons);

    batchButtonMobile?.addEventListener('click', function(e) {
        e.preventDefault();
        handleBatchAction(batchActionMobile.value, batchForm);
    });

    batchForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        if (e.submitter && (e.submitter.id === 'batchButtonDesktop' || e.submitter.id === 'batchButtonMobile')) {
            handleBatchAction(batchAction.value, this);
        }
    });
} 