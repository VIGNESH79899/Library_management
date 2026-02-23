<?php
require_once "../config/app.php";
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

include "../config/db.php";

/* Fine rate: ₹10 per day (fixed) */
define('FINE_RATE_PER_DAY', 10.00);
$fine_rate = FINE_RATE_PER_DAY;

/* Fetch issued books with issue details */
$issues = $conn->query("
    SELECT I.Issue_ID, B.Book_ID, B.Title, I.Issue_Date, I.Due_Date,
           M.Member_Name, M.Member_ID
    FROM issue I
    JOIN book B ON I.Book_ID = B.Book_ID
    JOIN member M ON I.Member_ID = M.Member_ID
    WHERE B.Status = 'Issued'
    ORDER BY I.Due_Date ASC
");

$message    = "";
$error      = "";
$last_fine  = 0;
$last_days  = 0;
$last_title = "";

/* ─── Return Book Logic ─────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $issue_id    = (int)($_POST['issue_id'] ?? 0);
    $return_date = date('Y-m-d'); // always today

    if ($issue_id > 0) {

        /*
         * Fetch the issue row including Member_ID and Due_Date.
         * Use MySQL DATEDIFF to compute days late right in the query
         * so the calculation is 100% server-authoritative.
         *
         * DATEDIFF(return_date, due_date) > 0  →  book is overdue
         */
        $stmt_check = $conn->prepare("
            SELECT I.Book_ID, I.Member_ID, I.Due_Date,
                   B.Title,
                   GREATEST(DATEDIFF(?, I.Due_Date), 0)           AS Days_Late,
                   GREATEST(DATEDIFF(?, I.Due_Date), 0) * ?       AS Fine_Amount
            FROM issue I
            JOIN book B ON I.Book_ID = B.Book_ID
            WHERE I.Issue_ID = ?
        ");
        $stmt_check->bind_param("ssdi", $return_date, $return_date, $fine_rate, $issue_id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $row_check  = $res_check->fetch_assoc();
            $book_id    = (int)$row_check['Book_ID'];
            $member_id  = (int)$row_check['Member_ID'];
            $due_date   = $row_check['Due_Date'];
            $last_title = $row_check['Title'];
            $days_late  = (int)$row_check['Days_Late'];     // DATEDIFF result
            $fine       = (float)$row_check['Fine_Amount'];  // days × ₹10
            $last_fine  = $fine;
            $last_days  = $days_late;

            /* ── Transaction ──────────────────────────────────────────── */
            $conn->begin_transaction();
            try {
                /* 1. Insert into Return_Book */
                $stmt1 = $conn->prepare("
                    INSERT INTO return_book (Issue_ID, Return_Date, Fine_Amount)
                    VALUES (?, ?, ?)
                ");
                $stmt1->bind_param("isd", $issue_id, $return_date, $fine);
                $stmt1->execute();
                $return_id = $conn->insert_id;

                /* 2. If overdue → insert into Fine table using DATEDIFF */
                if ($days_late > 0) {
                    $stmt2 = $conn->prepare("
                        INSERT INTO fine
                            (Issue_ID, Return_ID, Member_ID,
                             Due_Date, Return_Date,
                             Days_Late, Fine_Rate, Fine_Amount)
                        VALUES (?, ?, ?,
                                ?, ?,
                                DATEDIFF(?, ?), ?, ?)
                    ");
                    $stmt2->bind_param(
                        "iiissssdd",
                        $issue_id,   // Issue_ID
                        $return_id,  // Return_ID
                        $member_id,  // Member_ID
                        $due_date,   // Due_Date
                        $return_date,// Return_Date
                        $return_date,// DATEDIFF arg1
                        $due_date,   // DATEDIFF arg2
                        $fine_rate,  // Fine_Rate
                        $fine        // Fine_Amount
                    );
                    $stmt2->execute();
                }

                /* 3. Mark book as Available */
                $stmt3 = $conn->prepare("UPDATE book SET Status='Available' WHERE Book_ID=?");
                $stmt3->bind_param("i", $book_id);
                $stmt3->execute();

                $conn->commit();
                $message = "Book returned successfully!";

            } catch (Exception $e) {
                $conn->rollback();
                $error = "Transaction failed: " . $e->getMessage();
            }

            // Refresh issued list after return
            $issues = $conn->query("
                SELECT I.Issue_ID, B.Book_ID, B.Title, I.Issue_Date, I.Due_Date, M.Member_Name
                FROM issue I
                JOIN book B ON I.Book_ID = B.Book_ID
                JOIN member M ON I.Member_ID = M.Member_ID
                WHERE B.Status = 'Issued'
                ORDER BY I.Due_Date ASC
            ");

        } else {
            $error = "Invalid Issue ID.";
        }
    } else {
        $error = "Please select a book to return.";
    }
}

// Build JS data array for live preview
$js_books = [];
if ($issues && $issues->num_rows > 0) {
    $issues->data_seek(0);
    while ($row = $issues->fetch_assoc()) {
        $js_books[$row['Issue_ID']] = [
            'title'      => $row['Title'],
            'member'     => $row['Member_Name'],
            'due_date'   => $row['Due_Date'],
            'issue_date' => $row['Issue_Date'],
        ];
    }
    $issues->data_seek(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Return Book</title>
    <?php include "../includers/headers.php"; ?>
    <style>
        .fine-card-enter {
            animation: slideDown 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-12px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .overdue-badge {
            animation: pulseBadge 2s ease-in-out infinite;
        }
        @keyframes pulseBadge {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.3); }
            50%       { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        }
    </style>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include "../includers/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="flex-1 ml-64 flex flex-col relative z-0">
        
        <main class="p-8 space-y-8 flex items-center justify-center min-h-[calc(100vh-80px)]">
             <div class="w-full max-w-2xl animate-fade-in-up">
                <!-- Wrapper -->
                <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-gray-100 overflow-hidden">
                    
                    <div class="px-8 py-6 bg-gradient-to-r from-emerald-500 to-teal-600 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                        <div class="absolute bottom-0 left-0 -mb-6 -ml-6 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-1">
                                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                                    <i class="fas fa-undo text-white"></i>
                                </div>
                                <h1 class="text-2xl font-bold tracking-tight text-white">Return Book</h1>
                            </div>
                            <p class="text-emerald-100 text-sm ml-12">Fine = Days Overdue × ₹<?= number_format($fine_rate, 0) ?>/day</p>
                        </div>
                    </div>

                    <div class="p-8">

                        <?php if ($message): ?>
                            <!-- Success with fine breakdown -->
                            <div class="mb-6 animate-fade-in">
                                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-start gap-3" role="alert">
                                    <i class="fas fa-check-circle mt-0.5 text-green-500 text-lg"></i>
                                    <div>
                                        <p class="font-bold text-sm">Return Processed Successfully</p>
                                        <p class="text-sm mt-0.5">"<strong><?= htmlspecialchars($last_title) ?></strong>" has been returned.</p>
                                    </div>
                                </div>

                                <?php if ($last_fine > 0): ?>
                                <div class="mt-3 bg-gradient-to-br from-orange-50 to-red-50 border border-orange-200 rounded-xl p-5">
                                    <p class="text-xs font-bold uppercase tracking-widest text-orange-500 mb-3 flex items-center gap-2">
                                        <i class="fas fa-receipt"></i> Fine Receipt
                                    </p>
                                    <div class="space-y-2 text-sm text-slate-600">
                                        <div class="flex justify-between">
                                            <span class="text-slate-500">Days Overdue</span>
                                            <span class="font-semibold text-slate-700"><?= $last_days ?> day<?= $last_days != 1 ? 's' : '' ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500">Rate Per Day</span>
                                            <span class="font-semibold text-slate-700">₹<?= number_format($fine_rate, 2) ?></span>
                                        </div>
                                        <div class="border-t border-orange-200 mt-2 pt-2 flex justify-between items-center">
                                            <span class="font-bold text-slate-700">Total Fine</span>
                                            <span class="text-2xl font-extrabold text-red-600">₹<?= number_format($last_fine, 2) ?></span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-orange-400 mt-3 italic">Fine = <?= $last_days ?> days × ₹<?= number_format($fine_rate, 2) ?>/day</p>
                                </div>
                                <?php else: ?>
                                <div class="mt-3 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3">
                                    <i class="fas fa-award text-emerald-500 text-2xl"></i>
                                    <div>
                                        <p class="font-bold text-emerald-700 text-sm">No Fine! 🎉</p>
                                        <p class="text-emerald-600 text-xs">Returned on or before the due date.</p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-start gap-3 animate-fade-in" role="alert">
                                <i class="fas fa-exclamation-circle mt-0.5 text-red-500"></i>
                                <div>
                                    <p class="font-bold text-sm">Error</p>
                                    <p class="text-sm"><?= htmlspecialchars($error) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-5" id="returnForm">

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700" for="issue_id">Select Issued Book</label>
                                <div class="relative">
                                    <select name="issue_id" id="issue_id" required onchange="updateFinePreview(this.value)"
                                            class="w-full pl-4 pr-10 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500 transition-all appearance-none cursor-pointer text-slate-700 text-sm">
                                        <option value="">-- Choose Book to Return --</option>
                                        <?php 
                                        if ($issues && $issues->num_rows > 0) {
                                            $issues->data_seek(0);
                                            while ($row = $issues->fetch_assoc()) {
                                                $today = date('Y-m-d');
                                                $isLate = $today > $row['Due_Date'];
                                                $finePreview = 0;
                                                if ($isLate) {
                                                    $d = floor((strtotime($today) - strtotime($row['Due_Date'])) / 86400);
                                                    $finePreview = $d * $fine_rate;
                                                }
                                                $lateTag = $isLate ? " ⚠ ₹" . number_format($finePreview, 0) . " fine" : "";
                                                ?>
                                                <option value="<?= $row['Issue_ID'] ?>">
                                                    <?= htmlspecialchars($row['Title']) ?> — <?= htmlspecialchars($row['Member_Name']) ?> (Due: <?= date('M d', strtotime($row['Due_Date'])) ?><?= $lateTag ?>)
                                                </option>
                                                <?php
                                            }
                                        } else { ?>
                                            <option value="" disabled>No outstanding issued books</option>
                                        <?php } ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Live Fine Preview Card -->
                            <div id="finePreviewCard" class="hidden">
                                <div class="rounded-xl border overflow-hidden">
                                    <!-- Book info header -->
                                    <div class="bg-slate-50 border-b px-4 py-3 flex items-center justify-between">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Loan Details</p>
                                            <p id="preview_title" class="font-semibold text-slate-800 text-sm mt-0.5 truncate max-w-xs"></p>
                                        </div>
                                        <div id="preview_status_badge" class=""></div>
                                    </div>
                                    <!-- Dates row -->
                                    <div class="grid grid-cols-3 divide-x bg-white">
                                        <div class="px-4 py-3">
                                            <p class="text-xs text-slate-400 font-medium">Issue Date</p>
                                            <p id="preview_issue" class="text-sm font-semibold text-slate-700 mt-0.5"></p>
                                        </div>
                                        <div class="px-4 py-3">
                                            <p class="text-xs text-slate-400 font-medium">Due Date</p>
                                            <p id="preview_due" class="text-sm font-semibold mt-0.5"></p>
                                        </div>
                                        <div class="px-4 py-3">
                                            <p class="text-xs text-slate-400 font-medium">Return Date</p>
                                            <p id="preview_return" class="text-sm font-semibold text-slate-700 mt-0.5"><?= date('M d, Y') ?></p>
                                        </div>
                                    </div>
                                    <!-- Fine breakdown -->
                                    <div id="fineBreakdown" class="hidden bg-gradient-to-r from-red-50 to-orange-50 border-t border-orange-100 px-4 py-4">
                                        <p class="text-xs font-bold uppercase tracking-widest text-orange-500 mb-2 flex items-center gap-1.5">
                                            <i class="fas fa-calculator"></i> Fine Calculation
                                        </p>
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="space-y-1 text-slate-500">
                                                <p>Days overdue: <span id="preview_days" class="font-bold text-slate-700"></span></p>
                                                <p>Rate per day: <span class="font-bold text-slate-700">₹<?= number_format($fine_rate, 2) ?></span></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-slate-400 mb-0.5">Total Fine</p>
                                                <p id="preview_fine" class="text-3xl font-extrabold text-red-600"></p>
                                            </div>
                                        </div>
                                        <p id="preview_formula" class="text-xs text-orange-400 mt-2 italic"></p>
                                    </div>
                                    <!-- No fine -->
                                    <div id="noFineBlock" class="hidden bg-emerald-50 border-t border-emerald-100 px-4 py-3 flex items-center gap-3">
                                        <i class="fas fa-award text-emerald-500 text-xl"></i>
                                        <div>
                                            <p class="text-sm font-bold text-emerald-700">No Fine</p>
                                            <p class="text-xs text-emerald-500">Returned within the due date — no charges apply.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Policy Info -->
                            <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100 text-sm text-emerald-800">
                                <div class="flex gap-3">
                                    <i class="fas fa-info-circle mt-0.5 text-emerald-500"></i>
                                    <div>
                                        <p class="font-bold text-xs uppercase tracking-wide text-emerald-600 mb-1">Fine Policy</p>
                                        <p>Fine = (Return Date – Due Date) × <span class="font-bold">₹<?= number_format($fine_rate, 2) ?>/day</span>. No fine applies if returned on or before the due date.</p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-500/30 transition-all transform active:scale-95 flex items-center justify-center gap-2 text-base">
                                <i class="fas fa-undo"></i>
                                <span>Confirm Return</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Book data from PHP
const books = <?= json_encode($js_books) ?>;
const fineRate = <?= $fine_rate ?>;

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-IN', { month: 'short', day: 'numeric', year: 'numeric' });
}

function daysBetween(date1Str, date2Str) {
    const d1 = new Date(date1Str + 'T00:00:00');
    const d2 = new Date(date2Str + 'T00:00:00');
    return Math.floor((d2 - d1) / (1000 * 60 * 60 * 24));
}

function updateFinePreview(issueId) {
    const card = document.getElementById('finePreviewCard');
    const fineBreakdown = document.getElementById('fineBreakdown');
    const noFineBlock  = document.getElementById('noFineBlock');

    if (!issueId || !books[issueId]) {
        card.classList.add('hidden');
        return;
    }

    const book      = books[issueId];
    const today     = new Date().toISOString().split('T')[0]; // yyyy-mm-dd
    const daysLate  = daysBetween(book.due_date, today);

    // Populate fields
    document.getElementById('preview_title').textContent  = book.title;
    document.getElementById('preview_issue').textContent  = formatDate(book.issue_date);
    document.getElementById('preview_due').textContent    = formatDate(book.due_date);

    const dueCell = document.getElementById('preview_due');
    const statusBadge = document.getElementById('preview_status_badge');

    if (daysLate > 0) {
        // OVERDUE
        dueCell.classList.add('text-red-600');
        dueCell.classList.remove('text-emerald-600');

        statusBadge.innerHTML = `<span class="overdue-badge inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600 border border-red-200">
            <i class="fas fa-exclamation-triangle"></i> ${daysLate} Day${daysLate !== 1 ? 's' : ''} Overdue
        </span>`;

        const fine = daysLate * fineRate;
        document.getElementById('preview_days').textContent  = daysLate;
        document.getElementById('preview_fine').textContent  = '₹' + fine.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('preview_formula').textContent = `${daysLate} days × ₹${fineRate.toFixed(2)}/day = ₹${fine.toFixed(2)}`;

        fineBreakdown.classList.remove('hidden');
        noFineBlock.classList.add('hidden');
    } else {
        // ON TIME
        dueCell.classList.add('text-emerald-600');
        dueCell.classList.remove('text-red-600');

        statusBadge.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-600 border border-emerald-200">
            <i class="fas fa-check-circle"></i> On Time
        </span>`;

        fineBreakdown.classList.add('hidden');
        noFineBlock.classList.remove('hidden');
    }

    card.classList.remove('hidden');
    card.querySelector('div').classList.add('fine-card-enter');
    setTimeout(() => card.querySelector('div').classList.remove('fine-card-enter'), 400);
}
</script>

</body>
</html>
