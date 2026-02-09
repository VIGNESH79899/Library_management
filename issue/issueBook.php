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
    $book_id = $_POST['book_id'];
    $member_id = $_POST['member_id'];
    $librarian_id = 1; // Assuming default admin librarian for now, or fetch from session

    $issue_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+7 days'));

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO Issue (Book_ID, Member_ID, Librarian_ID, Issue_Date, Due_Date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $book_id, $member_id, $librarian_id, $issue_date, $due_date);
        $stmt->execute();

        $conn->query("UPDATE Book SET Status='Issued' WHERE Book_ID=$book_id");
        $conn->commit();
        $message = "Book issued successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Failed to issue book.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>LMS | Issue Book</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex text-sm">

<?php include "../includers/sidebar.php"; ?>

<div class="flex-1 flex flex-col min-h-screen ml-64">
    <?php include "../includers/headers.php"; ?>

    <main class="p-8">
        <div class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-4">Issue Book</h1>

            <?php if (isset($message)) { ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p><?= $message ?></p>
                </div>
            <?php } ?>

            <?php if (isset($error)) { ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p><?= $error ?></p>
                </div>
            <?php } ?>

            <form method="POST" class="space-y-6">
                
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Select Member</label>
                    <select name="member_id" required 
                            class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">-- Select Member --</option>
                        <?php while ($m = $members->fetch_assoc()) { ?>
                            <option value="<?= $m['Member_ID'] ?>">
                                <?= $m['Member_Name'] ?> (ID: <?= $m['Member_ID'] ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-1">Select Book</label>
                    <select name="book_id" required 
                            class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">-- Select Available Book --</option>
                        <?php while ($b = $books->fetch_assoc()) { ?>
                            <option value="<?= $b['Book_ID'] ?>">
                                <?= $b['Title'] ?> (by <?= $b['Author'] ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="bg-blue-50 p-4 rounded text-sm text-blue-800">
                    <p><strong>Note:</strong> Book will be issued for 7 days from today.</p>
                </div>

                <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition font-medium shadow">
                    Issue Book
                </button>

            </form>
        </div>
    </main>
</div>

</body>
</html>