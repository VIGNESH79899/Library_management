<aside class="w-64 glass-dark text-slate-300 min-h-screen fixed top-0 left-0 flex flex-col z-50 transition-all duration-300 border-r border-gray-800 shadow-2xl">
    <div class="p-6 flex items-center gap-3 border-b border-gray-800/50">
        <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white shadow-indigo-500/20 shadow-lg">
            <i class="fas fa-book-open"></i>
        </div>
        <h2 class="text-xl font-bold text-white tracking-wide">LMS <span class="text-indigo-500">Pro</span></h2>
    </div>
    
    <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
        <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        $navItems = [
            'dashboard.php' => ['label' => 'Dashboard', 'icon' => 'fas fa-th-large', 'path' => '/Library-management/dashboard/dashboard.php'],
            'books.php' => ['label' => 'Books', 'icon' => 'fas fa-book', 'path' => '/Library-management/books/books.php'],
            'categories.php' => ['label' => 'Categories', 'icon' => 'fas fa-list', 'path' => '/Library-management/categories/categories.php'],
            'members.php' => ['label' => 'Members', 'icon' => 'fas fa-users', 'path' => '/Library-management/members/members.php'],
            'issueBook.php' => ['label' => 'Issue Book', 'icon' => 'fas fa-hand-holding', 'path' => '/Library-management/issue/issueBook.php'],
            'returnBook.php' => ['label' => 'Return Book', 'icon' => 'fas fa-undo', 'path' => '/Library-management/return/returnBook.php'],
            'librarians.php' => ['label' => 'Librarians', 'icon' => 'fas fa-user-tie', 'path' => '/Library-management/librarians/librarians.php'],
            'publishers.php' => ['label' => 'Publishers', 'icon' => 'fas fa-print', 'path' => '/Library-management/publishers/publishers.php'],
            'reports.php' => ['label' => 'Reports', 'icon' => 'fas fa-chart-bar', 'path' => '/Library-management/reports/reports.php'],
        ];

        foreach ($navItems as $page => $item) {
            $isActive = $currentPage === $page;
            $activeClass = $isActive ? 'bg-indigo-600/10 text-white border-indigo-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white border-transparent';
            ?>
            <a href="<?= $item['path'] ?>" 
               class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 border-l-4 <?= $activeClass ?> group">
               <i class="<?= $item['icon'] ?> w-6 text-center transition-transform group-hover:scale-110 <?= $isActive ? 'text-indigo-400' : 'text-slate-500 group-hover:text-indigo-400' ?>"></i>
               <span class="ml-3 font-medium"><?= $item['label'] ?></span>
               <?php if($isActive): ?>
                <div class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_8px_rgba(129,140,248,0.6)]"></div>
               <?php endif; ?>
            </a>
            <?php
        }
        ?>
    </nav>
    
    <div class="p-4 border-t border-gray-800/50">
        <a href="/Library-management/auth/logout.php" 
           class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-red-500/10 hover:bg-red-600 text-red-500 hover:text-white rounded-lg transition-all duration-200 group">
           <i class="fas fa-sign-out-alt group-hover:rotate-180 transition-transform duration-300"></i>
           <span class="font-medium">Logout</span>
        </a>
    </div>
</aside>
