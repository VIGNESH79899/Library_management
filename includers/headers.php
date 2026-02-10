<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>

<div class="glass sticky top-0 z-40 flex items-center justify-between px-8 py-4 shadow-sm border-b border-gray-100/50">
    <div class="flex items-center gap-4">
        <!-- Optional: Mobile Menu Toggle could go here -->
        <div class="flex flex-col">
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Library Management</h1>
            <span class="text-xs text-slate-500 font-medium">Administrator Dashboard</span>
        </div>
    </div>
    
    <div class="flex items-center gap-6">
        <!-- Search placeholder -->
        <div class="relative hidden md:block">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 rounded-full border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none transition-all w-64 text-sm">
        </div>

        <div class="h-8 w-[1px] bg-gray-200 hidden md:block"></div>

        <div class="flex items-center gap-3 group cursor-pointer">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-semibold text-slate-700 leading-tight">Admin User</p>
                <p class="text-xs text-slate-500">System Admin</p>
            </div>
            <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg ring-2 ring-white">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </div>
</div>
