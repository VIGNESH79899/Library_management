<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

/* Fine rate is fixed at ₹10/day */
define('FINE_RATE_PER_DAY', 10.00);
$fine_rate = FINE_RATE_PER_DAY;
$rate_msg  = "";

// ── Core stats ────────────────────────────────────────────────
$totalBooks    = $conn->query("SELECT COUNT(*) AS total FROM Book")->fetch_assoc()['total'];
$totalMembers  = $conn->query("SELECT COUNT(*) AS total FROM Member")->fetch_assoc()['total'];
$totalIssued   = $conn->query("SELECT COUNT(*) AS total FROM Book WHERE Status='Issued'")->fetch_assoc()['total'];
$totalReturned = $conn->query("SELECT COUNT(*) AS total FROM Return_Book")->fetch_assoc()['total'];
$totalOverdue  = $conn->query("SELECT COUNT(*) AS total FROM Issue I JOIN Book B ON I.Book_ID = B.Book_ID WHERE B.Status='Issued' AND I.Due_Date < CURDATE()")->fetch_assoc()['total'];

// ── Fines from dedicated Fine table ───────────────────────────
// Total collected (only rows that exist in Fine table = actual late returns)
$totalFinesRes = $conn->query("SELECT IFNULL(SUM(Fine_Amount),0) AS total, COUNT(*) AS count, IFNULL(SUM(Days_Late),0) AS total_days FROM Fine");
$fineStats     = $totalFinesRes ? $totalFinesRes->fetch_assoc() : ['total'=>0,'count'=>0,'total_days'=>0];
$totalFines    = $fineStats['total'];
$fineCount     = $fineStats['count'];
$totalDaysLate = $fineStats['total_days'];

