<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

$user_id = $_SESSION['user_id'];

// Fetch borrowed books that are NOT returned yet
$sql = "SELECT I.Issue_ID, B.Title, B.Author, I.Issue_Date, I.Due_Date 
        FROM Issue I 
        JOIN Book B ON I.Book_ID = B.Book_ID 
        WHERE I.Member_ID = ? AND I.Issue_ID NOT IN (SELECT Issue_ID FROM Return_Book)
        ORDER BY I.Due_Date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in-up">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800">My Bookshelf</h1>
        <p class="text-slate-500 mt-1">Track your borrowed books and return dates.</p>
    </div>

    <!-- Active Loans -->
    <div class="space-y-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $due_date = strtotime($row['Due_Date']);
                $today = time();
                $is_late = $today > $due_date;
                
                // Calculate fine
                $fine = 0;
                if ($is_late) {
                    $days_late = floor(($today - $due_date) / (60 * 60 * 24));
                    $fine = $days_late * 10;
                }
            ?>
            <div class="bg-white rounded-2xl border border-slate-100 p-6 flex flex-col md:flex-row items-center gap-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                
                <?php if ($is_late): ?>
                    <div class="absolute top-0 right-0 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-bl-xl">Overdue</div>
                <?php endif; ?>

                <div class="w-16 h-16 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 text-2xl flex-shrink-0">
                    <i class="fas fa-book"></i>
                </div>
                
                <div class="flex-1 text-center md:text-left">
                    <h3 class="font-bold text-lg text-slate-800"><?= $row['Title'] ?></h3>
                    <p class="text-slate-500 text-sm">by <?= $row['Author'] ?></p>
                </div>

                <div class="flex items-center gap-8 text-sm">
                    <div class="text-center">
                        <p class="text-slate-400 text-xs font-bold uppercase">Borrowed On</p>
                        <p class="font-semibold text-slate-700"><?= date('M d, Y', strtotime($row['Issue_Date'])) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-slate-400 text-xs font-bold uppercase">Return By</p>
                        <p class="font-semibold <?= $is_late ? 'text-red-500' : 'text-slate-700' ?>"><?= date('M d, Y', strtotime($row['Due_Date'])) ?></p>
                    </div>
                    <?php if ($fine > 0): ?>
                    <div class="text-center">
                        <p class="text-slate-400 text-xs font-bold uppercase">Late Fine</p>
                        <p class="font-bold text-red-600">₹<?= $fine ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="w-full md:w-auto mt-4 md:mt-0">
                   <!-- <button class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold px-4 py-2 rounded-lg transition-colors text-sm">
                        Request Renew
                    </button> -->
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-2xl">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700">No active loans</h3>
                <p class="text-slate-500 mb-6">You haven't borrowed any books yet.</p>
                <a href="browse.php" class="inline-flex items-center gap-2 text-brand-600 font-bold hover:underline">
                    Browse Collection <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
