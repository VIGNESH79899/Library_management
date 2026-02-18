<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

$user_id = $_SESSION['user_id'];

// Stats
// 1. Books Currently Borrowed
$sql_borrowed = "SELECT COUNT(*) as count FROM Issue WHERE Member_ID = ? AND Issue_ID NOT IN (SELECT Issue_ID FROM Return_Book)";
$stmt = $conn->prepare($sql_borrowed);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$borrowed_count = $stmt->get_result()->fetch_assoc()['count'];

// 2. Total Books Returned
$sql_returned = "SELECT COUNT(*) as count FROM Issue I JOIN Return_Book R ON I.Issue_ID = R.Issue_ID WHERE I.Member_ID = ?";
$stmt = $conn->prepare($sql_returned);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$returned_count = $stmt->get_result()->fetch_assoc()['count'];

// 3. Outstanding Fines (Estimated)
$sql_issues = "SELECT Due_Date FROM Issue WHERE Member_ID = ? AND Issue_ID NOT IN (SELECT Issue_ID FROM Return_Book)";
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
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in-up">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800">Welcome back, <?= explode(' ', $_SESSION['user_name'])[0] ?>!</h1>
        <p class="text-slate-500 mt-1">Here's what's happening with your library account.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <!-- Card 1 -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fas fa-book-reader"></i>
            </div>
            <div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Currently Borrowed</p>
                <p class="text-3xl font-bold text-slate-800"><?= $borrowed_count ?></p>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Returned</p>
                <p class="text-3xl font-bold text-slate-800"><?= $returned_count ?></p>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl <?= $total_fine > 0 ? 'bg-red-50 text-red-600' : 'bg-gray-50 text-gray-400' ?> flex items-center justify-center text-xl">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Estimated Fines</p>
                <p class="text-3xl font-bold text-slate-800">₹<?= $total_fine ?></p>
            </div>
        </div>
    </div>

    <!-- Recently Added Books (Optional Preview) -->
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h2 class="text-xl font-bold text-slate-800">New Arrivals</h2>
            <p class="text-slate-500 text-sm">Freshly added to our collection</p>
        </div>
        <a href="browse.php" class="text-brand-600 font-semibold text-sm hover:underline">View All Books &rarr;</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $recent_books = $conn->query("SELECT * FROM Book WHERE Status='Available' ORDER BY Book_ID DESC LIMIT 4");
        while ($book = $recent_books->fetch_assoc()) {
        ?>
        <div class="group bg-white rounded-2xl border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="h-40 bg-slate-100 relative overflow-hidden flex items-center justify-center">
                <!-- Fallback book cover -->
                <i class="fas fa-book text-4xl text-slate-300 group-hover:scale-110 transition-transform duration-500"></i>
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                    <span class="text-white text-xs font-bold bg-brand-600 px-2 py-1 rounded">Available</span>
                </div>
            </div>
            <div class="p-5">
                <h3 class="font-bold text-slate-800 line-clamp-1 mb-1" title="<?= $book['Title'] ?>"><?= $book['Title'] ?></h3>
                <p class="text-slate-500 text-sm mb-4 line-clamp-1">By <?= $book['Author'] ?></p>
                <a href="browse.php?take=<?= $book['Book_ID'] ?>" class="block w-full text-center py-2 rounded-lg bg-slate-50 text-slate-600 font-semibold text-sm hover:bg-brand-600 hover:text-white transition-colors">
                    Borrow Now
                </a>
            </div>
        </div>
        <?php } ?>
    </div>

</div>

</body>
</html>
