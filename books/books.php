<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

// Fetch Books
$books = $conn->query("
    SELECT B.Book_ID, B.Title, B.Author, B.ISBN, B.Status, C.Category_Name, P.Publisher_Name
    FROM Book B
    LEFT JOIN Category C ON B.Category_ID = C.Category_ID
    LEFT JOIN Publisher P ON B.Publisher_ID = P.Publisher_ID
    ORDER BY B.Book_ID DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Books Management</title>
    <?php include "../includers/headers.php"; ?>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include "../includers/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="flex-1 ml-64 flex flex-col relative z-0">
        
        <!-- Helper for mobile toggle if needed later -->
        
        <main class="p-8 space-y-8">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Books Management</h1>
                    <!-- Breadcrumbs -->
                    <nav class="flex text-sm text-slate-500 mt-1">
                        <a href="../dashboard/dashboard.php" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                        <span class="mx-2">/</span>
                        <span class="text-indigo-600 font-medium">Books</span>
                    </nav>
                </div>
                
                <a href="addBook.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-all shadow-lg shadow-indigo-500/30 flex items-center gap-2 transform hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i>
                    <span>Add New Book</span>
                </a>
            </div>

            <!-- Content Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up">
                
                <!-- Filters or Search (Optional placeholder) -->
                <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h2 class="font-bold text-slate-700">All Books List</h2>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" placeholder="Search books..." class="pl-8 pr-4 py-1.5 rounded-md border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all w-64">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Book Details</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Publisher</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while ($row = $books->fetch_assoc()) { ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="p-4 font-mono text-xs text-slate-400">#<?= str_pad($row['Book_ID'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-8 bg-slate-200 rounded flex-shrink-0 flex items-center justify-center text-slate-400">
                                            <i class="fas fa-book"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 hover:text-indigo-600 transition-colors cursor-pointer"><?= $row['Title'] ?></div>
                                            <div class="text-xs text-slate-500">by <?= $row['Author'] ?> • ISBN: <?= $row['ISBN'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-600">
                                        <?= $row['Category_Name'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-slate-600 text-xs font-medium"><?= $row['Publisher_Name'] ?></td>
                                <td class="p-4">
                                    <?php if ($row['Status'] == 'Available') { ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 border border-green-100">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                            Available
                                        </span>
                                    <?php } else { ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-600 border border-amber-100">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Issued
                                        </span>
                                    <?php } ?>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="editBook.php?id=<?= $row['Book_ID'] ?>" class="h-8 w-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <a href="deleteBook.php?id=<?= $row['Book_ID'] ?>" class="h-8 w-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this book?')">
                                            <i class="fas fa-trash text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination (Mockup) -->
                <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-slate-500">
                    <div>Showing all books</div>
                    <div class="flex gap-1">
                        <button class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-50 disabled:opacity-50">Prev</button>
                        <button class="px-3 py-1 bg-indigo-50 border border-indigo-200 text-indigo-600 rounded font-medium">1</button>
                        <button class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-50">Next</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>
