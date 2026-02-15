<aside class="w-64 glass-sidebar min-h-screen fixed top-0 left-0 flex flex-col z-50 transition-all duration-300 border-r border-slate-800 shadow-2xl">
    <!-- Brand -->
    <div class="h-16 flex items-center px-6 border-b border-white/5 bg-slate-900/50 backdrop-blur-md">
        <div class="h-8 w-8 rounded bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20 mr-3">
            <i class="fas fa-layer-group text-sm"></i>
        </div>
        <div>
            <h2 class="text-sm font-bold text-slate-100 tracking-wide font-inter">LMS <span class="text-indigo-400">ADMIN</span></h2>
        </div>
    </div>
    
    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1 custom-scrollbar">
        <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        $navItems = [
            'dashboard.php' => ['label' => 'Overview', 'icon' => 'fas fa-chart-pie', 'path' => '/Library-management/dashboard/dashboard.php'],
            'books.php' => ['label' => 'Books Inventory', 'icon' => 'fas fa-book', 'path' => '/Library-management/books/books.php'],
            'members.php' => ['label' => 'Members', 'icon' => 'fas fa-users', 'path' => '/Library-management/members/members.php'],
            'issueBook.php' => ['label' => 'Issue Book', 'icon' => 'fas fa-file-signature', 'path' => '/Library-management/issue/issueBook.php'],
            'returnBook.php' => ['label' => 'Return Book', 'icon' => 'fas fa-undo-alt', 'path' => '/Library-management/return/returnBook.php'],
            'librarians.php' => ['label' => 'Librarians', 'icon' => 'fas fa-user-shield', 'path' => '/Library-management/librarians/librarians.php'],
            'categories.php' => ['label' => 'Categories', 'icon' => 'fas fa-th-list', 'path' => '/Library-management/categories/categories.php'],
            'publishers.php' => ['label' => 'Publishers', 'icon' => 'fas fa-building', 'path' => '/Library-management/publishers/publishers.php'],
            'reports.php' => ['label' => 'Analytics', 'icon' => 'fas fa-chart-line', 'path' => '/Library-management/reports/reports.php'],
        ];

        $index = 0;
        foreach ($navItems as $page => $item) {
            $isActive = $currentPage === $page;
            // Staggered animation delay
            $delayClass = 'delay-' . (min(($index + 1) * 100, 500)); 
            
            // Active State: subtle glow, border-l
            $activeClass = $isActive 
                ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500' 
                : 'text-slate-400 hover:bg-white/5 hover:text-slate-200 border-transparent';
            ?>
            <a href="<?= $item['path'] ?>" 
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-r-lg border-l-2 transition-all duration-200 group animate-enter <?= $delayClass ?>"
               style="animation-fill-mode: both;">
               
               <i class="<?= $item['icon'] ?> w-5 text-center transition-colors <?= $isActive ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300' ?>"></i>
               <span class="ml-3 tracking-wide"><?= $item['label'] ?></span>
            </a>
            <?php
            $index++;
        }
        ?>
    </nav>
    
    <!-- User Profile / Logout -->
    <div class="p-4 border-t border-white/5 bg-slate-900/30">
        <a href="/Library-management/auth/logout.php" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/5 transition-all text-slate-400 hover:text-red-400 group">
           <i class="fas fa-power-off text-xs group-hover:rotate-90 transition-transform"></i>
           <span class="text-xs font-semibold uppercase tracking-wider">Sign Out</span>
        </a>
    </div>
</aside>
