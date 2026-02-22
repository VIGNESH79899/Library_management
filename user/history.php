<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

$user_id = $_SESSION['user_id'];

// Fine rate: fixed at ₹10/day
define('FINE_RATE_PER_DAY', 10.00);
$fine_rate = FINE_RATE_PER_DAY;

// Fetch returned books history — join Fine table for DATEDIFF-based data
$sql = "SELECT B.Book_ID, B.Title, B.Author, C.Category_Name,
        I.Issue_Date, I.Due_Date, R.Return_Date,
        R.Fine_Amount AS Return_Fine,
        F.Fine_ID, F.Days_Late, F.Fine_Rate, F.Fine_Amount AS Fine_Table_Amount,
        CASE WHEN L.Like_ID IS NOT NULL THEN 1 ELSE 0 END as is_liked
        FROM Return_Book R
        JOIN Issue I ON R.Issue_ID = I.Issue_ID
        JOIN Book B ON I.Book_ID = B.Book_ID
        LEFT JOIN Category C ON B.Category_ID = C.Category_ID
        LEFT JOIN Book_Likes L ON B.Book_ID = L.Book_ID AND L.Member_ID = ?
        LEFT JOIN Fine F ON I.Issue_ID = F.Issue_ID
        WHERE I.Member_ID = ?
        ORDER BY R.Return_Date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Collect rows to calculate totals
