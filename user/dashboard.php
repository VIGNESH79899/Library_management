<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

$user_id = $_SESSION['user_id'];
$user_name = explode(' ', $_SESSION['user_name'])[0];

// Stats Logic
// 1. Books Currently Borrowed
$sql_borrowed = "SELECT COUNT(*) as count FROM issue WHERE Member_ID = ? AND Issue_ID NOT IN (SELECT Issue_ID FROM return_book)";
$stmt = $conn->prepare($sql_borrowed);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$borrowed_count = $stmt->get_result()->fetch_assoc()['count'];

// 2. Total Books Returned
$sql_returned = "SELECT COUNT(*) as count FROM issue I JOIN return_book R ON I.Issue_ID = R.Issue_ID WHERE I.Member_ID = ?";
$stmt = $conn->prepare($sql_returned);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$returned_count = $stmt->get_result()->fetch_assoc()['count'];

// 3. Outstanding Fines (Estimated)
$sql_issues = "SELECT Due_Date FROM issue WHERE Member_ID = ? AND Issue_ID NOT IN (SELECT Issue_ID FROM return_book)";
$stmt = $conn->prepare($sql_issues);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_fine = 0;
while ($row = $result->fetch_assoc()) {
    $due = strtotime($row['Due_Date']);
    $today = time();
    if ($today > $due) {
        $days = floor(($today - $due) / (60 * 60 * 24));
        $total_fine += ($days * 10);
    }
}

