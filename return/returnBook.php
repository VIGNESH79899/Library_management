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
");

$message = "";

/* Return Book Logic */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $issue_id = $_POST['issue_id'];
    $book_id  = $_POST['book_id'];
    $due_date = $_POST['due_date'];

    $return_date = date('Y-m-d');

    // Fine calculation (₹10 per late day)
    $fine = 0;
    if ($return_date > $due_date) {
        $days = (strtotime($return_date) - strtotime($due_date)) / (60 * 60 * 24);
        $fine = $days * 10;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert return record
        $stmt = $conn->prepare("
            INSERT INTO Return_Book (Issue_ID, Return_Date, Fine_Amount)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("isd", $issue_id, $return_date, $fine);
        $stmt->execute();

        // Update book status
        $stmt2 = $conn->prepare("
            UPDATE Book SET Status='Available' WHERE Book_ID=?
        ");
        $stmt2->bind_param("i", $book_id);
        $stmt2->execute();

        // Commit transaction
        $conn->commit();
        $message = "Book returned successfully. Fine: ₹" . $fine;
        
        // Refresh issues list
        header("Refresh: 2");

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $message = "Error returning book";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>LMS | Return Book</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex text-sm">

<?php include "../includers/sidebar.php"; ?>

<div class="flex-1 flex flex-col min-h-screen ml-64">
    <?php include "../includers/headers.php"; ?>

    <main class="p-8">
        <div class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-4">Return Book</h1>

            <?php if ($message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p><?= $message ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">

                <div>
                    <label class="block text-gray-700 font-medium mb-1">Select Issued Book to Return</label>
                    <select name="issue_data" required
                            onchange="setDetails(this)"
                            class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">

                        <option value="">-- Select Book --</option>

                        <?php while ($row = $issues->fetch_assoc()) { ?>
                            <option
                                value="<?= $row['Issue_ID'] ?>"
                                data-issue="<?= $row['Issue_ID'] ?>"
                                data-book="<?= $row['Book_ID'] ?>"
                                data-due="<?= $row['Due_Date'] ?>">
                                <?= $row['Title'] ?> - Issued to <?= $row['Member_Name'] ?> (Due: <?= $row['Due_Date'] ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Hidden Fields -->
                <input type="hidden" name="issue_id" id="issue_id">
                <input type="hidden" name="book_id" id="book_id">
                <input type="hidden" name="due_date" id="due_date">

                <div id="info-box" class="hidden bg-gray-50 p-4 rounded text-gray-600 text-sm">
                    <p><strong>Due Date:</strong> <span id="display-due"></span></p>
                    <p class="mt-1">Creating return record for today: <strong><?= date('Y-m-d') ?></strong></p>
                </div>

                <button class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition font-medium shadow">
                    Confirm Return
                </button>
            </form>
        </div>
    </main>
</div>

<script>
function setDetails(select) {
    const opt = select.options[select.selectedIndex];
    if (opt.value === "") {
        document.getElementById('info-box').classList.add('hidden');
        return;
    }
    
    document.getElementById('issue_id').value = opt.dataset.issue;
    document.getElementById('book_id').value = opt.dataset.book;
    document.getElementById('due_date').value = opt.dataset.due;
    
    document.getElementById('display-due').textContent = opt.dataset.due;
    document.getElementById('info-box').classList.remove('hidden');
}
</script>

</body>
</html>