$history_rows = [];
$total_fines  = 0.0;
$total_days_late = 0;
while ($row = $result->fetch_assoc()) {
    // Prefer Fine table data (DATEDIFF-based) if available
    $fine_amt    = ($row['Fine_ID'] !== null) ? (float)$row['Fine_Table_Amount'] : (float)$row['Return_Fine'];
    $days_late   = ($row['Fine_ID'] !== null) ? (int)$row['Days_Late'] : 0;
    $row['_fine_amt']  = $fine_amt;
    $row['_days_late'] = $days_late;
    $history_rows[]    = $row;
    $total_fines      += $fine_amt;
    $total_days_late  += $days_late;
}
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 animate-fade-in-up">

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Reading History</h1>
            <p class="text-slate-500 mt-1">A collection of all the books you've explored.</p>
        </div>
        
        <div class="flex gap-2">
            <a href="browse.php" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-4 py-2 rounded-lg font-bold text-sm transition-colors flex items-center gap-2">
                <i class="fas fa-search"></i> Find New Books
            </a>
        </div>
    </div>

    <!-- Summary Bar -->
    <?php if (!empty($history_rows)): ?>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-slate-100 rounded-2xl p-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500">
                <i class="fas fa-book"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Books Read</p>
                <p class="text-xl font-bold text-slate-800"><?= count($history_rows) ?></p>
            </div>
        </div>
        <div class="bg-white border <?= $total_fines > 0 ? 'border-red-100' : 'border-slate-100' ?> rounded-2xl p-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-full <?= $total_fines > 0 ? 'bg-red-50 text-red-500' : 'bg-emerald-50 text-emerald-500' ?> flex items-center justify-center">
                <i class="fas fa-receipt"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Total Fines Paid</p>
                <p class="text-xl font-bold <?= $total_fines > 0 ? 'text-red-600' : 'text-emerald-600' ?>">₹<?= number_format($total_fines, 2) ?></p>
            </div>
        </div>
        <div class="hidden md:flex bg-white border border-slate-100 rounded-2xl p-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center">
                <i class="fas fa-coins"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Fine Rate</p>
                <p class="text-xl font-bold text-slate-800">₹<?= number_format($fine_rate, 0) ?><span class="text-xs text-slate-400 font-normal">/day</span></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- History Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if (!empty($history_rows)): ?>
            <?php foreach ($history_rows as $row):
                $is_liked   = $row['is_liked'];
                $book_id    = $row['Book_ID'];
                // Use pre-processed Fine table data
                $fine_amt  = $row['_fine_amt'];
                $days_late = $row['_days_late'];
            ?>
                <div class="bg-white rounded-2xl border border-slate-100 p-5 flex items-start gap-5 hover:shadow-lg transition-shadow group relative overflow-hidden">
                    
                    <!-- Decorative Background -->
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-full blur-xl group-hover:scale-150 transition-transform duration-700"></div>

                    <!-- Icon -->
                    <div class="w-16 h-20 rounded-lg bg-slate-50 flex items-center justify-center text-slate-300 text-3xl shadow-inner flex-shrink-0 group-hover:text-indigo-400 transition-colors">
                        <i class="fas fa-book"></i>
                    </div>

                    <div class="flex-1 min-w-0 relative z-10">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-lg text-slate-800 leading-snug truncate pr-2" title="<?= htmlspecialchars($row['Title']) ?>"><?= htmlspecialchars($row['Title']) ?></h3>
                                <p class="text-slate-500 text-sm">by <?= htmlspecialchars($row['Author']) ?></p>
                            </div>
                            <!-- "Like" Heart Icon -->
                            <button onclick="toggleLike(this, <?= $book_id ?>)" 
                                    class="text-2xl transition-all active:scale-95 hover:scale-110 focus:outline-none <?= $is_liked ? 'text-pink-500' : 'text-slate-300 hover:text-pink-300' ?>" 
                                    title="<?= $is_liked ? 'Remove from favorites' : 'Add to favorites' ?>">
                                <i class="<?= $is_liked ? 'fas' : 'far' ?> fa-heart"></i>
                            </button>
                        </div>
                        
                        <div class="mt-3 flex flex-wrap gap-y-2 gap-x-5 text-xs text-slate-400 font-medium uppercase tracking-wide">
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-calendar-check text-emerald-400"></i>
                                <span>Returned: <?= date('M d, Y', strtotime($row['Return_Date'])) ?></span>
                            </div>
                            
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-tag text-indigo-400"></i>
                                <span><?= htmlspecialchars($row['Category_Name'] ?? 'General') ?></span>
                            </div>
                        </div>

                        <!-- Fine Badge -->
                        <div class="mt-3">
                            <?php if ($fine_amt > 0): ?>
                                <div class="inline-flex items-center gap-2 bg-red-50 border border-red-100 text-red-600 rounded-lg px-3 py-1.5 text-xs font-semibold">
                                    <i class="fas fa-receipt"></i>
                                    Fine Paid: &#8377;<?= number_format($fine_amt, 2) ?>
                                    <?php if ($days_late > 0): ?>
                                        <span class="text-red-400 font-normal">(DATEDIFF = <?= $days_late ?> &times; &#8377;<?= FINE_RATE_PER_DAY ?>)</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="inline-flex items-center gap-2 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-lg px-3 py-1.5 text-xs font-semibold">
                                    <i class="fas fa-award"></i>
                                    No Fine — Returned on time!
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full py-16 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-3xl shadow-sm">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No history yet</h3>
                <p class="text-slate-500 max-w-md mx-auto mb-6">Once you return borrowed books, they will appear here in your reading history.</p>
                <a href="browse.php" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-lg shadow-indigo-200 hover:-translate-y-0.5">
                    Start Reading Now
                </a>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
async function toggleLike(btn, bookId) {
    if (btn.disabled) return;
    btn.disabled = true;

    const icon = btn.querySelector('i');
    const wasLiked = icon.classList.contains('fas');
    
    if (wasLiked) {
        icon.classList.remove('fas', 'text-pink-500');
        icon.classList.add('far');
        btn.classList.remove('text-pink-500');
        btn.classList.add('text-slate-300', 'hover:text-pink-300');
        btn.title = "Add to favorites";
    } else {
        icon.classList.remove('far');
        icon.classList.add('fas');
        btn.classList.remove('text-slate-300', 'hover:text-pink-300');
        btn.classList.add('text-pink-500');
        btn.title = "Remove from favorites";
        icon.classList.add('animate-ping');
        setTimeout(() => icon.classList.remove('animate-ping'), 300);
    }

    try {
        const response = await fetch('toggle_like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ book_id: bookId }),
        });

        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
             throw new Error("Received non-JSON response from server");
        }

        const result = await response.json();
        if (result.status === 'error') throw new Error(result.message || "Unknown error");
        
    } catch (error) {
        console.error('Like toggle failed:', error);
        if (wasLiked) {
            icon.classList.add('fas', 'text-pink-500');
            icon.classList.remove('far');
            btn.classList.add('text-pink-500');
            btn.classList.remove('text-slate-300', 'hover:text-pink-300');
            btn.title = "Remove from favorites";
        } else {
            icon.classList.remove('fas', 'text-pink-500');
            icon.classList.add('far');
            btn.classList.remove('text-pink-500');
            btn.classList.add('text-slate-300', 'hover:text-pink-300');
            btn.title = "Add to favorites";
        }
        alert('Failed to update: ' + error.message);
    } finally {
        btn.disabled = false;
    }
}
</script>

</body>
</html>
