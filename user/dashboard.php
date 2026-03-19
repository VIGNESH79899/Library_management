<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

$user_id = $_SESSION['user_id'];
$user_name = explode(' ', $_SESSION['user_name'])[0];

// 1. Books currently borrowed
$sql_borrowed = "SELECT COUNT(*) as count FROM issue WHERE Member_ID = ? AND Issue_ID NOT IN (SELECT Issue_ID FROM return_book)";
$stmt = $conn->prepare($sql_borrowed);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$borrowed_count = (int) $stmt->get_result()->fetch_assoc()['count'];

// 2. Total books returned
$sql_returned = "SELECT COUNT(*) as count FROM issue I JOIN return_book R ON I.Issue_ID = R.Issue_ID WHERE I.Member_ID = ?";
$stmt = $conn->prepare($sql_returned);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$returned_count = (int) $stmt->get_result()->fetch_assoc()['count'];

// 3. Lifetime books borrowed by user
$sql_user_borrowed = "SELECT COUNT(*) as count FROM issue WHERE Member_ID = ?";
$stmt = $conn->prepare($sql_user_borrowed);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_borrowed_total = (int) $stmt->get_result()->fetch_assoc()['count'];

// 4. Total books in library
$total_books_count = 0;
$total_books_res = $conn->query("SELECT COUNT(*) as count FROM book");
if ($total_books_res) {
    $total_books_count = (int) $total_books_res->fetch_assoc()['count'];
}

// 5. Recommended books count from user's previously borrowed categories
$recommended_count = 0;
$sql_recommended = "
    SELECT COUNT(*) as count
    FROM book
    WHERE Status = 'Available'
      AND Category_ID IN (
          SELECT DISTINCT B.Category_ID
          FROM issue I
          JOIN book B ON I.Book_ID = B.Book_ID
          WHERE I.Member_ID = ?
      )";
$stmt = $conn->prepare($sql_recommended);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recommended_count = (int) $stmt->get_result()->fetch_assoc()['count'];

if ($recommended_count === 0) {
    $fallback_rec = $conn->query("SELECT COUNT(*) as count FROM book WHERE Status = 'Available'");
    if ($fallback_rec) {
        $recommended_count = (int) $fallback_rec->fetch_assoc()['count'];
    }
}

// 6. Outstanding estimated fine
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

