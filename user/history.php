<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

$user_id = $_SESSION['user_id'];

// Fetch returned books history
$sql = "SELECT B.Title, B.Author, C.Category_Name, I.Issue_Date, R.Return_Date 
        FROM Return_Book R 
        JOIN Issue I ON R.Issue_ID = I.Issue_ID 
        JOIN Book B ON I.Book_ID = B.Book_ID 
        LEFT JOIN Category C ON B.Category_ID = C.Category_ID
        WHERE I.Member_ID = ? 
        ORDER BY R.Return_Date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 animate-fade-in-up">

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-10">
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

    <!-- History Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
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
                                <h3 class="font-bold text-lg text-slate-800 leading-snug truncate pr-2" title="<?= $row['Title'] ?>"><?= $row['Title'] ?></h3>
                                <p class="text-slate-500 text-sm">by <?= $row['Author'] ?></p>
                            </div>
                            <!-- "Like" Heart Icon (Visual Only for now as requested "Liked Books") -->
                            <button class="text-slate-300 hover:text-pink-500 transition-colors" title="Add to favorites">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        
                        <div class="mt-4 flex flex-wrap gap-y-2 gap-x-6 text-xs text-slate-400 font-medium uppercase tracking-wide">
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-calendar-check text-emerald-400"></i>
                                <span>Returned: <?= date('M d, Y', strtotime($row['Return_Date'])) ?></span>
                            </div>
                            
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-tag text-indigo-400"></i>
                                <span><?= $row['Category_Name'] ?? 'General' ?></span>
                            </div>
                             
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
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

</body>
</html>
