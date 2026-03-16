<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Staff Role Restriction
if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'staff') {
    header("Location: " . BASE_URL . "/dashboard/dashboard.php");
    exit;
}

include "../config/db.php";

$categories = $conn->query("SELECT * FROM category");
$publishers = $conn->query("SELECT * FROM publisher");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $category = $_POST['category'];
    $publisher = $_POST['publisher'];

    $quantity = (int)$_POST['quantity'];

    $stmt = $conn->prepare("
        INSERT INTO book (Title, Author, ISBN, Category_ID, Publisher_ID, Quantity, Available_Quantity)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssiiii", $title, $author, $isbn, $category, $publisher, $quantity, $quantity);
    if ($stmt->execute()) {
        header("Location: books.php");
        exit;
    } else {
        $error = "Failed to add book.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Add Book</title>
    <?php include "../includers/headers.php"; ?>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include "../includers/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="main-content flex-1 ml-0 md:ml-64 flex flex-col relative z-0">
        <!-- Top Navigation -->
        <?php include "../includers/navbar.php"; ?>
        
        <main class="p-4 md:p-8 space-y-8 flex items-center justify-center min-h-[calc(100vh-80px)]">
            <div class="w-full max-w-2xl animate-fade-in-up">
                
                <div class="flex flex-col mb-6">
                    <h1 class="page-title">Add New Book</h1>
                    <nav class="flex text-sm text-slate-500 mt-1">
                        <a href="books.php" class="hover:text-indigo-600 transition-colors">Books Management</a>
                        <span class="mx-2">/</span>
                        <span class="text-indigo-600 font-medium">Add New</span>
                    </nav>
                </div>

                <div class="premium-card overflow-hidden text-sm">
                    <form method="POST" class="p-8 space-y-6">
                        
                        <?php if (isset($error)) { ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-start gap-3 animate-fade-in" role="alert">
                                <i class="fas fa-exclamation-circle mt-0.5 text-red-500"></i>
                                <div>
                                    <p class="font-bold text-sm">Error</p>
                                    <p class="text-sm"><?= $error ?></p>
                                </div>
                            </div>
                        <?php } ?>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-2">Book Title</label>
                            <input type="text" name="title" required 
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all outline-none"
                                   placeholder="Enter book title">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-2">Author</label>
                                <input type="text" name="author" required 
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all outline-none"
                                       placeholder="Author name">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-2">ISBN</label>
                                <input type="text" name="isbn" required 
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all outline-none"
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-2">Total Copies (Quantity)</label>
                                <input type="number" name="quantity" required min="1" value="1"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all outline-none"
                                       placeholder="Number of copies">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-2">Category</label>
                                <div class="relative">
                                    <select name="category" required 
                                            class="w-full pl-4 pr-10 py-2.5 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all outline-none appearance-none cursor-pointer">
                                        <option value="">Select Category</option>
                                        <?php while ($c = $categories->fetch_assoc()) { ?>
                                            <option value="<?= $c['Category_ID'] ?>">
                                                <?= $c['Category_Name'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-2">Publisher</label>
                                <div class="relative">
                                    <select name="publisher" required 
                                            class="w-full pl-4 pr-10 py-2.5 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all outline-none appearance-none cursor-pointer">
                                        <option value="">Select Publisher</option>
                                        <?php while ($p = $publishers->fetch_assoc()) { ?>
                                            <option value="<?= $p['Publisher_ID'] ?>">
                                                <?= $p['Publisher_Name'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100 flex items-center gap-4">
                            <a href="books.php" class="px-6 py-2.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 font-medium transition-colors">Cancel</a>
                            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg font-bold shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                                <i class="fas fa-save"></i>
                                <span>Save Book</span>
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