$total_activity = $borrowed_count + $returned_count;
$progress_percent = $total_activity > 0 ? (int) round(($returned_count / $total_activity) * 100) : 0;

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
    <div class="relative bg-gradient-to-br from-[#5f2eea] via-[#7a2ce8] to-[#9d2be0] rounded-[32px] p-8 sm:p-10 lg:p-12 mb-10 overflow-hidden shadow-[0_12px_40px_-10px_rgba(95,46,234,0.5)] transition-all duration-500 hover:shadow-[0_16px_50px_-10px_rgba(95,46,234,0.6)]">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/10 rounded-full blur-[80px] -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-indigo-300/20 rounded-full blur-[60px] translate-y-1/2 -translate-x-1/4 pointer-events-none"></div>
        <div class="absolute top-1/2 right-1/4 w-32 h-32 bg-purple-300/30 rounded-full blur-[40px] -translate-y-1/2 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-8 lg:items-center lg:justify-between">
            <div class="text-white">
                <p class="text-white/80 font-medium mb-2 tracking-wide uppercase text-xs sm:text-sm"><?= date('l, F j, Y') ?></p>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-3 tracking-tight"><?= $greeting ?>, <?= htmlspecialchars($user_name) ?>!</h1>
                <p class="text-white/90 text-base sm:text-lg max-w-xl">Track your reading progress, discover popular picks, and keep learning every day.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="browse.php" class="bg-white text-[#5f2eea] px-5 py-2.5 rounded-xl font-bold transition-all duration-200 shadow-lg shadow-black/10 hover:-translate-y-1 hover:shadow-[0_10px_24px_rgba(0,0,0,0.12)]">
                        <i class="fas fa-search mr-1.5"></i> Browse Library
                    </a>
                    <a href="my_books.php" class="bg-white/10 border border-white/20 text-white px-5 py-2.5 rounded-xl font-bold transition-all duration-200 hover:bg-white/20 hover:-translate-y-1">
                        <i class="fas fa-book mr-1.5"></i> My Books
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full lg:max-w-xl">
                <div class="bg-white/10 border border-white/20 backdrop-blur-xl rounded-[20px] p-5 shadow-lg shadow-black/5 hover:bg-white/15 transition-all duration-300">
                    <p class="text-white/80 text-[11px] font-semibold uppercase tracking-widest mb-1">Total Books</p>
                    <p class="text-white text-3xl font-extrabold"><?= $total_books_count ?></p>
                </div>
                <div class="bg-white/10 border border-white/20 backdrop-blur-xl rounded-[20px] p-5 shadow-lg shadow-black/5 hover:bg-white/15 transition-all duration-300">
                    <p class="text-white/80 text-[11px] font-semibold uppercase tracking-widest mb-1">Borrowed By You</p>
                    <p class="text-white text-3xl font-extrabold"><?= $user_borrowed_total ?></p>
                </div>
                <div class="bg-white/10 border border-white/20 backdrop-blur-xl rounded-[20px] p-5 shadow-lg shadow-black/5 hover:bg-white/15 transition-all duration-300">
                    <p class="text-white/80 text-[11px] font-semibold uppercase tracking-widest mb-1">Recommended</p>
                    <p class="text-white text-3xl font-extrabold"><?= $recommended_count ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <div class="lg:col-span-2">
            <div class="premium-card p-6 sm:p-8">
                <div class="flex items-center justify-between gap-3 mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800 flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        Learning Summary
                    </h2>
                    <span class="text-xs font-bold px-4 py-1.5 rounded-full <?= $progress_percent >= 60 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-amber-50 text-amber-600 border border-amber-100' ?>">
                        <?= $progress_percent ?>% Complete
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
                    <div class="rounded-2xl p-5 bg-white border border-brand-100 shadow-[0_4px_20px_-4px_rgba(99,102,241,0.1)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_8px_24px_-4px_rgba(99,102,241,0.15)] relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 p-4 opacity-5 group-hover:opacity-10 transition-opacity duration-300">
                            <i class="fas fa-book-reader text-7xl text-brand-500"></i>
                        </div>
                        <p class="text-[11px] font-bold text-brand-600 uppercase tracking-widest mb-1.5 relative z-10">Books Borrowed</p>
                        <p class="text-4xl font-extrabold text-slate-800 relative z-10"><?= $borrowed_count ?></p>
                    </div>
                    <div class="rounded-2xl p-5 bg-white border border-emerald-100 shadow-[0_4px_20px_-4px_rgba(16,185,129,0.1)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_8px_24px_-4px_rgba(16,185,129,0.15)] relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 p-4 opacity-5 group-hover:opacity-10 transition-opacity duration-300">
                            <i class="fas fa-check-circle text-7xl text-emerald-500"></i>
                        </div>
                        <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-widest mb-1.5 relative z-10">Books Returned</p>
                        <p class="text-4xl font-extrabold text-slate-800 relative z-10"><?= $returned_count ?></p>
                    </div>
                    <div class="rounded-2xl p-5 bg-white border <?= $total_fine > 0 ? 'border-red-100 shadow-[0_4px_20px_-4px_rgba(239,68,68,0.1)] hover:shadow-[0_8px_24px_-4px_rgba(239,68,68,0.15)]' : 'border-teal-100 shadow-[0_4px_20px_-4px_rgba(20,184,166,0.1)] hover:shadow-[0_8px_24px_-4px_rgba(20,184,166,0.15)]' ?> transition-all duration-300 hover:-translate-y-1 relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 p-4 opacity-5 group-hover:opacity-10 transition-opacity duration-300">
                            <i class="fas <?= $total_fine > 0 ? 'fa-exclamation-circle text-red-500' : 'fa-shield-alt text-teal-500' ?> text-7xl"></i>
                        </div>
                        <p class="text-[11px] font-bold <?= $total_fine > 0 ? 'text-red-500' : 'text-teal-600' ?> uppercase tracking-widest mb-1.5 relative z-10">Estimated Fines</p>
                        <p class="text-4xl font-extrabold <?= $total_fine > 0 ? 'text-red-500' : 'text-slate-800' ?> relative z-10">&#8377;<?= $total_fine ?></p>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-bold text-slate-700">Reading Progress <span class="text-slate-400 font-medium ml-1">(Borrowed vs Returned)</span></p>
                        <p class="text-xs font-bold text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-100"><?= $returned_count ?> / <?= max(1, $total_activity) ?></p>
                    </div>
                    <div class="h-3.5 rounded-full bg-slate-100 overflow-hidden shadow-inner border border-slate-200/50">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#5f2eea] via-[#7d2ae8] to-[#9d2be0] transition-all duration-1000 relative" style="width: <?= $progress_percent ?>%;">
                            <div class="absolute inset-0 bg-white/20" style="background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent); background-size: 1rem 1rem;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="premium-card p-6">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center text-brand-500">
                        <i class="fas fa-eye"></i>
                    </div>
                    Recently Viewed
                </h3>
                <div id="recentlyViewedBooks" class="space-y-3"></div>
            </div>

            <div>
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-bolt text-amber-400"></i> Quick Actions
                </h2>
                <div class="premium-card p-3">
                    <a href="browse.php" class="flex items-center gap-4 p-3 hover:bg-brand-50/50 rounded-xl transition-all duration-300 border border-transparent hover:border-brand-100 group">
                        <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center transition-all duration-300 group-hover:bg-brand-600 group-hover:text-white group-hover:shadow-md group-hover:shadow-indigo-200">
                            <i class="fas fa-search"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm group-hover:text-brand-700 transition-colors">Find a Book</p>
                            <p class="text-xs text-slate-500 mt-0.5">Search the catalog</p>
                        </div>
                        <div class="ml-auto w-8 h-8 rounded-full flex items-center justify-center bg-white border border-slate-100 group-hover:border-indigo-200 group-hover:bg-brand-50 transition-all">
                            <i class="fas fa-chevron-right text-slate-400 text-xs group-hover:text-brand-600"></i>
                        </div>
                    </a>
                    <a href="profile.php" class="flex items-center gap-4 p-3 hover:bg-pink-50/50 rounded-xl transition-all duration-300 border border-transparent hover:border-pink-100 group">
                        <div class="w-12 h-12 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center transition-all duration-300 group-hover:bg-pink-600 group-hover:text-white group-hover:shadow-md group-hover:shadow-pink-200">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm group-hover:text-pink-700 transition-colors">Update Profile</p>
                            <p class="text-xs text-slate-500 mt-0.5">Manage your account</p>
                        </div>
                        <div class="ml-auto w-8 h-8 rounded-full flex items-center justify-center bg-white border border-slate-100 group-hover:border-pink-200 group-hover:bg-pink-50 transition-all">
                            <i class="fas fa-chevron-right text-slate-400 text-xs group-hover:text-pink-600"></i>
                        </div>
                    </a>
                    <a href="my_books.php" class="flex items-center gap-4 p-3 hover:bg-emerald-50/50 rounded-xl transition-all duration-300 border border-transparent hover:border-emerald-100 group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center transition-all duration-300 group-hover:bg-emerald-600 group-hover:text-white group-hover:shadow-md group-hover:shadow-emerald-200">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm group-hover:text-emerald-700 transition-colors">My Borrowed Books</p>
                            <p class="text-xs text-slate-500 mt-0.5">Return or renew books</p>
                        </div>
                        <div class="ml-auto w-8 h-8 rounded-full flex items-center justify-center bg-white border border-slate-100 group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all">
                            <i class="fas fa-chevron-right text-slate-400 text-xs group-hover:text-emerald-600"></i>
                        </div>
                    </a>
                </div>
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
            <a href="browse.php" class="group flex items-center gap-2 text-brand-600 font-semibold text-sm hover:text-brand-700">
                View All <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            $recent_books = $conn->query("SELECT B.*, C.Category_Name as Category FROM book B LEFT JOIN category C ON B.Category_ID = C.Category_ID WHERE B.Status='Available' ORDER BY B.Book_ID DESC LIMIT 4");
            if ($recent_books && $recent_books->num_rows > 0) {
                while ($book = $recent_books->fetch_assoc()) {
                    $gradients = [
                        'from-brand-500 to-purple-600',
                        'from-emerald-400 to-cyan-500',
                        'from-rose-400 to-red-500',
                        'from-amber-400 to-orange-500',
                        'from-brand-500 to-brand-600',
                        'from-fuchsia-500 to-pink-600',
                        'from-teal-400 to-emerald-500',
                        'from-violet-500 to-fuchsia-500'
                    ];
                    $gradient = $gradients[$book['Book_ID'] % count($gradients)];
            ?>
            <div class="group bg-white rounded-[20px] border border-slate-100 shadow-sm hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.08)] transition-all duration-500 hover:-translate-y-2 h-full flex flex-col relative">
                <div class="h-44 relative overflow-hidden flex items-center justify-center bg-gradient-to-br <?= $gradient ?>">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-slate-900/20 to-transparent"></div>
                    <span class="relative z-10 bg-white/95 text-[10px] font-bold px-3 py-1.5 rounded-full text-[#5f2eea] uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#5f2eea] animate-pulse"></span> New
                    </span>
                </div>
                <div class="p-5 flex-1 flex flex-col bg-white/60">
                    <span class="text-[10px] font-bold text-[#5f2eea] uppercase tracking-wider"><?= htmlspecialchars($book['Category'] ?? 'General') ?></span>
                    <h3 class="font-bold text-slate-800 leading-snug mt-2 mb-1 line-clamp-2"><?= htmlspecialchars($book['Title']) ?></h3>
                    <p class="text-slate-500 text-xs mb-4">by <span class="text-slate-700"><?= htmlspecialchars($book['Author']) ?></span></p>
                    <a href="browse.php?search=<?= urlencode($book['Title']) ?>" class="mt-auto inline-flex items-center justify-center gap-2 bg-[#5f2eea]/10 text-[#5f2eea] font-bold px-4 py-2 rounded-xl hover:bg-gradient-to-r hover:from-[#5f2eea] hover:to-[#8e2de2] hover:text-white transition-all duration-200">
                        <i class="fas fa-book-open"></i> View Book
                    </a>
                </div>
            </div>
            <?php
                }
            } else {
            ?>
                <div class="col-span-full py-8 text-center text-slate-400 italic">No new arrivals at the moment.</div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
const recentContainer = document.getElementById('recentlyViewedBooks');
const RECENT_KEY = 'aurora_recently_viewed_books';

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value || '';
    return div.innerHTML;
}

