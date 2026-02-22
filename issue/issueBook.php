<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

$members = $conn->query("SELECT * FROM Member");
$books = $conn->query("SELECT * FROM Book WHERE Status='Available'");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id      = (int) $_POST['book_id'];
    $member_id    = (int) $_POST['member_id'];
    $librarian_id = 1; // Default admin librarian

    $issue_date     = date('Y-m-d');
    $due_date_input = trim($_POST['due_date'] ?? '');
    $today          = date('Y-m-d');

    // Validate due date
    if (empty($due_date_input) || $due_date_input <= $today) {
        $error = 'Please select a valid due date (must be after today).';
    } elseif ($due_date_input > date('Y-m-d', strtotime('+365 days'))) {
        $error = 'Due date cannot exceed 1 year from today.';
    } else {
        $due_date = $due_date_input;
    }

    if (!isset($error)) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO Issue (Book_ID, Member_ID, Librarian_ID, Issue_Date, Due_Date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiss", $book_id, $member_id, $librarian_id, $issue_date, $due_date);
            $stmt->execute();

            $conn->query("UPDATE Book SET Status='Issued' WHERE Book_ID=$book_id");
            $conn->commit();
            $message = "Book issued successfully! Due date set to " . date('M d, Y', strtotime($due_date)) . ".";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to issue book. " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Issue Book</title>
    <?php include "../includers/headers.php"; ?>
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
                    
                    <div class="px-8 py-6 bg-gradient-brand text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                        <div class="relative z-10">
                            <h1 class="text-2xl font-bold tracking-tight text-indigo-700">Issue New Book</h1>
                            <p class="text-indigo-500 text-sm mt-1">Select a member and a book to process a new loan.</p>
                        </div>
                    </div>

                    <div class="p-8">
                        <?php if (isset($message)) { ?>
                            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-start gap-3 animate-fade-in" role="alert">
                                <i class="fas fa-check-circle mt-0.5 text-green-500"></i>
                                <div>
                                    <p class="font-bold text-sm">Success</p>
                                    <p class="text-sm"><?= $message ?></p>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (isset($error)) { ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-start gap-3 animate-fade-in" role="alert">
                                <i class="fas fa-exclamation-circle mt-0.5 text-red-500"></i>
                                <div>
                                    <p class="font-bold text-sm">Error</p>
                                    <p class="text-sm"><?= $error ?></p>
                                </div>
                            </div>
                        <?php } ?>

                        <form method="POST" class="space-y-6">
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Select Member</label>
                                <div class="relative">
                                    <select name="member_id" required 
                                            class="w-full pl-4 pr-10 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all appearance-none cursor-pointer text-slate-700">
                                        <option value="">-- Choose Member --</option>
                                        <?php 
                                        // Reset pointer if reused
                                        $members->data_seek(0);
                                        while ($m = $members->fetch_assoc()) { ?>
                                            <option value="<?= $m['Member_ID'] ?>">
                                                <?= $m['Member_Name'] ?> (ID: <?= $m['Member_ID'] ?>)
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Select Book</label>
                                <div class="relative">
                                    <select name="book_id" required 
                                            class="w-full pl-4 pr-10 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all appearance-none cursor-pointer text-slate-700">
                                        <option value="">-- Choose Book --</option>
                                        <?php 
                                        // Reset pointer
                                        $books->data_seek(0);
                                        while ($b = $books->fetch_assoc()) { ?>
                                            <option value="<?= $b['Book_ID'] ?>">
                                                <?= $b['Title'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Only books with status 'Available' are shown.</p>
                            </div>

                            <!-- Due Date Picker -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">
                                    <i class="fas fa-calendar-alt text-indigo-400 mr-1"></i> Due / Return Date
                                </label>
                                <input type="date" name="due_date" id="due_date" required
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                       max="<?= date('Y-m-d', strtotime('+365 days')) ?>"
                                       value="<?= date('Y-m-d', strtotime('+7 days')) ?>"
                                       class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-slate-700 font-medium">
                                <p class="text-xs text-slate-400">Default is 7 days from today. You can extend up to 1 year.</p>
                            </div>

                            <div class="bg-indigo-50/50 p-4 rounded-lg border border-indigo-100 flex gap-3 text-sm text-indigo-800">
                                <i class="fas fa-info-circle mt-0.5 text-indigo-500"></i>
                                <div>
                                    <p class="font-bold text-xs uppercase tracking-wide text-indigo-500 mb-1">Loan Policy</p>
                                    <p>Set the due date above. Loans issued from today (<strong><?= date('M d, Y') ?></strong>). Please ensure the member has no outstanding fines.</p>
                                </div>
                            </div>

                            <div class="pt-4 flex items-center gap-4">
                                <a href="../dashboard/dashboard.php" class="px-6 py-3 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 font-medium transition-colors">Cancel</a>
                                <button class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-bold shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                                    <i class="fas fa-paper-plane"></i>
                                    <span>Confirm Issue</span>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>