// Time based greeting
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in-up">
    
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-[#5f2eea] to-[#8e2de2] rounded-[24px] p-8 md:p-12 mb-10 overflow-hidden shadow-[0_8px_30px_rgba(95,46,234,0.3)]">
        <!-- Abstract Shapes -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-white">
                <p class="text-white/80 font-medium mb-2 tracking-wide uppercase text-sm"><?= date('l, F j, Y') ?></p>
                <h1 class="text-4xl md:text-5xl font-bold mb-4 font-['Inter'] tracking-tight">Good Afternoon, <?= $user_name ?>!</h1>
                <p class="text-white/90 text-lg max-w-xl font-['Inter']">Ready to discover your next favorite book? Explore our collection or manage your current reads.</p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="browse.php" class="bg-white text-[#5f2eea] px-6 py-3 rounded-xl font-bold hover:bg-white/90 transition-all duration-300 shadow-lg shadow-black/10 flex items-center gap-2 hover:-translate-y-1">
                        <i class="fas fa-search"></i> Browse Library
                    </a>
                    <a href="profile.php" class="bg-white/10 backdrop-blur-md border border-white/20 text-white px-6 py-3 rounded-xl font-bold hover:bg-white/20 transition-all duration-300 flex items-center gap-2 hover:-translate-y-1">
                        <i class="fas fa-user-circle"></i> My Profile
                    </a>
                </div>
            </div>
            <div class="hidden md:block animate-[bounce_3s_infinite] relative">
                <i class="fas fa-book-reader text-9xl text-white/30 transform drop-shadow-2xl"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <!-- Stats Section -->
        <div class="lg:col-span-2 space-y-6">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-chart-pie text-indigo-500"></i> Your Activity
            </h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Borrowed -->
                <div class="backdrop-blur-md bg-white/70 p-6 rounded-[24px] shadow-sm border border-slate-100/50 hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] transition-all duration-300 hover:-translate-y-2 group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-full bg-[#5f2eea]/10 text-[#5f2eea] flex items-center justify-center text-xl group-hover:scale-110 transition-transform duration-300 group-hover:bg-[#5f2eea] group-hover:text-white">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <span class="text-xs font-bold bg-[#5f2eea]/10 text-[#5f2eea] px-3 py-1.5 rounded-full">Active</span>
                    </div>
                    <p class="text-4xl font-extrabold text-slate-800 mb-1 tracking-tight font-['Inter']"><?= $borrowed_count ?></p>
                    <p class="text-slate-500 text-sm font-medium font-['Inter']">Books Borrowed</p>
                </div>
                
                <!-- Returned -->
                <div class="backdrop-blur-md bg-white/70 p-6 rounded-[24px] shadow-sm border border-slate-100/50 hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] transition-all duration-300 hover:-translate-y-2 group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform duration-300 group-hover:bg-emerald-500 group-hover:text-white">
                            <i class="fas fa-history"></i>
                        </div>
                        <span class="text-xs font-bold bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full">Lifetime</span>
                    </div>
                    <p class="text-4xl font-extrabold text-slate-800 mb-1 tracking-tight font-['Inter']"><?= $returned_count ?></p>
                    <p class="text-slate-500 text-sm font-medium font-['Inter']">Books Returned</p>
                </div>

                <!-- Fines -->
                <div class="backdrop-blur-md bg-white/70 p-6 rounded-[24px] shadow-sm border border-slate-100/50 hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] transition-all duration-300 hover:-translate-y-2 group relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-bl from-<?= $total_fine > 0 ? 'red' : 'emerald' ?>-50/50 to-transparent rounded-bl-full -mr-4 -mt-4"></div>
                    <div class="flex justify-between items-start mb-4 relative z-10">
                        <div class="w-12 h-12 rounded-full <?= $total_fine > 0 ? 'bg-red-50 text-red-600 group-hover:bg-red-500' : 'bg-emerald-50 text-emerald-500 group-hover:bg-emerald-500' ?> flex items-center justify-center text-xl group-hover:scale-110 transition-transform duration-300 group-hover:text-white">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                         <?php if ($total_fine > 0): ?>
                            <span class="text-xs font-bold bg-red-100 text-red-600 px-3 py-1.5 rounded-full animate-pulse">Unpaid</span>
                        <?php else: ?>
                            <span class="text-xs font-bold bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full">All Clear</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-4xl font-extrabold <?= $total_fine > 0 ? 'text-red-500' : 'text-slate-800' ?> mb-1 tracking-tight font-['Inter']">₹<?= $total_fine ?></p>
                    <p class="text-slate-500 text-sm font-medium font-['Inter']">Estimated Fines</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions Sidebar -->
        <div>
             <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2 mb-6">
                <i class="fas fa-bolt text-yellow-500"></i> Quick Actions
            </h2>
            <div class="backdrop-blur-md bg-white/70 rounded-[24px] shadow-sm border border-slate-100/50 p-3">
                <a href="browse.php" class="flex items-center gap-4 p-3 hover:bg-slate-50/50 rounded-xl transition-colors group">
                    <div class="w-12 h-12 rounded-full bg-[#5f2eea]/10 text-[#5f2eea] flex items-center justify-center group-hover:bg-[#5f2eea] group-hover:text-white transition-all duration-300">
                        <i class="fas fa-search"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-700 text-sm font-['Inter']">Find a Book</p>
                        <p class="text-xs text-slate-500 font-['Inter']">Search the catalog</p>
                    </div>
                    <i class="fas fa-chevron-right ml-auto text-slate-300 text-xs group-hover:text-[#5f2eea] transition-colors"></i>
                </a>
                <a href="profile.php" class="flex items-center gap-4 p-3 hover:bg-slate-50 rounded-xl transition-colors group">
                    <div class="w-10 h-10 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center group-hover:bg-pink-600 group-hover:text-white transition-colors">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-700 text-sm">Update Profile</p>
                        <p class="text-xs text-slate-400">Change password or info</p>
                    </div>
                     <i class="fas fa-chevron-right ml-auto text-slate-300 text-xs"></i>
                </a>
                <a href="history.php" class="flex items-center gap-4 p-3 hover:bg-slate-50 rounded-xl transition-colors group">
                    <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center group-hover:bg-teal-600 group-hover:text-white transition-colors">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-700 text-sm">Liked Books</p>
                        <p class="text-xs text-slate-400">Your reading history</p>
                    </div>
                     <i class="fas fa-chevron-right ml-auto text-slate-300 text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- New Arrivals Section -->
    <div>
        <div class="flex justify-between items-end mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-sparkles text-amber-400"></i> New Arrivals
                </h2>
                <p class="text-slate-500 text-sm mt-1">Fresh additions to our collection this week.</p>
            </div>
            <a href="browse.php" class="group flex items-center gap-2 text-indigo-600 font-semibold text-sm hover:text-indigo-700">
                View All <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            $recent_books = $conn->query("SELECT B.*, C.Category_Name as Category FROM book B LEFT JOIN category C ON B.Category_ID = C.Category_ID WHERE B.Status='Available' ORDER BY B.Book_ID DESC LIMIT 4");
            if ($recent_books->num_rows > 0) {
                while ($book = $recent_books->fetch_assoc()) {
            ?>
            <div class="group backdrop-blur-md bg-white/80 rounded-[18px] border border-slate-100/50 overflow-hidden hover:shadow-[0_8px_30px_rgba(95,46,234,0.15)] transition-all duration-300 hover:-translate-y-2 h-full flex flex-col relative" data-book-id="<?= $book['Book_ID'] ?>">
                <div class="h-48 relative overflow-hidden flex items-center justify-center bg-slate-100">
                    <img src="https://picsum.photos/seed/<?= $book['Book_ID'] ?>/400/600" alt="Book Cover" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity duration-300"></div>
                    
                    <div class="absolute top-3 left-3 z-20">
                         <span class="bg-white/95 backdrop-blur-md text-[10px] font-bold px-3 py-1.5 rounded-full shadow-sm text-[#5f2eea] uppercase tracking-widest font-['Inter'] flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#5f2eea] animate-pulse"></span> New</span>
                    </div>

                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-sm z-30 bg-black/20">
                        <a href="browse.php?take=<?= $book['Book_ID'] ?>" class="bg-gradient-to-r from-[#5f2eea] to-[#8e2de2] text-white font-bold px-6 py-2.5 rounded-full shadow-[0_4px_15px_rgba(95,46,234,0.3)] transform translate-y-4 group-hover:translate-y-0 transition-all duration-300 hover:scale-105 font-['Inter']">
                            Borrow Now
                        </a>
                    </div>
                </div>
                <div class="p-5 flex-1 flex flex-col relative z-20 bg-white/50 backdrop-blur-sm">
                    <div class="mb-2">
                        <span class="text-[10px] font-bold text-[#5f2eea] uppercase tracking-wider font-['Inter']"><?= $book['Category'] ?? 'General' ?></span>
                    </div>
                    <h3 class="font-bold text-slate-800 leading-snug mb-1 line-clamp-2 hover:text-[#5f2eea] transition-colors font-['Inter']" title="<?= $book['Title'] ?>"><?= $book['Title'] ?></h3>
                    <p class="text-slate-500 text-xs mb-4 font-['Inter']">by <span class="text-slate-700"><?= htmlspecialchars($book['Author']) ?></span></p>
                </div>
            </div>
            <?php 
                } 
            } else {
            ?>
                <div class="col-span-full py-8 text-center text-slate-400 italic">
                    No new arrivals at the moment.
                </div>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>