function renderRecentlyViewed() {
    if (!recentContainer) return;

    let books = [];
    try {
        books = JSON.parse(localStorage.getItem(RECENT_KEY) || '[]');
    } catch (e) {
        books = [];
    }

    if (!Array.isArray(books) || books.length === 0) {
        recentContainer.innerHTML = '<p class="text-sm text-slate-500 bg-slate-50 border border-slate-100 rounded-xl p-4">No recently viewed books yet. Explore the catalog to build your list.</p>';
        return;
    }

    recentContainer.innerHTML = books.slice(0, 3).map((book) => `
        <a href="browse.php?search=${encodeURIComponent(book.title || '')}" class="block p-4 rounded-[16px] border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-brand-100 hover:shadow-md hover:shadow-brand-100/50 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-1">
                <p class="text-[10px] font-bold text-brand-600 uppercase tracking-widest">${escapeHtml(book.category || 'General')}</p>
                <i class="fas fa-arrow-right text-slate-300 text-xs opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-brand-500 transition-all"></i>
            </div>
            <p class="text-sm font-bold text-slate-800 line-clamp-1 group-hover:text-brand-700 transition-colors">${escapeHtml(book.title || '')}</p>
            <p class="text-xs text-slate-500 mt-1">by <span class="text-slate-700 font-medium">${escapeHtml(book.author || 'Unknown')}</span></p>
        </a>
    `).join('');
}

renderRecentlyViewed();
</script>

</body>
</html>

