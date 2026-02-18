<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

$message = "";
$error = "";

// Handle Borrow Request
if (isset($_GET['take'])) {
    $book_id = intval($_GET['take']);
    $user_id = $_SESSION['user_id'];
    
    // Check if book is available
    $check = $conn->query("SELECT Status FROM Book WHERE Book_ID = $book_id");
    if ($check->num_rows > 0 && $check->fetch_assoc()['Status'] == 'Available') {
        
        $issue_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime('+7 days'));
        
        $conn->begin_transaction();
        try {
            // Insert Issue
            $stmt = $conn->prepare("INSERT INTO Issue (Book_ID, Member_ID, Librarian_ID, Issue_Date, Due_Date) VALUES (?, ?, 1, ?, ?)");
            $stmt->bind_param("iiss", $book_id, $user_id, $issue_date, $due_date);
            $stmt->execute();
            
            // Update Book Status
            $conn->query("UPDATE Book SET Status='Issued' WHERE Book_ID=$book_id");
            
            $conn->commit();
            $message = "Book borrowed successfully! Please return by " . date('M d, Y', strtotime($due_date));
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to borrow book.";
        }
    } else {
        $error = "This book is no longer available.";
    }
}

// Search Logic
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM Book WHERE Status='Available'";
if ($search) {
    $sql .= " AND (Title LIKE '%$search%' OR Author LIKE '%$search%' OR Category LIKE '%$search%')";
}
$books = $conn->query($sql);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in-up">

    <!-- Header & Search -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Browse Books</h1>
            <p class="text-slate-500 mt-1">Explore our collection and borrow instantly.</p>
        </div>
        <form class="w-full md:w-96 relative">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title, author..." 
                   class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none transition-all shadow-sm">
            <i class="fas fa-search absolute left-3.5 top-3.5 text-gray-400"></i>
        </form>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3 animate-fade-in">
            <i class="fas fa-check-circle text-lg"></i>
            <?= $message ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3 animate-fade-in">
            <i class="fas fa-exclamation-circle text-lg"></i>
            <?= $error ?>
        </div>
    <?php endif; ?>

    <!-- Books Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php if ($books->num_rows > 0): ?>
            <?php while ($book = $books->fetch_assoc()): ?>
                <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col h-full">
                    <div class="h-48 bg-slate-50 relative overflow-hidden flex items-center justify-center p-6">
                         <!-- Fallback Icon -->
                        <i class="fas fa-book text-5xl text-slate-200"></i>
                        
                        <div class="absolute top-3 right-3">
                            <span class="bg-white/90 backdrop-blur text-xs font-bold px-2 py-1 rounded-md shadow-sm border border-slate-100 text-slate-600">
                                <?= $book['Category'] ?? 'General' ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="font-bold text-lg text-slate-800 mb-1 leading-tight"><?= $book['Title'] ?></h3>
                        <p class="text-slate-500 text-sm mb-4">by <?= $book['Author'] ?></p>
                        
                        <div class="mt-auto pt-4 border-t border-slate-50">
                            <a href="browse.php?take=<?= $book['Book_ID'] ?>" onclick="return confirm('Are you sure you want to borrow this book?')" 
                               class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-2.5 rounded-xl shadow-lg shadow-brand-500/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                                <i class="fas fa-plus-circle"></i> Borrow Book
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full py-12 text-center text-slate-400">
                <i class="fas fa-search text-4xl mb-3"></i>
                <p>No available books match your search.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
