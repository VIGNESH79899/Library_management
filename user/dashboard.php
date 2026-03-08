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
    <div class="relative bg-gradient-to-br from-[#5f2eea] to-[#8e2de2] rounded-[24px] p-8 md:p-10 mb-10 overflow-hidden shadow-[0_8px_30px_rgba(95,46,234,0.3)]">
        <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3"></div>

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

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full lg:max-w-xl">
                <div class="bg-white/10 border border-white/15 backdrop-blur-md rounded-2xl p-4">
                    <p class="text-white/70 text-xs uppercase tracking-wider">Total Books</p>
                    <p class="text-white text-2xl font-extrabold mt-1"><?= $total_books_count ?></p>
                </div>
                <div class="bg-white/10 border border-white/15 backdrop-blur-md rounded-2xl p-4">
                    <p class="text-white/70 text-xs uppercase tracking-wider">Borrowed By You</p>
                    <p class="text-white text-2xl font-extrabold mt-1"><?= $user_borrowed_total ?></p>
                </div>
                <div class="bg-white/10 border border-white/15 backdrop-blur-md rounded-2xl p-4">
                    <p class="text-white/70 text-xs uppercase tracking-wider">Recommended</p>
                    <p class="text-white text-2xl font-extrabold mt-1"><?= $recommended_count ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[22px] p-6 sm:p-7 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between gap-3 mb-5">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-chart-line text-indigo-500"></i> Learning Summary
                    </h2>
                    <span class="text-xs font-bold px-3 py-1 rounded-full <?= $progress_percent >= 60 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                        <?= $progress_percent ?>% Complete
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="rounded-2xl p-4 bg-gradient-to-br from-indigo-50 to-indigo-100/60 border border-indigo-100">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Books Borrowed</p>
                        <p class="text-3xl font-extrabold text-slate-800 mt-1"><?= $borrowed_count ?></p>
                    </div>
                    <div class="rounded-2xl p-4 bg-gradient-to-br from-emerald-50 to-emerald-100/60 border border-emerald-100">
                        <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Books Returned</p>
                        <p class="text-3xl font-extrabold text-slate-800 mt-1"><?= $returned_count ?></p>
                    </div>
                    <div class="rounded-2xl p-4 bg-gradient-to-br <?= $total_fine > 0 ? 'from-red-50 to-orange-100/60 border-red-100' : 'from-teal-50 to-teal-100/60 border-teal-100' ?> border">
                        <p class="text-xs font-semibold <?= $total_fine > 0 ? 'text-red-600' : 'text-teal-600' ?> uppercase tracking-wider">Estimated Fines</p>
                        <p class="text-3xl font-extrabold <?= $total_fine > 0 ? 'text-red-600' : 'text-slate-800' ?> mt-1">&#8377;<?= $total_fine ?></p>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-slate-700">Reading Progress (Borrowed vs Returned)</p>
                        <p class="text-sm font-bold text-slate-700"><?= $returned_count ?> / <?= max(1, $total_activity) ?></p>
                    </div>
                    <div class="h-3 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#5f2eea] to-[#8e2de2] transition-all duration-500" style="width: <?= $progress_percent ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-[22px] p-5 border border-slate-100 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-eye text-indigo-500"></i> Recently Viewed Books
                </h3>
                <div id="recentlyViewedBooks" class="space-y-3"></div>
            </div>

            <div>
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-bolt text-yellow-500"></i> Quick Actions
                </h2>
                <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-3">
                    <a href="browse.php" class="flex items-center gap-4 p-3 hover:bg-slate-50 rounded-xl transition-all duration-200 group">
                        <div class="w-11 h-11 rounded-xl bg-[#5f2eea]/10 text-[#5f2eea] flex items-center justify-center transition-all duration-200 group-hover:bg-[#5f2eea] group-hover:text-white">
                            <i class="fas fa-search"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-700 text-sm">Find a Book</p>
                            <p class="text-xs text-slate-500">Search the catalog</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-slate-300 text-xs group-hover:text-[#5f2eea] transition-colors"></i>
                    </a>
                    <a href="profile.php" class="flex items-center gap-4 p-3 hover:bg-slate-50 rounded-xl transition-all duration-200 group">
                        <div class="w-11 h-11 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center transition-all duration-200 group-hover:bg-pink-600 group-hover:text-white">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-700 text-sm">Update Profile</p>
                            <p class="text-xs text-slate-500">Manage your account</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-slate-300 text-xs group-hover:text-pink-500 transition-colors"></i>
                    </a>
                    <a href="my_books.php" class="flex items-center gap-4 p-3 hover:bg-slate-50 rounded-xl transition-all duration-200 group">
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center transition-all duration-200 group-hover:bg-emerald-600 group-hover:text-white">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-700 text-sm">My Borrowed Books</p>
                            <p class="text-xs text-slate-500">Return or renew books</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-slate-300 text-xs group-hover:text-emerald-500 transition-colors"></i>
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
            <a href="browse.php" class="group flex items-center gap-2 text-indigo-600 font-semibold text-sm hover:text-indigo-700">
                View All <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            $recent_books = $conn->query("SELECT B.*, C.Category_Name as Category FROM book B LEFT JOIN category C ON B.Category_ID = C.Category_ID WHERE B.Status='Available' ORDER BY B.Book_ID DESC LIMIT 4");
            if ($recent_books && $recent_books->num_rows > 0) {
                while ($book = $recent_books->fetch_assoc()) {
                    $gradients = [
                        'from-indigo-500 to-purple-600',
                        'from-emerald-400 to-cyan-500',
                        'from-rose-400 to-red-500',
                        'from-amber-400 to-orange-500',
                        'from-blue-500 to-indigo-600',
                        'from-fuchsia-500 to-pink-600',
                        'from-teal-400 to-emerald-500',
                        'from-violet-500 to-fuchsia-500'
                    ];
                    $gradient = $gradients[$book['Book_ID'] % count($gradients)];
            ?>
            <div class="group bg-white/80 rounded-[18px] border border-slate-100/60 overflow-hidden hover:shadow-[0_12px_25px_rgba(0,0,0,0.12)] transition-all duration-200 hover:-translate-y-1.5 h-full flex flex-col relative">
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
        <a href="browse.php?search=${encodeURIComponent(book.title || '')}" class="block p-3.5 rounded-xl border border-slate-100 bg-slate-50/80 hover:bg-slate-100 transition-all duration-200">
            <p class="text-xs font-bold text-[#5f2eea] uppercase tracking-wider">${escapeHtml(book.category || 'General')}</p>
            <p class="text-sm font-bold text-slate-800 mt-1 line-clamp-1">${escapeHtml(book.title || '')}</p>
            <p class="text-xs text-slate-500 mt-1">by <span class="text-slate-700">${escapeHtml(book.author || 'Unknown')}</span></p>
        </a>
    `).join('');
}

renderRecentlyViewed();
</script>

</body>
</html>

