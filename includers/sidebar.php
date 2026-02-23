<style>
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.02);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Layout Transitions */
    .sidebar-transition {
        transition-property: width, margin, transform, opacity;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 300ms;
    }
    
    .content-transition {
        transition-property: margin-left;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 300ms;
    }

    .fade-text {
        transition: opacity 0.2s ease;
        white-space: nowrap;
    }
</style>

<aside id="sidebar" class="w-64 h-screen fixed top-0 left-0 flex flex-col z-50 bg-[#0f172a] border-r border-slate-700/50 shadow-2xl sidebar-transition group/sidebar">
    <!-- Brand -->
    <div id="brand-header" class="h-20 flex items-center px-6 border-b border-slate-700/50 bg-[#0f172a] relative z-10 transition-all duration-300">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20 flex-shrink-0">
                <i class="fas fa-layer-group text-lg"></i>
            </div>
            <div class="sidebar-text fade-text">
                <h2 class="text-base font-bold text-white tracking-wide font-inter">LMS <span class="text-indigo-400">ADMIN</span></h2>
            </div>
        </div>
    </div>

    <!-- Floating Toggle Button -->
    <button id="sidebarToggle" class="absolute -right-3 top-8 z-50 h-6 w-6 rounded-full bg-indigo-600 text-white shadow-md border-2 border-[#0f172a] hover:bg-indigo-500 transition-all flex items-center justify-center focus:outline-none cursor-pointer">
        <i class="fas fa-chevron-left text-[10px] transition-transform duration-300"></i>
    </button>
    
    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2 custom-scrollbar overflow-x-hidden">
        
        <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        
        $navItems = [
            'dashboard.php'  => ['label' => 'Overview',       'icon' => 'fas fa-chart-pie',      'path' => BASE_URL . '/dashboard/dashboard.php'],
            'books.php'      => ['label' => 'Books Inventory', 'icon' => 'fas fa-book',            'path' => BASE_URL . '/books/books.php'],
            'members.php'    => ['label' => 'Members',         'icon' => 'fas fa-users',           'path' => BASE_URL . '/members/members.php'],
            'issueBook.php'  => ['label' => 'Issue Book',      'icon' => 'fas fa-file-signature',  'path' => BASE_URL . '/issue/issueBook.php'],
            'returnBook.php' => ['label' => 'Return Book',     'icon' => 'fas fa-undo-alt',        'path' => BASE_URL . '/return/returnBook.php'],
            'librarians.php' => ['label' => 'Librarians',      'icon' => 'fas fa-user-shield',     'path' => BASE_URL . '/librarians/librarians.php'],
            'categories.php' => ['label' => 'Categories',      'icon' => 'fas fa-th-list',         'path' => BASE_URL . '/categories/categories.php'],
            'publishers.php' => ['label' => 'Publishers',      'icon' => 'fas fa-building',        'path' => BASE_URL . '/publishers/publishers.php'],
            'reports.php'    => ['label' => 'Analytics',       'icon' => 'fas fa-chart-line',      'path' => BASE_URL . '/reports/reports.php'],
            'email_logs.php' => ['label' => 'Email Logs',      'icon' => 'fas fa-envelope-open-text', 'path' => BASE_URL . '/admin/email_logs.php'],
        ];

        foreach ($navItems as $page => $item) {
            $isActive = $currentPage === $page;
            
            // Premium Dark Theme Styles
            $baseClasses = "nav-item flex items-center px-3 py-3 rounded-xl text-sm font-medium transition-all duration-200 group relative";
            
            if ($isActive) {
                // Active: Indigo gradient background with glowing text
                $classes = $baseClasses . " bg-indigo-600 shadow-lg shadow-indigo-900/20 text-white";
                $iconColor = "text-white";
            } else {
                // Inactive: Slate text, hover effect
                $classes = $baseClasses . " text-slate-400 hover:bg-slate-800 hover:text-white";
                $iconColor = "text-slate-400 group-hover:text-white transition-colors";
            }
            ?>
            <a href="<?= $item['path'] ?>" class="<?= $classes ?>" title="<?= $item['label'] ?>">
                <div class="flex items-center justify-center w-6 h-6 flex-shrink-0">
                    <i class="<?= $item['icon'] ?> <?= $iconColor ?> text-lg"></i>
                </div>
                <!-- Added margin-left for better spacing -->
                <span class="ml-4 tracking-wide sidebar-text fade-text"><?= $item['label'] ?></span>
                
                <?php if($isActive): ?>
                <!-- Optional: small dot indicator -->
                <div class="absolute right-3 w-1.5 h-1.5 rounded-full bg-white opacity-50 sidebar-text fade-text"></div>
                <?php endif; ?>
            </a>
            <?php
        }
        ?>
    </nav>
    
    <!-- Footer -->
    <div class="p-4 border-t border-slate-700/50 bg-[#0f172a] relative z-20">
        <a href="<?= BASE_URL ?>/auth/logout.php" 
           class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-red-500/10 border border-transparent hover:border-red-500/20 transition-all text-slate-400 hover:text-red-400 group overflow-hidden">
            <div class="w-6 h-6 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-power-off text-lg group-hover:scale-110 transition-transform"></i>
            </div>
            <span class="text-sm font-semibold sidebar-text fade-text ml-1">Sign Out</span>
        </a>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const brandHeader = document.getElementById('brand-header');
    const toggleBtn = document.getElementById('sidebarToggle');
    const toggleIcon = toggleBtn.querySelector('i');
    const sidebarTexts = document.querySelectorAll('.sidebar-text');
    
    // Select the main content div based on its margin classes
    const mainContent = document.querySelector('.ml-64, .ml-20');

    // Retrieve state
    let isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

    // Apply initial state
    applyState(false); 

    toggleBtn.addEventListener('click', (e) => {
        isCollapsed = !isCollapsed;
        localStorage.setItem('sidebarCollapsed', isCollapsed);
        applyState(true);
    });

    function applyState(animate) {
        if (animate) {
            sidebar.classList.add('sidebar-transition');
            if (mainContent) mainContent.classList.add('content-transition');
        } else {
            sidebar.classList.remove('sidebar-transition');
            if (mainContent) mainContent.classList.remove('content-transition');
        }

        const navItems = document.querySelectorAll('.nav-item');

        if (isCollapsed) {
            // --- COLLAPSED ---
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-20');
            
            if (mainContent) {
                mainContent.classList.remove('ml-64');
                mainContent.classList.add('ml-20');
            }

            // Adjust Brand Header
            brandHeader.classList.remove('px-6');
            brandHeader.classList.add('px-2', 'justify-center');

            // Hide text
            sidebarTexts.forEach(el => {
                el.style.display = 'none';
                el.style.opacity = '0';
            });
            
            // Adjust Nav Items for center alignment
            navItems.forEach(el => {
                el.classList.remove('px-3');
                el.classList.add('px-0', 'justify-center');
                // Remove extra internal margins if needed when collapsed
                const span = el.querySelector('span');
                if(span) span.style.display = 'none';
            });

            // Adjust Toggle Button (Just rotate icon)
            toggleIcon.classList.remove('fa-chevron-left');
            toggleIcon.classList.add('fa-chevron-right');

        } else {
            // --- EXPANDED ---
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-64');

            if (mainContent) {
                mainContent.classList.remove('ml-20');
                mainContent.classList.add('ml-64');
            }

            // Reset Brand Header
            brandHeader.classList.add('px-6');
            brandHeader.classList.remove('px-2', 'justify-center');

            // Show text
            sidebarTexts.forEach(el => {
                el.style.display = 'block';
                setTimeout(() => el.style.opacity = '1', 50);
            });

            // Reset Nav Items
            navItems.forEach(el => {
                el.classList.add('px-3');
                el.classList.remove('px-0', 'justify-center');
                const span = el.querySelector('span');
                if(span) span.style.display = 'inline';
            });

            // Reset Toggle Button (Just rotate icon)
            toggleIcon.classList.remove('fa-chevron-right');
            toggleIcon.classList.add('fa-chevron-left');
        }
    }
});
</script>
