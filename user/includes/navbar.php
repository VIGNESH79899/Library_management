<nav class="fixed top-0 left-0 right-0 z-50 glass-nav shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center text-white shadow-lg shadow-brand-500/30">
                    <i class="fas fa-book-open text-sm"></i>
                </div>
                <span class="font-bold text-xl tracking-tight text-slate-800">Aurora<span class="text-brand-600">Lib</span></span>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-8">
                <a href="dashboard.php" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'text-brand-600' : '' ?>">
                    Dashboard
                </a>
                <a href="browse.php" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'browse.php' ? 'text-brand-600' : '' ?>">
                    Browse Books
                </a>
                <a href="my_books.php" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition-colors <?= basename($_SERVER['PHP_SELF']) == 'my_books.php' ? 'text-brand-600' : '' ?>">
                    My Borrowed Books
                </a>
            </div>

            <!-- User Menu -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col items-end mr-2">
                    <p class="text-sm font-bold text-slate-700 leading-tight"><?= $_SESSION['user_name'] ?? 'Student' ?></p>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Student</p>
                </div>

                <!-- Click-toggle dropdown (replaces broken hover-only) -->
                <div class="relative" id="userMenuWrapper">
                    <button id="userMenuBtn"
                            onclick="toggleUserMenu()"
                            class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 transition-all">
                        <i class="fas fa-user text-sm"></i>
                    </button>

                    <!-- Dropdown -->
                    <div id="userDropdown"
                         class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-2xl shadow-slate-200/60 border border-gray-100 hidden animate-fade-in-up origin-top-right overflow-hidden z-50">
                        <div class="p-2">
                            <!-- Name header -->
                            <div class="px-3 py-2 border-b border-slate-100 mb-1">
                                <p class="text-xs font-bold text-slate-800"><?= $_SESSION['user_name'] ?? 'Student' ?></p>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider">Student Account</p>
                            </div>

                            <a href="profile.php"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">
                                <i class="fas fa-user-circle text-slate-400 w-4"></i> My Profile
                            </a>

                            <a href="../auth/logout_user.php"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors font-medium">
                                <i class="fas fa-sign-out-alt w-4"></i> Sign Out
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
<div class="h-16"></div> <!-- Spacer for fixed nav -->

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking anywhere outside it
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('userMenuWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('userDropdown').classList.add('hidden');
    }
});
</script>
