<?php require_once dirname(dirname(__DIR__)) . "/config/app.php"; ?>
<?php 
$current_page = basename($_SERVER['PHP_SELF']); 

$nav_items = [
    ['name' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'home', 'match' => 'dashboard.php'],
    ['name' => 'Browse', 'url' => 'browse.php', 'icon' => 'library', 'match' => 'browse.php'],
    ['name' => 'My Books', 'url' => 'my_books.php', 'icon' => 'book-open', 'match' => 'my_books.php'],
    ['name' => 'Profile', 'url' => 'profile.php', 'icon' => 'user', 'match' => 'profile.php']
];
?>

<!-- Include Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- Global Loader -->
<div id="global-loader" class="fixed inset-0 z-[9999] bg-white/80 dark:bg-[#0b1220]/80 backdrop-blur-3xl flex items-center justify-center transition-all duration-500 opacity-100 visible">
    <div class="relative flex flex-col items-center justify-center">
        <!-- Glowing Orb -->
        <div class="absolute inset-0 bg-brand-500 rounded-full blur-[70px] opacity-20 animate-pulse"></div>
        <!-- 3D Book Icon -->
        <img src="https://raw.githubusercontent.com/microsoft/fluentui-emoji/main/assets/Orange%20book/3D/orange_book_3d.png" 
             style="filter: hue-rotate(240deg);"
             alt="Loading..." class="w-24 h-24 object-contain drop-shadow-2xl animate-bounce relative z-10">
        <!-- Brand Name -->
        <h2 class="mt-4 text-xl font-black text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-purple-600 tracking-tight animate-pulse relative z-10">
            AuroraLib
        </h2>
    </div>
</div>

<script>
    function hideLoader() {
        const loader = document.getElementById('global-loader');
        if (loader && loader.style.opacity !== '0') {
            loader.style.opacity = '0';
            loader.style.visibility = 'hidden';
            setTimeout(() => loader.remove(), 500);
        }
    }
    // Attempt graceful hide when DOM and assets load
    window.addEventListener('load', hideLoader);
    // Failsafe timeout to force very fast experience
    setTimeout(hideLoader, 800);
</script>

<div class="fixed bottom-0 sm:bottom-auto sm:top-0 left-1/2 -translate-x-1/2 z-50 mb-6 sm:mt-6 sm:pt-6 w-full px-4 sm:px-0 flex justify-center w-max">
    <div class="flex items-center gap-2 sm:gap-3 bg-white/80 border border-slate-200/50 backdrop-blur-lg py-1 px-1 rounded-full shadow-lg dark:bg-[#0b1220]/80 dark:border-slate-800/80">
        
        <?php foreach ($nav_items as $item): ?>
            <?php $isActive = ($current_page === $item['match']); ?>
            <a href="<?= $item['url'] ?>" 
               class="relative cursor-pointer text-sm font-semibold px-4 sm:px-6 py-2 rounded-full transition-colors flex items-center justify-center <?= $isActive ? 'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-500/10' : 'text-slate-600 hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400' ?>">
                
                <span class="hidden md:inline"><?= $item['name'] ?></span>
                <span class="md:hidden"><i data-lucide="<?= $item['icon'] ?>" class="w-5 h-5"></i></span>
                
                <?php if ($isActive): ?>
                    <div class="absolute inset-0 w-full bg-brand-500/5 rounded-full -z-10"></div>
                    <!-- Tubelight Lamp Effect -->
                    <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-8 h-1 bg-brand-500 rounded-t-full z-10">
                        <div class="absolute w-12 h-6 bg-brand-500/30 rounded-full blur-md -top-2 -left-2"></div>
                        <div class="absolute w-8 h-6 bg-brand-500/30 rounded-full blur-md -top-1"></div>
                        <div class="absolute w-4 h-4 bg-brand-500/30 rounded-full blur-sm top-0 left-2"></div>
                    </div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>

        <!-- Theme Toggle Button -->
        <button onclick="toggleTheme()" class="relative cursor-pointer text-sm font-semibold px-3 py-2 rounded-full transition-colors flex items-center justify-center text-slate-600 hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400" title="Toggle Theme">
            <span id="themeToggleIconMobile" class="md:hidden"><i data-lucide="moon" class="w-5 h-5"></i></span>
            <span id="themeToggleIconDesktop" class="hidden md:inline"><i data-lucide="moon" class="w-5 h-5"></i></span>
        </button>

        <!-- Logout Button -->
        <a href="<?= BASE_URL ?>/auth/logout_user.php" class="relative cursor-pointer text-sm font-semibold px-3 py-2 rounded-full transition-colors flex items-center justify-center text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300" title="Sign Out">
            <span class="md:hidden"><i data-lucide="log-out" class="w-5 h-5"></i></span>
            <span class="hidden md:inline"><i data-lucide="log-out" class="w-5 h-5"></i></span>
        </a>

    </div>
</div>

<div class="h-24 sm:h-20"></div>

<script>
function setTheme(mode) {
    const root = document.documentElement;
    if (mode === 'dark') {
        root.classList.add('dark');
        localStorage.setItem('aurora_theme', 'dark');
    } else {
        root.classList.remove('dark');
        localStorage.setItem('aurora_theme', 'light');
    }
    updateThemeToggleUI();
}

function toggleTheme() {
    const isDark = document.documentElement.classList.contains('dark');
    setTheme(isDark ? 'light' : 'dark');
}

function updateThemeToggleUI() {
    const isDark = document.documentElement.classList.contains('dark');
    const desktopIcon = document.getElementById('themeToggleIconDesktop');
    const mobileIcon = document.getElementById('themeToggleIconMobile');

    // Due to Lucide icons dynamically replacing the element, we should reconstruct them if needed, 
    // or just toggle a class. However, creating new lucide markup safely is easier.
    if (desktopIcon) desktopIcon.innerHTML = `<i data-lucide="${isDark ? 'sun' : 'moon'}" class="w-5 h-5"></i>`;
    if (mobileIcon) mobileIcon.innerHTML = `<i data-lucide="${isDark ? 'sun' : 'moon'}" class="w-5 h-5"></i>`;
    lucide.createIcons();
}

// Initialize Lucide Icons
lucide.createIcons();
updateThemeToggleUI();
</script>
