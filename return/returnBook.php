<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

/* Fetch issued books with issue details */
$issues = $conn->query("
    SELECT I.Issue_ID, B.Book_ID, B.Title, I.Due_Date, M.Member_Name
    FROM Issue I
    JOIN Book B ON I.Book_ID = B.Book_ID
    JOIN Member M ON I.Member_ID = M.Member_ID
    WHERE B.Status = 'Issued'
    ORDER BY I.Issue_Date DESC
");

$message = "";
$error = "";

/* Return Book Logic */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $issue_id = $_POST['issue_id'] ?? null;
    $return_date = date('Y-m-d');

    if ($issue_id) {
        // Fetch details securely from DB
        $stmt_check = $conn->prepare("SELECT Book_ID, Due_Date FROM Issue WHERE Issue_ID = ?");
        $stmt_check->bind_param("i", $issue_id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $row_check = $res_check->fetch_assoc();
            $book_id = $row_check['Book_ID'];
            $due_date = $row_check['Due_Date'];

            // Fine calculation (₹10 per late day)
            $fine = 0;
            if (strtotime($return_date) > strtotime($due_date)) {
                $diff = (strtotime($return_date) - strtotime($due_date));
                $days = floor($diff / (60 * 60 * 24));
                $fine = $days * 10;
            }

            // Start transaction
            $conn->begin_transaction();

            try {
                // Insert return record
                $stmt = $conn->prepare("INSERT INTO Return_Book (Issue_ID, Return_Date, Fine_Amount) VALUES (?, ?, ?)");
                $stmt->bind_param("isd", $issue_id, $return_date, $fine);
                $stmt->execute();

                // Update book status
                $stmt2 = $conn->prepare("UPDATE Book SET Status='Available' WHERE Book_ID=?");
                $stmt2->bind_param("i", $book_id);
                $stmt2->execute();

                // Commit transaction
                $conn->commit();
                $message = "Book returned successfully.";
                if ($fine > 0) {
                     $message .= " <strong>Late Fine: ₹" . $fine . "</strong>";
                }
                
                // Refresh to clear form/list
                // header("Refresh: 2"); // Optional, but let's just show the message

            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $error = "Transaction failed: " . $e->getMessage();
            }
        } else {
            $error = "Invalid Issue ID.";
        }
    } else {
        $error = "Please select a book to return.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Return Book</title>
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
                    
                    <div class="px-8 py-6 bg-gradient-to-r from-emerald-500 to-teal-600 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                        <div class="relative z-10">
                            <h1 class="text-2xl font-bold tracking-tight text-white">Return Book</h1>
                            <p class="text-emerald-100 text-sm mt-1">Process book returns and calculate fines automatically.</p>
                        </div>
                    </div>

                    <div class="p-8">

                        <?php if ($message): ?>
                            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-start gap-3 animate-fade-in" role="alert">
                                <i class="fas fa-check-circle mt-0.5 text-green-500"></i>
                                <div>
                                    <p class="font-bold text-sm">Success</p>
                                    <p class="text-sm"><?= $message ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-start gap-3 animate-fade-in" role="alert">
                                <i class="fas fa-exclamation-circle mt-0.5 text-red-500"></i>
                                <div>
                                    <p class="font-bold text-sm">Error</p>
                                    <p class="text-sm"><?= $error ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-6">

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Select Issued Book</label>
                                <div class="relative">
                                    <select name="issue_id" required 
                                            class="w-full pl-4 pr-10 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500 transition-all appearance-none cursor-pointer text-slate-700">
                                        <option value="">-- Choose Book to Return --</option>
                                        <?php 
                                        if ($issues->num_rows > 0) {
                                            $issues->data_seek(0);
                                            while ($row = $issues->fetch_assoc()) { 
                                                // Calculate fine preview
                                                $finePreview = 0;
                                                $isLate = false;
                                                $today = date('Y-m-d');
                                                if ($today > $row['Due_Date']) {
                                                    $diff = (strtotime($today) - strtotime($row['Due_Date']));
                                                    $finePreview = floor($diff / (60 * 60 * 24)) * 10;
                                                    $isLate = true;
                                                }
                                                ?>
                                                <option value="<?= $row['Issue_ID'] ?>">
                                                    <?= $row['Title'] ?> | Lent to: <?= $row['Member_Name'] ?> 
                                                    (Due: <?= date('M d', strtotime($row['Due_Date'])) ?><?= $isLate ? " - Late Fine: ₹$finePreview" : "" ?>)
                                                </option>
                                            <?php } 
                                        } else { ?>
                                            <option value="" disabled>No outstanding issued books</option>
                                        <?php } ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-emerald-50/50 p-4 rounded-lg border border-emerald-100 text-sm text-emerald-800">
                                <div class="flex gap-3">
                                    <i class="fas fa-info-circle mt-0.5 text-emerald-500"></i>
                                    <div>
                                        <p class="font-bold text-xs uppercase tracking-wide text-emerald-600 mb-1">Return Policy</p>
                                        <p>Fines are calculated automatically at <span class="font-bold">₹10 per day</span> for overdue returns.</p>
                                    </div>
                                </div>
                            </div>

                            <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-lg font-bold shadow-lg shadow-emerald-500/30 transition-all transform active:scale-95 flex items-center justify-center gap-2">
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

</body>
</html>