// ── Pending fines for currently overdue (not yet returned) books
// Uses DATEDIFF(CURDATE(), Due_Date) × ₹10 to estimate
$pendingFineRes = $conn->query("
    SELECT
        COUNT(*)                                                      AS overdue_count,
        IFNULL(SUM(DATEDIFF(CURDATE(), I.Due_Date)), 0)               AS total_days_pending,
        IFNULL(SUM(DATEDIFF(CURDATE(), I.Due_Date) * " . FINE_RATE_PER_DAY . "), 0) AS pending_amount
    FROM Issue I
    JOIN Book B ON I.Book_ID = B.Book_ID
    WHERE B.Status = 'Issued'
      AND I.Due_Date < CURDATE()
");
$pendingStats  = $pendingFineRes ? $pendingFineRes->fetch_assoc() : ['overdue_count'=>0,'total_days_pending'=>0,'pending_amount'=>0];
$pendingFines  = $pendingStats['pending_amount'];

// Most Popular Books (Most Issued)
$popularBooks = $conn->query("
    SELECT B.Title, COUNT(I.Issue_ID) as Issue_Count
    FROM Issue I
    JOIN Book B ON I.Book_ID = B.Book_ID
    GROUP BY I.Book_ID
    ORDER BY Issue_Count DESC
    LIMIT 5
");

// Member Activity – join Fine table for accurate totals
$activeMembers = $conn->query("
    SELECT M.Member_Name, COUNT(I.Issue_ID) as Issue_Count,
           IFNULL(SUM(F.Fine_Amount), 0) AS Total_Fines,
           IFNULL(SUM(F.Days_Late), 0)   AS Total_Days_Late
    FROM Issue I
    JOIN Member M ON I.Member_ID = M.Member_ID
    LEFT JOIN Fine F ON I.Issue_ID = F.Issue_ID
    GROUP BY I.Member_ID
    ORDER BY Issue_Count DESC
    LIMIT 5
");

// Recent fine transactions — from the Fine table
$recentFines = $conn->query("
    SELECT F.Fine_ID, F.Days_Late, F.Fine_Rate, F.Fine_Amount,
           F.Due_Date, F.Return_Date, F.Created_At,
           B.Title,
           M.Member_Name
    FROM Fine F
    JOIN Issue I ON F.Issue_ID = I.Issue_ID
    JOIN Book  B ON I.Book_ID  = B.Book_ID
    JOIN Member M ON F.Member_ID = M.Member_ID
    ORDER BY F.Created_At DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Reports</title>
    <?php include "../includers/headers.php"; ?>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <?php include "../includers/sidebar.php"; ?>

    <div class="flex-1 ml-64 flex flex-col">
        <?php include "../includers/navbar.php"; ?>

        <main class="p-8 space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 tracking-tight">System Reports</h1>
                    <p class="text-slate-500 mt-1">Overview of library activity and fine collections.</p>
                </div>
                <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-sm transition-all">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </div>

            <?php if ($rate_msg): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3 text-sm animate-fade-in">
                <i class="fas fa-check-circle text-green-500"></i>
                <?= htmlspecialchars($rate_msg) ?>
            </div>
            <?php endif; ?>

            <!-- Stats Overview -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-blue-500 col-span-1">
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Books</p>
                    <h2 class="text-3xl font-bold text-gray-800 mt-1"><?= $totalBooks ?></h2>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-emerald-500 col-span-1">
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Members</p>
                    <h2 class="text-3xl font-bold text-gray-800 mt-1"><?= $totalMembers ?></h2>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-yellow-500 col-span-1">
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Issued</p>
                    <h2 class="text-3xl font-bold text-gray-800 mt-1"><?= $totalIssued ?></h2>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-indigo-500 col-span-1">
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Returned</p>
                    <h2 class="text-3xl font-bold text-gray-800 mt-1"><?= $totalReturned ?></h2>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-red-500 col-span-1">
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Fines Collected</p>
                    <h2 class="text-2xl font-bold text-red-600 mt-1">₹<?= number_format($totalFines, 0) ?></h2>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-orange-500 col-span-1">
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Overdue</p>
                    <h2 class="text-3xl font-bold text-orange-500 mt-1"><?= $totalOverdue ?></h2>
                </div>
            </div>

            <!-- Fine Info + Pending Fines -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Fine Calculation Info -->
                <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 p-6">
                    <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-calculator text-indigo-500"></i> Fine Calculation Logic
                    </h3>
                    <div class="bg-indigo-50 rounded-xl p-4 mb-4">
                        <p class="text-xs text-indigo-400 font-semibold uppercase tracking-widest mb-2">Formula (MySQL)</p>
                        <code class="text-sm font-mono text-indigo-700 font-bold block leading-relaxed">
                            Fine = DATEDIFF(Return_Date, Due_Date) &times; &#8377;<?= FINE_RATE_PER_DAY ?>/day
                        </code>
                        <p class="text-xs text-indigo-400 mt-2">Only applied when DATEDIFF &gt; 0 (overdue). Result stored in <strong>Fine</strong> table.</p>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-slate-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-slate-400 font-medium">Rate/Day</p>
                            <p class="text-lg font-extrabold text-indigo-600">&#8377;<?= number_format($fine_rate, 0) ?></p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-slate-400 font-medium">Fine Records</p>
                            <p class="text-lg font-extrabold text-red-500"><?= $fineCount ?></p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-slate-400 font-medium">Total Days Late</p>
                            <p class="text-lg font-extrabold text-orange-500"><?= $totalDaysLate ?></p>
                        </div>
                    </div>
                </div>

                <!-- Pending Fines -->
                <div class="bg-white rounded-2xl shadow-sm border border-orange-100 p-6">
                    <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-hourglass-half text-orange-500"></i> Pending Fines (Unreturned)
                    </h3>
                    <div class="bg-orange-50 rounded-xl p-4 flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs text-orange-400 font-semibold uppercase tracking-wider">Estimated via DATEDIFF</p>
                            <p class="text-3xl font-extrabold text-orange-600 mt-1">&#8377;<?= number_format($pendingFines, 2) ?></p>
                            <p class="text-xs text-orange-400 mt-1"><?= $pendingStats['overdue_count'] ?> overdue &bull; <?= $pendingStats['total_days_pending'] ?> total days</p>
                        </div>
                        <div class="text-orange-200 text-4xl">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">Estimated using <code class="bg-slate-100 px-1 rounded">DATEDIFF(CURDATE(), Due_Date) &times; &#8377;<?= FINE_RATE_PER_DAY ?></code> for each overdue book. Final fine is recorded on return.</p>
                </div>
            </div>

            <!-- Recent Fine Transactions -->
            <?php if ($recentFines && $recentFines->num_rows > 0): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="font-bold text-slate-700 flex items-center gap-2">
                        <i class="fas fa-receipt text-red-400"></i> Recent Fine Transactions
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Last 10 returns with fines collected</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Book</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Member</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Return Date</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider text-center">Days Late</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider text-right">Fine</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while ($fr = $recentFines->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-4">
                                    <span class="font-medium text-slate-800"><?= htmlspecialchars($fr['Title']) ?></span>
                                </td>
                                <td class="p-4 text-slate-600"><?= htmlspecialchars($fr['Member_Name']) ?></td>
                                <td class="p-4 text-red-500 font-medium"><?= date('M d, Y', strtotime($fr['Due_Date'])) ?></td>
                                <td class="p-4 text-slate-600"><?= date('M d, Y', strtotime($fr['Return_Date'])) ?></td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-100">
                                        <?= $fr['Days_Late'] ?> day<?= $fr['Days_Late'] != 1 ? 's' : '' ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <span class="font-bold text-red-600 text-base">₹<?= number_format($fr['Fine_Amount'], 2) ?></span>
                                    <div class="text-xs text-slate-400"><?= $fr['Days_Late'] ?> × ₹<?= number_format($fine_rate, 0) ?></div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Popular Books -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-base font-bold mb-4 border-b pb-2 text-gray-700 flex items-center gap-2">
                        <i class="fas fa-fire text-orange-400"></i> Top 5 Most Popular Books
                    </h2>
                    <ul class="divide-y divide-gray-100">
                        <?php while ($b = $popularBooks->fetch_assoc()): ?>
                        <li class="py-3 flex justify-between items-center">
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($b['Title']) ?></span>
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                                <?= $b['Issue_Count'] ?> issues
                            </span>
                        </li>
                        <?php endwhile; ?>
                        <?php if ($popularBooks->num_rows == 0): ?>
                        <p class="text-gray-400 text-sm">No data available yet.</p>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Active Members -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-base font-bold mb-4 border-b pb-2 text-gray-700 flex items-center gap-2">
                        <i class="fas fa-users text-indigo-400"></i> Top 5 Most Active Members
                    </h2>
                    <ul class="divide-y divide-gray-100">
                        <?php while ($m = $activeMembers->fetch_assoc()): ?>
                        <li class="py-3 flex justify-between items-center">
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($m['Member_Name']) ?></span>
                            <div class="flex items-center gap-2">
                                <?php if ($m['Total_Fines'] > 0): ?>
                                <span class="bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded-full">
                                    ₹<?= number_format($m['Total_Fines'], 0) ?> fines
                                </span>
                                <?php endif; ?>
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    <?= $m['Issue_Count'] ?> issues
                                </span>
                            </div>
                        </li>
                        <?php endwhile; ?>
                        <?php if ($activeMembers->num_rows == 0): ?>
                        <p class="text-gray-400 text-sm">No data available yet.</p>
                        <?php endif; ?>
                    </ul>
                </div>

            </div>

        </main>
    </div>
</div>

</body>
</html>