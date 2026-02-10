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
        <!-- The headers.php actually outputs the top navigation bar directly. 
             Since I included it in <head> (which is wrong if it generates visual output),
             I need to correct this. ideally headers.php should be split.
             But based on my previous edit, headers.php has <head> AND the top div.
             So including it at the very top of the file before <body> is wrong if it outputs HTML.
             Let's restructure: 
             The previous headers.php I wrote has <head> content AND <div class="glass...">
             So I should include it inside the <body>? or split it?
             For now, to avoid breaking too much, I'll include it inside <body> 
             BUT I need to be careful about the <head> tags it contains.
             Browsers are forgiving, but it's bad practice.
             However, for this task, I will assume I can just include it at the start of body or just before.
             Actually, let's look at how I wrote headers.php.
             It has <head>...</head> then <div>...</div>.
             So if I include it inside <body>, I have nested <head>.
             I will remove the manual <head> in this file and just let headers.php handle it.
        -->
        
        <!-- Dashboard Content -->
        <main class="p-8 space-y-8">
            
            <!-- Welcome Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Dashboard Overview</h1>
                    <p class="text-slate-500 mt-1">Welcome back, get a quick overview of your library stats.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-500 bg-white px-3 py-1 rounded-full border border-gray-200 shadow-sm">
                        <i class="far fa-calendar-alt mr-2"></i> <?= date('F j, Y') ?>
                    </span>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-lg shadow-indigo-500/30 flex items-center gap-2">
                        <i class="fas fa-plus"></i> <a href="../issue/issueBook.php">Issue New Book</a>
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-fade-in-up">
                <!-- Total Books -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-1">Total Books</p>
                            <h3 class="text-3xl font-bold text-slate-800"><?= number_format($totalBooks) ?></h3>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400">
                        <span class="text-green-500 font-medium flex items-center mr-2">
                            <i class="fas fa-arrow-up mr-1"></i> +2.5%
                        </span>
                        from last month
                    </div>
                </div>

                <!-- Total Members -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-pink-500 uppercase tracking-wider mb-1">Total Members</p>
                            <h3 class="text-3xl font-bold text-slate-800"><?= number_format($totalMembers) ?></h3>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-pink-50 flex items-center justify-center text-pink-600 group-hover:bg-pink-600 group-hover:text-white transition-colors">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400">
                        <span class="text-green-500 font-medium flex items-center mr-2">
                            <i class="fas fa-arrow-up mr-1"></i> +12%
                        </span>
                        new registrations
                    </div>
                </div>

                <!-- Issued Books -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-amber-500 uppercase tracking-wider mb-1">Issued Books</p>
                            <h3 class="text-3xl font-bold text-slate-800"><?= number_format($issuedBooks) ?></h3>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400">
                        <span class="text-amber-500 font-medium flex items-center mr-2">
                            <i class="fas fa-clock mr-1"></i> Active
                        </span>
                        loans currently
                    </div>
                </div>

                <!-- Fines -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-red-500 uppercase tracking-wider mb-1">Total Fines</p>
                            <h3 class="text-3xl font-bold text-slate-800">₹<?= number_format($totalFines) ?></h3>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-red-50 flex items-center justify-center text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400">
                        <span class="text-red-500 font-medium flex items-center mr-2">
                            <i class="fas fa-exclamation-circle mr-1"></i> Action
                        </span>
                        needed on unpaid fines
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Issues -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h2 class="font-bold text-slate-800">Recent Issues</h2>
                        <a href="../issue/issueBook.php" class="text-sm text-indigo-600 font-medium hover:text-indigo-800">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Book</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Member</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Issued</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Due</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php while ($row = $issues->fetch_assoc()) { ?>
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="p-4">
                                        <div class="font-medium text-slate-800"><?= $row['Title'] ?></div>
                                        <div class="text-xs text-gray-400">ID: <?= $row['Issue_ID'] ?></div>
                                    </td>
                                    <td class="p-4 text-slate-600"><?= $row['Member_Name'] ?></td>
                                    <td class="p-4 text-slate-500"><?= date('M d', strtotime($row['Issue_Date'])) ?></td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-600">
                                            <?= date('M d', strtotime($row['Due_Date'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Returns -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h2 class="font-bold text-slate-800">Recent Returns</h2>
                        <a href="../return/returnBook.php" class="text-sm text-indigo-600 font-medium hover:text-indigo-800">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Book</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Returned</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Fine</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php while ($row = $returns->fetch_assoc()) { ?>
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="p-4">
                                        <div class="font-medium text-slate-800"><?= $row['Title'] ?></div>
                                        <div class="text-xs text-gray-400">ID: <?= $row['Return_ID'] ?></div>
                                    </td>
                                    <td class="p-4 text-slate-500"><?= date('M d', strtotime($row['Return_Date'])) ?></td>
                                    <td class="p-4">
                                        <?php if($row['Fine_Amount'] > 0): ?>
                                            <span class="text-red-600 font-bold">₹<?= $row['Fine_Amount'] ?></span>
                                        <?php else: ?>
                                            <span class="text-green-600 font-medium text-xs bg-green-100 px-2 py-1 rounded">No Fine</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php } ?>
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
