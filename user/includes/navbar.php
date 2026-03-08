<?php require_once dirname(dirname(__DIR__)) . "/config/app.php"; ?>
<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<style>
.nav-link-pill {
    transition: all 0.2s ease;
}
.nav-link-pill:hover {
    transform: translateY(-1px);
}
</style>

<nav class="fixed top-0 left-0 right-0 z-50 glass-nav shadow-sm border-b border-slate-100/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-brand-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-brand-500/30">
                    <i class="fas fa-book-open text-sm"></i>
                </div>
                <span class="font-bold text-xl tracking-tight text-slate-800">Aurora<span class="text-brand-600">Lib</span></span>
            </div>

            <div class="hidden md:flex items-center gap-2">
                <a href="dashboard.php" class="nav-link-pill inline-flex items-center gap-2 text-sm font-semibold px-3.5 py-2 rounded-lg <?= $current_page === 'dashboard.php' ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-brand-50/60' ?>">
                    <i class="fas fa-gauge-high text-xs"></i> Dashboard
                </a>
                <a href="browse.php" class="nav-link-pill inline-flex items-center gap-2 text-sm font-semibold px-3.5 py-2 rounded-lg <?= $current_page === 'browse.php' ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-brand-50/60' ?>">
                    <i class="fas fa-book text-xs"></i> Browse Books
                </a>
                <a href="my_books.php" class="nav-link-pill inline-flex items-center gap-2 text-sm font-semibold px-3.5 py-2 rounded-lg <?= $current_page === 'my_books.php' ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-brand-50/60' ?>">
                    <i class="fas fa-book-reader text-xs"></i> My Borrowed Books
                </a>
                <a href="profile.php" class="nav-link-pill inline-flex items-center gap-2 text-sm font-semibold px-3.5 py-2 rounded-lg <?= $current_page === 'profile.php' ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-brand-50/60' ?>">
                    <i class="fas fa-user text-xs"></i> Profile
                </a>
            </div>

            <div class="flex items-center gap-2 md:gap-4">
                <button id="mobileNavMenuBtn" onclick="toggleMobileNav()" class="md:hidden w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-brand-50 hover:text-brand-600 transition-all duration-200 mr-1">
                    <i class="fas fa-bars text-sm"></i>
                </button>
                <div class="hidden md:flex flex-col items-end mr-2">
                    <p class="text-sm font-bold text-slate-700 leading-tight"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?></p>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Student</p>
                </div>

                <div class="relative" id="userMenuWrapper">
                    <button id="userMenuBtn"
                            onclick="toggleUserMenu()"
                            class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 transition-all duration-200">
                        <i class="fas fa-user text-sm"></i>
                    </button>

                    <div id="userDropdown"
                         class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-2xl shadow-slate-200/60 border border-gray-100 hidden animate-fade-in-up origin-top-right overflow-hidden z-50">
                        <div class="p-2">
                            <div class="px-3 py-2 border-b border-slate-100 mb-1">
                                <p class="text-xs font-bold text-slate-800"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?></p>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider">Student Account</p>
                            </div>

                            <a href="profile.php"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg transition-colors duration-200">
                                <i class="fas fa-user-circle text-slate-400 w-4"></i> My Profile
                            </a>

                            <a href="<?= BASE_URL ?>/auth/logout_user.php"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200 font-medium">
                                <i class="fas fa-sign-out-alt w-4"></i> Sign Out
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="mobileNavDropdown" class="md:hidden bg-white border-t border-slate-100 shadow-lg hidden">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded-md text-base font-medium <?= $current_page === 'dashboard.php' ? 'text-brand-600 bg-brand-50' : 'text-slate-700 hover:bg-slate-50 hover:text-brand-600' ?>">
                <i class="fas fa-gauge-high w-5 text-sm"></i> Dashboard
            </a>
            <a href="browse.php" class="flex items-center gap-2 px-3 py-2 rounded-md text-base font-medium <?= $current_page === 'browse.php' ? 'text-brand-600 bg-brand-50' : 'text-slate-700 hover:bg-slate-50 hover:text-brand-600' ?>">
                <i class="fas fa-book w-5 text-sm"></i> Browse Books
            </a>
            <a href="my_books.php" class="flex items-center gap-2 px-3 py-2 rounded-md text-base font-medium <?= $current_page === 'my_books.php' ? 'text-brand-600 bg-brand-50' : 'text-slate-700 hover:bg-slate-50 hover:text-brand-600' ?>">
                <i class="fas fa-book-reader w-5 text-sm"></i> My Borrowed Books
            </a>
            <a href="profile.php" class="flex items-center gap-2 px-3 py-2 rounded-md text-base font-medium <?= $current_page === 'profile.php' ? 'text-brand-600 bg-brand-50' : 'text-slate-700 hover:bg-slate-50 hover:text-brand-600' ?>">
                <i class="fas fa-user w-5 text-sm"></i> Profile
            </a>
            <a href="<?= BASE_URL ?>/auth/logout_user.php" class="flex items-center gap-2 px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-red-50">
                <i class="fas fa-sign-out-alt w-5 text-sm"></i> Sign Out
            </a>
        </div>
    </div>
</nav>
<div class="h-16"></div>

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('hidden');
    document.getElementById('mobileNavDropdown').classList.add('hidden');
}

function toggleMobileNav() {
    const mobileNav = document.getElementById('mobileNavDropdown');
    mobileNav.classList.toggle('hidden');
    document.getElementById('userDropdown').classList.add('hidden');
}

document.addEventListener('click', function(e) {
    const userWrapper = document.getElementById('userMenuWrapper');
    const mobileNavBtn = document.getElementById('mobileNavMenuBtn');

    if (userWrapper && !userWrapper.contains(e.target) && (!mobileNavBtn || !mobileNavBtn.contains(e.target))) {
        document.getElementById('userDropdown').classList.add('hidden');
    }

    if (mobileNavBtn && !mobileNavBtn.contains(e.target)) {
        document.getElementById('mobileNavDropdown').classList.add('hidden');
    }
});
</script>

