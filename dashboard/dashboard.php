<?php
require_once "../config/app.php";
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

include "../config/db.php";

// Stats
$totalBooks = $conn->query("SELECT IFNULL(SUM(Quantity),0) AS total FROM book")->fetch_assoc()['total'];
$totalMembers = $conn->query("SELECT COUNT(*) AS total FROM member")->fetch_assoc()['total'];
$issuedBooks = $conn->query("SELECT COUNT(*) AS total FROM issue WHERE Issue_ID NOT IN (SELECT Issue_ID FROM return_book)")->fetch_assoc()['total'];
$totalFines = $conn->query("SELECT IFNULL(SUM(Fine_Amount),0) AS total FROM return_book")->fetch_assoc()['total'];

// Recent Issues
$issues = $conn->query("
    SELECT I.Issue_ID, B.Title, M.Member_Name, I.Issue_Date, I.Due_Date
    FROM issue I
    JOIN book B ON I.Book_ID = B.Book_ID
    JOIN member M ON I.Member_ID = M.Member_ID
    ORDER BY I.Issue_Date DESC
    LIMIT 5
");

// Recent Returns
$returns = $conn->query("
    SELECT R.Return_ID, B.Title, R.Return_Date, R.Fine_Amount
    FROM return_book R
    JOIN issue I ON R.Issue_ID = I.Issue_ID
    JOIN book B ON I.Book_ID = B.Book_ID
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
    <div class="main-content flex-1 ml-0 md:ml-64 flex flex-col relative z-0 min-h-screen bg-slate-50/50">
        <!-- Ambient Background Orbs -->
        <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
            <div class="absolute top-[-10%] left-[-5%] w-96 h-96 bg-indigo-300/20 rounded-full blur-3xl mix-blend-multiply opacity-70 animate-blob"></div>
            <div class="absolute top-[20%] right-[-5%] w-96 h-96 bg-fuchsia-300/20 rounded-full blur-3xl mix-blend-multiply opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-10%] left-[20%] w-96 h-96 bg-cyan-300/20 rounded-full blur-3xl mix-blend-multiply opacity-70 animate-blob animation-delay-4000"></div>
        </div>
        <!-- Top Navigation -->
        <?php include "../includers/navbar.php"; ?>
        
        <!-- Dashboard Content -->
        
        <!-- Dashboard Content -->
        <main class="p-4 md:p-8 space-y-8">
            
            <!-- Welcome Section -->
            <div class="relative bg-gradient-brand rounded-3xl p-6 md:p-10 text-white overflow-hidden shadow-xl shadow-indigo-500/20 animate-enter">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl transform translate-x-1/3 -translate-y-1/3"></div>
                <div class="absolute bottom-0 left-10 w-64 h-64 bg-indigo-400 opacity-20 rounded-full blur-2xl transform -translate-x-1/2 translate-y-1/2"></div>
                <div class="absolute right-20 bottom-10 opacity-10">
                    <i class="fas fa-book-reader text-9xl transform -rotate-12"></i>
                </div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div class="max-w-xl">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-semibold tracking-wide text-white border border-white/30 mb-4">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                            System Online
                        </span>
                        <h1 class="text-3xl md:text-4xl font-bold font-inter tracking-tight mb-3">Welcome Back, Admin! 👋</h1>
                        <p class="text-indigo-100 font-medium text-sm md:text-base leading-relaxed">
                            Overview of library performance and activities. Ready to dive into today's statistics?
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2.5 rounded-xl flex items-center gap-2 text-sm font-semibold text-white shadow-sm">
                            <i class="far fa-calendar-alt text-indigo-200"></i>
                            <?= date('M j, Y') ?>
                        </div>
                        <button class="bg-white text-indigo-600 hover:bg-slate-50 px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-all transform hover:-translate-y-0.5 shadow-md hover:shadow-xl" onclick="window.location.href='../issue/issueBook.php'">
                            <i class="fas fa-bolt"></i> Quick Issue
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Books -->
                <div class="premium-card relative overflow-hidden p-6 rounded-2xl border border-slate-200/60 shadow-sm hover-card group animate-enter delay-100">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full blur-3xl -mr-16 -mt-16 transition-all group-hover:bg-indigo-100"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="h-12 w-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
                                <i class="fas fa-book text-lg"></i>
                            </div>
                            <span class="badge bg-green-50 text-green-700 border border-green-100 flex items-center gap-1 shadow-sm">
                                <i class="fas fa-arrow-up text-[10px]"></i> 2.5%
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Total Books</p>
                            <h3 class="text-3xl font-extrabold text-slate-800 mt-1 font-inter tracking-tight"><?= number_format($totalBooks) ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Total Members -->
                <div class="premium-card relative overflow-hidden p-6 rounded-2xl border border-slate-200/60 shadow-sm hover-card group animate-enter delay-200">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-pink-50 rounded-full blur-3xl -mr-16 -mt-16 transition-all group-hover:bg-pink-100"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="h-12 w-12 rounded-xl bg-pink-50 border border-pink-100 flex items-center justify-center text-pink-600 group-hover:bg-pink-600 group-hover:text-white transition-all duration-300 shadow-sm">
                                <i class="fas fa-users text-lg"></i>
                            </div>
                            <span class="badge bg-green-50 text-green-700 border border-green-100 flex items-center gap-1 shadow-sm">
                                <i class="fas fa-arrow-up text-[10px]"></i> 12%
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Members</p>
                            <h3 class="text-3xl font-extrabold text-slate-800 mt-1 font-inter tracking-tight"><?= number_format($totalMembers) ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Issued Books -->
                <div class="premium-card relative overflow-hidden p-6 rounded-2xl border border-slate-200/60 shadow-sm hover-card group animate-enter delay-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full blur-3xl -mr-16 -mt-16 transition-all group-hover:bg-amber-100"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="h-12 w-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all duration-300 shadow-sm">
                                <i class="fas fa-hand-holding flex items-center justify-center text-lg"></i>
                            </div>
                            <span class="badge bg-slate-50 text-slate-600 border border-slate-100 shadow-sm">Active</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Result Issued</p>
                            <h3 class="text-3xl font-extrabold text-slate-800 mt-1 font-inter tracking-tight"><?= number_format($issuedBooks) ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Fines -->
                <div class="premium-card relative overflow-hidden p-6 rounded-2xl border border-slate-200/60 shadow-sm hover-card group animate-enter delay-400">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-full blur-3xl -mr-16 -mt-16 transition-all group-hover:bg-red-100"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="h-12 w-12 rounded-xl bg-red-50 border border-red-100 flex items-center justify-center text-red-600 group-hover:bg-red-600 group-hover:text-white transition-all duration-300 shadow-sm">
                                <i class="fas fa-rupee-sign text-lg"></i>
                            </div>
                            <span class="badge bg-red-50 text-red-700 border border-red-100 shadow-sm">Action</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Unpaid Fines</p>
                            <h3 class="text-3xl font-extrabold text-slate-800 mt-1 font-inter tracking-tight">₹<?= number_format($totalFines) ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Issues -->
                <div class="glass-panel rounded-3xl overflow-hidden animate-enter delay-500 flex flex-col border border-slate-200/60 shadow-sm">
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
                <div class="glass-panel rounded-3xl overflow-hidden animate-enter delay-500 flex flex-col border border-slate-200/60 shadow-sm">
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



