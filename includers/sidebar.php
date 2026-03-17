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

<aside id="sidebar" class="w-64 h-screen fixed top-0 left-0 flex flex-col z-50 bg-slate-900/95 backdrop-blur-3xl border-r border-white/10 shadow-[4px_0_24px_rgba(0,0,0,0.4)] sidebar-transition group/sidebar -translate-x-full md:translate-x-0">
    <!-- Brand -->
    <div id="brand-header" class="h-24 flex items-center px-6 border-b border-white/5 relative z-10 transition-all duration-300 bg-gradient-to-b from-white/5 to-transparent">
        <div class="flex items-center gap-4 overflow-hidden w-full">
            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-indigo-600 flex items-center justify-center text-white shadow-[0_0_20px_rgba(99,102,241,0.4)] border border-white/20 flex-shrink-0 relative overflow-hidden group">
                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-overlay"></div>
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out"></div>
                <i class="fas fa-layer-group text-xl relative z-10"></i>
            </div>
            <div class="sidebar-text fade-text flex flex-col">
                <h2 class="text-[1.35rem] leading-none font-black text-white tracking-widest font-inter">LMS</h2>
                <span class="text-[0.65rem] font-bold text-indigo-400 tracking-[0.25em] uppercase mt-1">Admin Pro</span>
            </div>
        </div>
    </div>

    <!-- Floating Toggle Button -->
    <button id="sidebarToggle" class="absolute -right-3.5 top-10 z-50 h-7 w-7 rounded-full bg-indigo-600 text-white shadow-[0_0_15px_rgba(99,102,241,0.5)] border-2 border-slate-900 hover:bg-indigo-500 hover:scale-110 transition-all hidden md:flex items-center justify-center focus:outline-none cursor-pointer">
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

        // Filter items for staff role
        $adminRole = $_SESSION['admin_role'] ?? 'admin';
        if ($adminRole === 'staff') {
            $allowedForStaff = ['dashboard.php', 'issueBook.php', 'returnBook.php', 'reports.php'];
            $navItems = array_filter($navItems, function($key) use ($allowedForStaff) {
                return in_array($key, $allowedForStaff);
            }, ARRAY_FILTER_USE_KEY);
        }

        foreach ($navItems as $page => $item) {
            $isActive = $currentPage === $page;
            
            // Premium Dark Theme Styles
            $baseClasses = "nav-item flex items-center px-4 py-3.5 mx-2 rounded-2xl text-sm font-medium transition-all duration-300 group relative overflow-hidden";
            
            if ($isActive) {
                // Active: Indigo gradient background with glowing text
                $classes = $baseClasses . " bg-indigo-600 shadow-[0_4px_20px_rgba(79,70,229,0.3)] text-white border border-indigo-500/50";
                $iconColor = "text-white";
            } else {
                // Inactive: Slate text, hover effect
                $classes = $baseClasses . " text-slate-400 hover:bg-white/5 hover:text-white border border-transparent";
                $iconColor = "text-slate-500 group-hover:text-indigo-400 transition-colors duration-300";
            }
            ?>
            <a href="<?= $item['path'] ?>" class="<?= $classes ?>" title="<?= $item['label'] ?>">
                <?php if($isActive): ?>
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] animate-[shimmer_2s_infinite]"></div>
                <?php endif; ?>
                
                <div class="flex items-center justify-center w-8 h-8 rounded-xl <?= $isActive ? 'bg-white/20 shadow-inner' : 'bg-slate-800/80 group-hover:bg-slate-800' ?> flex-shrink-0 transition-colors duration-300 relative z-10">
                    <i class="<?= $item['icon'] ?> <?= $iconColor ?> text-sm"></i>
                </div>
                <!-- Added margin-left for better spacing -->
                <span class="ml-4 tracking-wide font-semibold sidebar-text fade-text relative z-10"><?= $item['label'] ?></span>
                

            </a>
            <?php
        }
        ?>
    </nav>
    
    <!-- Footer -->
    <div class="p-4 border-t border-white/5 relative z-20">
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
    
    // Select the main content div based on its class
    const mainContent = document.querySelector('.main-content');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn'); // from navbar

    // Retrieve state
    let isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

    // Apply initial state
    if (window.innerWidth >= 768) {
        applyState(false); 
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            isCollapsed = !isCollapsed;
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            applyState(true);
        });
    }

    // Mobile menu toggle
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        });
    }
    
    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', (e) => {
        if (window.innerWidth < 768) { // md breakpoint
            if (!sidebar.contains(e.target) && mobileMenuBtn && !mobileMenuBtn.contains(e.target)) {
                sidebar.classList.add('-translate-x-full');
            }
        }
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
                mainContent.classList.remove('md:ml-64');
                mainContent.classList.add('md:ml-20');
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
            if (toggleIcon) {
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            }

        } else {
            // --- EXPANDED ---
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-64');

            if (mainContent) {
                mainContent.classList.remove('md:ml-20');
                mainContent.classList.add('md:ml-64');
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
            if (toggleIcon) {
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
            }
        }
    }
});
</script>



