<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

// Stats
$totalBooks = $conn->query("SELECT COUNT(*) AS total FROM Book")->fetch_assoc()['total'];
$totalMembers = $conn->query("SELECT COUNT(*) AS total FROM Member")->fetch_assoc()['total'];
$issuedBooks = $conn->query("SELECT COUNT(*) AS total FROM Book WHERE Status='Issued'")->fetch_assoc()['total'];
$totalFines = $conn->query("SELECT IFNULL(SUM(Fine_Amount),0) AS total FROM Return_Book")->fetch_assoc()['total'];

// Recent Issues
$issues = $conn->query("
    SELECT I.Issue_ID, B.Title, M.Member_Name, I.Issue_Date, I.Due_Date
    FROM Issue I
    JOIN Book B ON I.Book_ID = B.Book_ID
    JOIN Member M ON I.Member_ID = M.Member_ID
    ORDER BY I.Issue_Date DESC
    LIMIT 5
");

// Recent Returns
$returns = $conn->query("
    SELECT R.Return_ID, B.Title, R.Return_Date, R.Fine_Amount
    FROM Return_Book R
    JOIN Issue I ON R.Issue_ID = I.Issue_ID
    JOIN Book B ON I.Book_ID = B.Book_ID
    ORDER BY R.Return_Date DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Dashboard</title>
    <!-- Header includes CSS now, so we just need to include the header file in the body, 
         but technically the header file has the <head> tags. 
         Wait, includers/headers.php has <head> tags.
         So I should NOT put <head> here if I include headers.php first. 
         However, standard practice in this project seems to be including headers.php inside the body?
         Let's check headers.php again. It has <head> ... </head> followed by the header <div>.
         This is a bit weird structure (head inside body or just included).
         Let's stick to the pattern: headers.php contains the <head> section AND the top bar.
    -->
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
        
        <!-- Dashboard Content -->
        
        <!-- Dashboard Content -->
        <main class="p-8 space-y-8">
            
            <!-- Welcome Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 animate-enter">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight font-inter">Dashboard</h1>
                    <p class="text-slate-500 mt-2 font-medium">Overview of library performance and activities.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-white border border-gray-200 px-4 py-2 rounded-lg flex items-center gap-2 text-sm font-semibold text-slate-600 shadow-sm">
                        <i class="far fa-calendar text-slate-400"></i>
                        <?= date('M j, Y') ?>
                    </div>
                    <button class="btn-primary px-5 py-2 rounded-lg text-sm font-semibold flex items-center gap-2" onclick="window.location.href='../issue/issueBook.php'">
                        <i class="fas fa-plus"></i> Issue Book
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Books -->
                <div class="bg-white p-6 rounded-xl border border-slate-200/60 shadow-sm hover-card group animate-enter delay-100">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-12 w-12 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                            <i class="fas fa-book text-lg"></i>
                        </div>
                        <span class="badge bg-green-50 text-green-700 border border-green-100 flex items-center gap-1">
                            <i class="fas fa-arrow-up text-[10px]"></i> 2.5%
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Total Books</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1 font-inter"><?= number_format($totalBooks) ?></h3>
                    </div>
                </div>

                <!-- Total Members -->
                <div class="bg-white p-6 rounded-xl border border-slate-200/60 shadow-sm hover-card group animate-enter delay-200">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-12 w-12 rounded-lg bg-pink-50 border border-pink-100 flex items-center justify-center text-pink-600 group-hover:bg-pink-600 group-hover:text-white transition-colors duration-300">
                            <i class="fas fa-users text-lg"></i>
                        </div>
                        <span class="badge bg-green-50 text-green-700 border border-green-100 flex items-center gap-1">
                            <i class="fas fa-arrow-up text-[10px]"></i> 12%
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Members</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1 font-inter"><?= number_format($totalMembers) ?></h3>
                    </div>
                </div>

                <!-- Issued Books -->
                <div class="bg-white p-6 rounded-xl border border-slate-200/60 shadow-sm hover-card group animate-enter delay-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-12 w-12 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300">
                            <i class="fas fa-hand-holding text-lg"></i>
                        </div>
                        <span class="badge bg-slate-50 text-slate-600 border border-slate-100">Active</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Result Issued</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1 font-inter"><?= number_format($issuedBooks) ?></h3>
                    </div>
                </div>

                <!-- Fines -->
                <div class="bg-white p-6 rounded-xl border border-slate-200/60 shadow-sm hover-card group animate-enter delay-400">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-12 w-12 rounded-lg bg-red-50 border border-red-100 flex items-center justify-center text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors duration-300">
                            <i class="fas fa-rupee-sign text-lg"></i>
                        </div>
                        <span class="badge bg-red-50 text-red-700 border border-red-100">Action</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Unpaid Fines</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1 font-inter">₹<?= number_format($totalFines) ?></h3>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Issues -->
                <div class="glass-panel rounded-xl overflow-hidden animate-enter delay-500 flex flex-col">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="font-bold text-slate-800 text-lg">Recent Scans</h2>
                        <a href="../issue/issueBook.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline uppercase tracking-wide">View All</a>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left text-sm table-modern">
                            <thead>
                                <tr>
                                    <th class="pl-6">Book Item</th>
                                    <th>Recipient</th>
                                    <th>Date</th>
                                    <th class="pr-6 text-right">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($issues->num_rows > 0): ?>
                                    <?php while ($row = $issues->fetch_assoc()) { ?>
                                    <tr class="table-row-modern group cursor-default">
                                        <td class="p-4 pl-6">
                                            <div class="font-semibold text-slate-700"><?= $row['Title'] ?></div>
                                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">ID: #<?= $row['Issue_ID'] ?></div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] text-slate-500 font-bold uppercase">
                                                    <?= substr($row['Member_Name'], 0, 1) ?>
                                                </div>
                                                <span class="text-slate-600 font-medium"><?= $row['Member_Name'] ?></span>
                                            </div>
                                        </td>
                                        <td class="p-4 text-slate-500 font-mono text-xs"><?= date('M d', strtotime($row['Issue_Date'])) ?></td>
                                        <td class="p-4 pr-6 text-right">
                                            <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded border border-amber-100">
                                                <?= date('M d', strtotime($row['Due_Date'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center text-slate-400 text-sm">No recent transactions.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Returns -->
                <div class="glass-panel rounded-xl overflow-hidden animate-enter delay-500 flex flex-col">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="font-bold text-slate-800 text-lg">Recent Returns</h2>
                        <a href="../return/returnBook.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline uppercase tracking-wide">View All</a>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left text-sm table-modern">
                            <thead>
                                <tr>
                                    <th class="pl-6">Book Item</th>
                                    <th>Return Date</th>
                                    <th class="pr-6 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($returns->num_rows > 0): ?>
                                    <?php while ($row = $returns->fetch_assoc()) { ?>
                                    <tr class="table-row-modern group cursor-default">
                                        <td class="p-4 pl-6">
                                            <div class="font-semibold text-slate-700"><?= $row['Title'] ?></div>
                                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Ref: #<?= $row['Return_ID'] ?></div>
                                        </td>
                                        <td class="p-4 text-slate-500 font-mono text-xs"><?= date('M d, Y', strtotime($row['Return_Date'])) ?></td>
                                        <td class="p-4 pr-6 text-right">
                                            <?php if($row['Fine_Amount'] > 0): ?>
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-bold bg-red-50 text-red-600 border border-red-100">
                                                    Fine: ₹<?= $row['Fine_Amount'] ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                                    Cleared
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="p-8 text-center text-slate-400 text-sm">No recent returns.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

</body>
</html>
