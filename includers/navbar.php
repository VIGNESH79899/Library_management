<?php
// Fetch admin details if not already fetched
$admin_details = null;
if (isset($_SESSION['admin'])) {
    if (isset($conn)) {
        $stmt_nav = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt_nav->bind_param("s", $_SESSION['admin']);
        $stmt_nav->execute();
        $admin_details = $stmt_nav->get_result()->fetch_assoc();
    }
}

require_once dirname(__DIR__) . "/config/app.php";
// Use absolute paths to ensure links work regardless of current URL or folder name typos
// This penetrates through localhost/Library-management and production/ root.
$base_url = BASE_URL;
$path_to_dashboard = $base_url . "/dashboard/dashboard.php";
$path_to_profile = $base_url . "/profile/index.php";
$path_to_logout = $base_url . "/auth/logout.php";
$path_to_settings = "#"; // Placeholder
?>
<div class="sticky top-0 z-40 flex items-center justify-between px-6 md:px-10 py-5 bg-white/70 backdrop-blur-2xl border-b border-indigo-50/60 shadow-[0_4px_30px_rgba(0,0,0,0.02)] transition-all duration-300">
    <div class="flex items-center gap-4 md:gap-6">
        <!-- Mobile Menu Toggle -->
        <button id="mobileMenuBtn" class="md:hidden text-slate-400 hover:text-indigo-600 focus:outline-none transition-all p-2 -ml-2 rounded-xl hover:bg-indigo-50 hover:shadow-inner">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <div class="flex items-center gap-3 animate-enter delay-100 relative group cursor-default">
            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-indigo-50 to-white shadow-inner border border-indigo-100/50 flex items-center justify-center text-indigo-600 transition-all duration-500 overflow-hidden relative">
                <i class="fas fa-book-open text-lg relative z-10 group-hover:scale-110 transition-transform duration-500"></i>
            </div>
            <div class="flex flex-col">
                <h1 class="text-xl md:text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-800 to-indigo-900 tracking-tight font-inter leading-none mb-1">Library Management</h1>
                <span class="text-[0.65rem] md:text-xs text-indigo-500 font-bold tracking-[0.2em] uppercase">Administrator Portal</span>
            </div>
        </div>
    </div>
    
    <div class="flex items-center gap-5 md:gap-8 animate-enter delay-200">
        <!-- Search Bar -->
        <div class="relative hidden lg:block group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400 text-sm group-focus-within:text-indigo-500 transition-colors"></i>
            </div>
            <input type="text" placeholder="Search commands, books, users..." 
                   class="pl-11 pr-14 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 focus:bg-white focus:border-indigo-300 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all w-72 focus:w-96 text-sm font-semibold text-slate-700 placeholder-slate-400 shadow-inner">
            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex gap-1 pointer-events-none">
                <span class="text-[10px] font-bold bg-white border border-gray-200 shadow-sm rounded-md px-1.5 py-0.5 text-slate-400">Ctrl</span>
                <span class="text-[10px] font-bold bg-white border border-gray-200 shadow-sm rounded-md px-1.5 py-0.5 text-slate-400">K</span>
            </div>
        </div>

        <div class="h-8 w-[2px] bg-slate-100 hidden md:block rounded-full"></div>

        <!-- Notification Bell -->
        <button class="relative p-2 text-slate-400 hover:text-indigo-600 transition-colors rounded-xl hover:bg-indigo-50 group">
            <i class="far fa-bell text-xl group-hover:animate-swing"></i>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-pink-500 rounded-full border-2 border-white shadow-sm"></span>
        </button>

        <!-- Profile Dropdown -->
        <div class="flex items-center gap-4 group cursor-pointer relative pl-2 md:pl-0">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-extrabold text-slate-800 leading-tight group-hover:text-indigo-600 transition-colors">
                    <?= $admin_details ? htmlspecialchars($admin_details['Full_Name'] ?? 'Admin User') : 'Admin User' ?>
                </p>
                <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-0.5 opacity-80">
                    <?= (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'staff') ? 'Staff Member' : 'System Admin' ?>
                </p>
            </div>
            <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-black shadow-[0_4px_15px_rgba(99,102,241,0.3)] transition-all duration-300 transform group-hover:-translate-y-1 group-hover:shadow-[0_8px_25px_rgba(99,102,241,0.4)] group-active:translate-y-0 border-2 border-white">
                <span class="text-sm">
                    <?php
                        $name = $admin_details['Full_Name'] ?? 'Admin User';
                        $parts = explode(' ', $name);
                        echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                    ?>
                </span>
            </div>
            
            <!-- Dropdown Menu -->
            <div class="absolute top-full right-0 mt-5 w-64 bg-white rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.08)] border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-right scale-95 group-hover:scale-100 z-50 p-2">
                <div class="px-4 py-3 bg-slate-50/50 rounded-xl mb-2">
                    <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-0.5">Signed in as</p>
                    <p class="text-sm font-extrabold text-slate-800 truncate">
                        <?= $admin_details ? htmlspecialchars($admin_details['Email'] ?? 'admin@library.com') : 'admin@library.com' ?>
                    </p>
                </div>
                <div class="space-y-1">
                    <a href="<?= $path_to_profile ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-all font-semibold mx-1">
                        <i class="fas fa-user-circle w-5 text-center text-indigo-400"></i> Profile
                    </a>
                    <a href="<?= $path_to_settings ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-indigo-600 rounded-xl transition-all font-semibold mx-1">
                        <i class="fas fa-cog w-5 text-center text-slate-400"></i> Settings
                    </a>
                </div>
                <div class="h-[1px] bg-slate-100 my-2"></div>
                <a href="<?= $path_to_logout ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 rounded-xl transition-all font-semibold mx-1 group/logout">
                    <i class="fas fa-sign-out-alt w-5 text-center text-red-400 group-hover/logout:text-red-500"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>



