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

// Use absolute paths to ensure links work regardless of current URL or folder name typos
// This fixes issues where user might be at 'Library%20management' instead of 'Library-management'
$base_url = "/Library-management";
$path_to_dashboard = $base_url . "/dashboard/dashboard.php";
$path_to_profile = $base_url . "/profile/index.php";
$path_to_logout = $base_url . "/auth/logout.php";
$path_to_settings = "#"; // Placeholder
?>
<div class="sticky top-0 z-40 flex items-center justify-between px-8 py-4 bg-white/80 backdrop-blur-xl border-b border-gray-200/50 transition-all duration-300">
    <div class="flex items-center gap-4">
        <a href="<?= $path_to_dashboard ?>" class="flex flex-col animate-enter delay-100 hover:opacity-80 transition-opacity">
            <h1 class="text-xl font-bold text-slate-800 tracking-tight font-inter">Library Management</h1>
            <span class="text-xs text-slate-500 font-medium tracking-wide">Administrator Dashboard</span>
        </a>
    </div>
    
    <div class="flex items-center gap-6 animate-enter delay-200">
        <!-- Search Bar -->
        <div class="relative hidden md:block group">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-sm group-focus-within:text-indigo-500 transition-colors"></i>
            <input type="text" placeholder="Type to search..." 
                   class="pl-10 pr-4 py-2.5 rounded-xl border border-transparent bg-slate-100 focus:bg-white focus:border-indigo-200 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all w-64 focus:w-80 text-sm font-medium text-slate-600 placeholder-slate-400">
            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex gap-1 pointer-events-none opacity-50">
                <span class="text-[10px] font-bold bg-white border border-gray-200 rounded px-1.5 py-0.5 text-slate-400">Ctrl</span>
                <span class="text-[10px] font-bold bg-white border border-gray-200 rounded px-1.5 py-0.5 text-slate-400">K</span>
            </div>
        </div>

        <div class="h-8 w-[1px] bg-slate-200 hidden md:block"></div>

        <!-- Profile Dropdown -->
        <div class="flex items-center gap-3 group cursor-pointer relative">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-semibold text-slate-700 leading-tight">
                    <?= $admin_details ? htmlspecialchars($admin_details['Full_Name'] ?? 'Admin User') : 'Admin User' ?>
                </p>
                <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider bg-indigo-50 inline-block px-1.5 py-0.5 rounded mt-0.5">System Admin</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold shadow-lg shadow-slate-200 transition-transform transform group-hover:scale-105 group-active:scale-95">
                <span class="text-sm">
                    <?php
                        $name = $admin_details['Full_Name'] ?? 'Admin User';
                        $parts = explode(' ', $name);
                        echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                    ?>
                </span>
            </div>
            
            <!-- Dropdown Menu -->
            <div class="absolute top-full right-0 mt-4 w-56 bg-white rounded-2xl shadow-xl shadow-slate-200 border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right z-50 p-2">
                <div class="px-3 py-2 border-b border-gray-100 mb-1">
                    <p class="text-xs text-slate-500 font-medium">Signed in as</p>
                    <p class="text-sm font-bold text-slate-800 truncate">
                        <?= $admin_details ? htmlspecialchars($admin_details['Email'] ?? 'admin@library.com') : 'admin@library.com' ?>
                    </p>
                </div>
                <a href="<?= $path_to_profile ?>" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-indigo-600 rounded-lg transition-colors font-medium">
                    <i class="fas fa-user-circle w-4 text-center"></i> Profile
                </a>
                <a href="<?= $path_to_settings ?>" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-indigo-600 rounded-lg transition-colors font-medium">
                    <i class="fas fa-cog w-4 text-center"></i> Settings
                </a>
                <div class="h-[1px] bg-gray-100 my-1"></div>
                <a href="<?= $path_to_logout ?>" class="flex items-center gap-2 px-3 py-2 text-sm text-red-500 hover:bg-red-50 rounded-lg transition-colors font-medium">
                    <i class="fas fa-sign-out-alt w-4 text-center"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>
