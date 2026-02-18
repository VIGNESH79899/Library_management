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
$sql = "SELECT B.*, C.Category_Name FROM Book B LEFT JOIN Category C ON B.Category_ID = C.Category_ID WHERE B.Status='Available'";
if ($search) {
    $sql .= " AND (B.Title LIKE '%$search%' OR B.Author LIKE '%$search%' OR C.Category_Name LIKE '%$search%')";
}
$books = $conn->query($sql);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 animate-fade-in-up">

    <!-- Header & Search -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-700 to-slate-900 tracking-tight mb-4">Discover Your Next Read</h1>
        <p class="text-slate-500 max-w-2xl mx-auto text-lg mb-8">Search through our vast collection of books and borrow them instantly.</p>
        
        <form class="max-w-xl mx-auto relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title, author, or category..." 
                   class="w-full pl-12 pr-4 py-4 rounded-full border-2 border-slate-100 hover:border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all shadow-xl shadow-slate-200/50 text-slate-600 font-medium">
            <?php if ($search): ?>
                <a href="browse.php" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fas fa-times-circle"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="relative bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl mb-10 flex items-start gap-4 shadow-lg shadow-emerald-100/50 animate-fade-in mx-auto max-w-2xl">
            <div class="bg-white p-2 rounded-full shadow-sm text-emerald-500">
                 <i class="fas fa-check text-xl"></i>
            </div>
            <div>
                 <h4 class="font-bold text-lg">Success!</h4>
                 <p><?= $message ?></p>
            </div>
             <button onclick="this.parentElement.remove()" class="absolute top-4 right-4 text-emerald-400 hover:text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="relative bg-red-50 border border-red-100 text-red-700 px-6 py-4 rounded-2xl mb-10 flex items-start gap-4 shadow-lg shadow-red-100/50 animate-fade-in mx-auto max-w-2xl">
            <div class="bg-white p-2 rounded-full shadow-sm text-red-500">
                 <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <div>
                 <h4 class="font-bold text-lg">Error</h4>
                 <p><?= $error ?></p>
            </div>
            <button onclick="this.parentElement.remove()" class="absolute top-4 right-4 text-red-400 hover:text-red-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif; ?>

    <!-- Books Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php if ($books->num_rows > 0): ?>
            <?php while ($book = $books->fetch_assoc()): ?>
                <div class="group bg-white rounded-3xl border border-slate-100 overflow-hidden hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300 hover:-translate-y-2 flex flex-col h-full relative">
                    
                    <!-- Cover -->
                    <div class="h-64 bg-slate-50 relative overflow-hidden flex items-center justify-center p-8 group-hover:bg-indigo-50/30 transition-colors">
                        <!-- Abstract Background Circle -->
                        <div class="absolute w-48 h-48 bg-white rounded-full blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                        
                        <i class="fas fa-book text-7xl text-slate-200 group-hover:text-indigo-300 drop-shadow-sm transition-all duration-500 group-hover:scale-110 group-hover:-rotate-3 relative z-10"></i>
                        
                        <div class="absolute top-4 right-4 z-20">
                            <span class="bg-white/80 backdrop-blur-md text-[10px] uppercase font-extrabold px-3 py-1 rounded-full shadow-sm border border-slate-100 text-indigo-600 tracking-wider">
                                <?= $book['Category_Name'] ?? 'General' ?>
                            </span>
                        </div>
                        
                         <!-- Hover Overlay Button -->
                        <div class="absolute inset-0 bg-indigo-900/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-[1px] z-20">
                           
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6 flex-1 flex flex-col relative z-20 bg-white">
                        <h3 class="font-bold text-xl text-slate-800 mb-1 leading-tight line-clamp-2 group-hover:text-indigo-600 transition-colors" title="<?= $book['Title'] ?>"><?= $book['Title'] ?></h3>
                        <p class="text-slate-400 text-sm font-medium mb-6">by <span class="text-slate-600"><?= $book['Author'] ?></span></p>
                        
                        <div class="mt-auto">
                            <a href="browse.php?take=<?= $book['Book_ID'] ?><?= $search ? '&search='.urlencode($search) : '' ?>" onclick="return confirm('Are you sure you want to borrow this book?')" 
                               class="block w-full text-center bg-slate-50 hover:bg-indigo-600 text-slate-700 hover:text-white font-bold py-3.5 rounded-2xl transition-all shadow-sm hover:shadow-lg hover:shadow-indigo-500/30 active:scale-95 group/btn">
                                <span class="group-hover/btn:hidden">Borrow</span>
                                <span class="hidden group-hover/btn:inline-flex items-center gap-2"><i class="fas fa-plus"></i> Add to Account</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full py-20 text-center">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 text-4xl">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No books found</h3>
                <p class="text-slate-400">We couldn't find any available books matching "<?= htmlspecialchars($search) ?>"</p>
                <?php if ($search): ?>
                    <a href="browse.php" class="inline-block mt-6 text-indigo-600 font-bold hover:underline">Clear Search</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
