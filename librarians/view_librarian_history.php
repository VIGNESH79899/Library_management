<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

if (!isset($_GET['id'])) {
    header("Location: librarians.php");
    exit;
}

$librarian_id = $_GET['id'];

// Fetch Librarian Details
$lib_stmt = $conn->prepare("SELECT * FROM librarian WHERE Librarian_ID = ?");
$lib_stmt->bind_param("i", $librarian_id);
$lib_stmt->execute();
$librarian = $lib_stmt->get_result()->fetch_assoc();

if (!$librarian) {
    header("Location: librarians.php");
    exit;
}

// Fetch Issued Books History
$history = $conn->prepare("
    SELECT I.*, B.Title, B.ISBN, M.Member_Name, M.Email as Member_Email
    FROM issue I
    JOIN book B ON I.Book_ID = B.Book_ID
    JOIN member M ON I.Member_ID = M.Member_ID
    WHERE I.Librarian_ID = ?
    ORDER BY I.Issue_Date DESC
");
$history->bind_param("i", $librarian_id);
$history->execute();
$result = $history->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Librarian History</title>
    <?php include "../includers/headers.php"; ?>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include "../includers/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="flex-1 ml-64 flex flex-col relative z-0 transition-all duration-300">
        <!-- Top Navigation -->
        <?php include "../includers/navbar.php"; ?>

        <main class="p-8 space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Librarian Activity Log</h1>
                    <div class="flex items-center gap-2 mt-2 text-slate-500">
                        <span class="font-semibold text-indigo-600"><?= $librarian['Librarian_Name'] ?></span>
                        <span>&bull;</span>
                        <span><?= $librarian['Email'] ?></span>
                    </div>
                </div>
                <div>
                    <a href="librarians.php" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-800 px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-sm flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Back to Librarians
                    </a>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium uppercase tracking-wider">Total Books Issued</p>
                        <h3 class="text-2xl font-bold text-slate-800"><?= $result->num_rows ?></h3>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h2 class="font-bold text-slate-700">Issued Books History</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Issue ID</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Book Details</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Issued To (Member)</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Issue Date</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 font-mono text-xs text-slate-400">#<?= $row['Issue_ID'] ?></td>
                                    <td class="p-4">
                                        <div class="font-medium text-slate-800"><?= $row['Title'] ?></div>
                                        <div class="text-xs text-slate-400">ISBN: <?= $row['ISBN'] ?></div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-medium text-slate-700"><?= $row['Member_Name'] ?></div>
                                        <div class="text-xs text-slate-400"><?= $row['Member_Email'] ?></div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <i class="far fa-calendar text-slate-400"></i>
                                            <?= date('M d, Y', strtotime($row['Issue_Date'])) ?>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <i class="far fa-calendar-alt text-slate-400"></i>
                                            <?= date('M d, Y', strtotime($row['Due_Date'])) ?>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if(isset($row['Return_Date']) && $row['Return_Date']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-100">
                                                Returned
                                            </span>
                                        <?php else: ?>
                                            <?php 
                                            $dueDate = strtotime($row['Due_Date']);
                                            $isOverdue = time() > $dueDate;
                                            ?>
                                            <?php if($isOverdue): ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-100">
                                                    Overdue
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-600 border border-blue-100">
                                                    Active
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400 italic">No issuance history found for this librarian.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

</body>
</html>
