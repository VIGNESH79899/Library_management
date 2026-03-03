<?php
require_once "../config/app.php";
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
include "../config/db.php";

if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit;
}

$id = $_GET['id'];
$book = $conn->query("SELECT * FROM book WHERE Book_ID=$id")->fetch_assoc();

if (!$book) {
    header("Location: books.php");
    exit;
}

$categories = $conn->query("SELECT * FROM category");
$publishers = $conn->query("SELECT * FROM publisher");
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $category = $_POST['category'];
    $publisher = $_POST['publisher'];

    $stmt = $conn->prepare("
        UPDATE book 
        SET Title=?, Author=?, ISBN=?, Category_ID=?, Publisher_ID=?
        WHERE Book_ID=?
    ");
    $stmt->bind_param("sssiii", $title, $author, $isbn, $category, $publisher, $id);
    
    if ($stmt->execute()) {
        header("Location: books.php");
        exit;
    } else {
        $error = "Failed to update book.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Edit Book</title>
    <?php include "../includers/headers.php"; ?>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include "../includers/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="flex-1 ml-64 flex flex-col relative z-0">
        <!-- Top Navigation -->
        <?php include "../includers/navbar.php"; ?>
        
        <main class="p-8 space-y-8 flex items-center justify-center min-h-[calc(100vh-80px)]">
            <div class="w-full max-w-2xl animate-fade-in-up">
                
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-start gap-3" role="alert">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                        <div>
                            <p class="font-bold text-sm">Update Failed</p>
                            <p class="text-sm"><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="premium-card p-8">
                    <div class="mb-8 border-b border-gray-100 pb-5">
                        <!-- Breadcrumbs -->
                        <nav class="flex text-xs text-slate-400 mb-2" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1">
                                <li class="inline-flex items-center">
                                    <a href="books.php" class="hover:text-indigo-600 transition-colors">Books</a>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <i class="fas fa-chevron-right text-[10px] mx-2 content-center"></i>
                                        <span class="text-slate-600 font-medium">Edit Book</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="page-title">Edit Book Details</h1>
                    </div>

                    <form method="POST" class="space-y-6">
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Book Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" required value="<?= htmlspecialchars($book['Title']) ?>"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm outline-none placeholder-gray-400"
                                   placeholder="Enter book title">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Author <span class="text-red-500">*</span></label>
                                <input type="text" name="author" required value="<?= htmlspecialchars($book['Author']) ?>"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm outline-none placeholder-gray-400"
                                       placeholder="Author name">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">ISBN Number <span class="text-red-500">*</span></label>
                                <input type="text" name="isbn" required value="<?= htmlspecialchars($book['ISBN']) ?>"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm outline-none placeholder-gray-400 font-mono"
                                       placeholder="e.g. 978-3-16-148410-0">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Category <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="category" required 
                                            class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm outline-none appearance-none cursor-pointer">
                                        <option value="" disabled>Select category</option>
                                        <?php while ($c = $categories->fetch_assoc()) { ?>
                                            <option value="<?= $c['Category_ID'] ?>" <?= ($c['Category_ID'] == $book['Category_ID']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['Category_Name']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Publisher <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="publisher" required 
                                            class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm outline-none appearance-none cursor-pointer">
                                        <option value="" disabled>Select publisher</option>
                                        <?php while ($p = $publishers->fetch_assoc()) { ?>
                                            <option value="<?= $p['Publisher_ID'] ?>" <?= ($p['Publisher_ID'] == $book['Publisher_ID']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['Publisher_Name']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 mt-8 border-t border-gray-100">
                            <a href="books.php" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center gap-2">
                                <i class="fas fa-save"></i> Update Book
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>
</div>

</body>
</html>
