<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

if (!isset($_GET['id'])) {
    header("Location: members.php");
    exit;
}

$member_id = $_GET['id'];

// Fetch Member Details
$mem_stmt = $conn->prepare("SELECT * FROM Member WHERE Member_ID = ?");
$mem_stmt->bind_param("i", $member_id);
$mem_stmt->execute();
$member = $mem_stmt->get_result()->fetch_assoc();

if (!$member) {
    header("Location: members.php");
    exit;
}

// Fetch Fine Rate
$rate_result = $conn->query("SELECT Setting_Value FROM Library_Settings WHERE Setting_Key = 'Fine_Rate_Per_Day'");
$fine_rate   = ($rate_result && $rate_result->num_rows > 0) ? (float)$rate_result->fetch_assoc()['Setting_Value'] : 10.0;

/* Fine rate: ₹10 per day (fixed) */
define('FINE_RATE_PER_DAY', 10.00);
$fine_rate = FINE_RATE_PER_DAY;

// Fetch Borrowing History - join Fine table for accurate DATEDIFF-based data
$history = $conn->prepare("
    SELECT I.Issue_ID, I.Issue_Date, I.Due_Date, I.Book_ID,
           B.Title, B.ISBN,
           L.Librarian_Name,
           R.Return_Date,
           R.Fine_Amount    AS Return_Fine,
           F.Fine_ID,
           F.Days_Late,
           F.Fine_Rate,
           F.Fine_Amount    AS Fine_Table_Amount
    FROM Issue I
    JOIN Book B ON I.Book_ID = B.Book_ID
    LEFT JOIN Librarian L ON I.Librarian_ID = L.Librarian_ID
    LEFT JOIN Return_Book R ON I.Issue_ID = R.Issue_ID
    LEFT JOIN Fine F ON I.Issue_ID = F.Issue_ID
    WHERE I.Member_ID = ?
    ORDER BY I.Issue_Date DESC
");
$history->bind_param("i", $member_id);
$history->execute();
$result = $history->get_result();

$total_borrowed  = 0;
$total_returned  = 0;
$total_fines     = 0.0;
$total_days_late = 0;
$rows = [];
while ($row = $result->fetch_assoc()) {
    $total_borrowed++;
    if (!empty($row['Return_Date'])) {
        $total_returned++;
        // Use Fine table amount (DATEDIFF-based) if a fine record exists, else fall back
        $fine_amt = ($row['Fine_ID'] !== null) ? (float)$row['Fine_Table_Amount'] : (float)$row['Return_Fine'];
        $total_fines     += $fine_amt;
        $total_days_late += (int)($row['Days_Late'] ?? 0);
    }
    $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Member History</title>
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
                    <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Member Borrowing History</h1>
                    <div class="flex items-center gap-2 mt-2 text-slate-500">
                        <span class="font-semibold text-indigo-600"><?= htmlspecialchars($member['Member_Name']) ?></span>
                        <span>&bull;</span>
                        <span><?= htmlspecialchars($member['Email']) ?></span>
                    </div>
                </div>
                <div>
                    <a href="members.php" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-800 px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-sm flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Back to Members
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Borrowed -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium uppercase tracking-wider">Total Borrowed</p>
                        <h3 class="text-2xl font-bold text-slate-800"><?= $total_borrowed ?></h3>
                    </div>
                </div>
                <!-- Total Returned -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium uppercase tracking-wider">Returned</p>
                        <h3 class="text-2xl font-bold text-slate-800"><?= $total_returned ?></h3>
                    </div>
                </div>
                <!-- Total Fines -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border <?= $total_fines > 0 ? 'border-red-100' : 'border-slate-100' ?> flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full <?= $total_fines > 0 ? 'bg-red-50 text-red-500' : 'bg-slate-50 text-slate-400' ?> flex items-center justify-center text-xl">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium uppercase tracking-wider">Total Fines</p>
                        <h3 class="text-2xl font-bold <?= $total_fines > 0 ? 'text-red-600' : 'text-slate-800' ?>">₹<?= number_format($total_fines, 2) ?></h3>
                    </div>
                </div>
            </div>

            <!-- Fine Formula Notice -->
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-3 flex items-center gap-3 text-sm text-indigo-700">
                <i class="fas fa-calculator text-indigo-500"></i>
                <span>Fine formula (MySQL): <strong>DATEDIFF(Return_Date, Due_Date) &times; &#8377;<?= FINE_RATE_PER_DAY ?>/day</strong> &mdash; Only when DATEDIFF &gt; 0. Stored in <code class="bg-indigo-100 px-1 rounded text-xs">Fine</code> table.</span>
            </div>

            <!-- History Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h2 class="font-bold text-slate-700">Borrowing History</h2>
                    <span class="text-xs text-slate-400"><?= $total_borrowed ?> record<?= $total_borrowed != 1 ? 's' : '' ?></span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Issue ID</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Book Details</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Issued By</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Issue Date</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Return Date</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider text-right">Fine</th>
                                <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (!empty($rows)): ?>
                                <?php foreach ($rows as $row): ?>
                                <?php
                                    $isReturned = !empty($row['Return_Date']);
                                    $dueTs      = strtotime($row['Due_Date']);
                                    $isOverdue  = !$isReturned && time() > $dueTs;

                                    // Fine data from the Fine table (DATEDIFF-based)
                                    $has_fine_record  = $row['Fine_ID'] !== null;
                                    $days_late_display = $has_fine_record ? (int)$row['Days_Late'] : 0;
                                    $fine_rate_used    = $has_fine_record ? (float)$row['Fine_Rate'] : FINE_RATE_PER_DAY;
                                    $fine_amt          = $has_fine_record
                                        ? (float)$row['Fine_Table_Amount']
                                        : (float)($row['Return_Fine'] ?? 0);
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 font-mono text-xs text-slate-400">#<?= $row['Issue_ID'] ?></td>
                                    <td class="p-4">
                                        <div class="font-medium text-slate-800"><?= htmlspecialchars($row['Title']) ?></div>
                                        <div class="text-xs text-slate-400">ISBN: <?= htmlspecialchars($row['ISBN']) ?></div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-medium text-slate-700"><?= htmlspecialchars($row['Librarian_Name'] ?? 'Unknown') ?></div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <i class="far fa-calendar text-slate-400"></i>
                                            <?= date('M d, Y', strtotime($row['Issue_Date'])) ?>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2 <?= (!$isReturned && $isOverdue) ? 'text-red-500 font-semibold' : '' ?>">
                                            <i class="far fa-calendar-alt <?= (!$isReturned && $isOverdue) ? 'text-red-400' : 'text-slate-400' ?>"></i>
                                            <?= date('M d, Y', strtotime($row['Due_Date'])) ?>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <?php if ($isReturned): ?>
                                            <div class="flex items-center gap-2 text-emerald-600">
                                                <i class="far fa-calendar-check text-emerald-400"></i>
                                                <?= date('M d, Y', strtotime($row['Return_Date'])) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-slate-300 text-xs italic">Not yet returned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-right">
                                        <?php if ($isReturned): ?>
                                            <?php if ($fine_amt > 0): ?>
                                                <div>
                                                    <span class="font-bold text-red-600">&#8377;<?= number_format($fine_amt, 2) ?></span>
                                                    <?php if ($days_late_display > 0): ?>
                                                    <div class="text-xs text-slate-400 mt-0.5">
                                                        DATEDIFF = <?= $days_late_display ?> &times; &#8377;<?= $fine_rate_used ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-emerald-500 font-semibold text-xs">No Fine</span>
                                            <?php endif; ?>
                                        <?php elseif ($isOverdue): ?>
                                            <?php
                                            $daysOv  = (int)floor((time() - $dueTs) / 86400);
                                            $estFine = $daysOv * FINE_RATE_PER_DAY;
                                            ?>
                                            <div>
                                                <span class="font-bold text-orange-500">~&#8377;<?= number_format($estFine, 2) ?></span>
                                                <div class="text-xs text-slate-400 mt-0.5">DATEDIFF = <?= $daysOv ?> &times; &#8377;<?= FINE_RATE_PER_DAY ?></div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-slate-300 text-xs">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if ($isReturned): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-100">
                                                <i class="fas fa-check mr-1 text-xs"></i> Returned
                                            </span>
                                        <?php elseif ($isOverdue): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-100">
                                                <i class="fas fa-exclamation-triangle mr-1 text-xs"></i> Overdue
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-600 border border-blue-100">
                                                <i class="fas fa-clock mr-1 text-xs"></i> Active
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-400 italic">No borrowing history found for this member.</td>
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
