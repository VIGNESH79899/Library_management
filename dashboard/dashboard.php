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
<html>
<head>
    <title>LMS | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex">

<!-- Sidebar -->
<?php include "../includers/sidebar.php"; ?>

<!-- Main Content Wrapper -->
<div class="flex-1 flex flex-col min-h-screen ml-64">

    <!-- Header -->
    <?php include "../includers/headers.php"; ?>

    <!-- Dashboard Content -->
    <main class="p-8">

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-500 mb-1">Total Books</p>
                <h2 class="text-3xl font-bold text-gray-800"><?= $totalBooks ?></h2>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-500 mb-1">Total Members</p>
                <h2 class="text-3xl font-bold text-gray-800"><?= $totalMembers ?></h2>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-500 mb-1">Issued Books</p>
                <h2 class="text-3xl font-bold text-gray-800"><?= $issuedBooks ?></h2>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-500 mb-1">Total Fines (₹)</p>
                <h2 class="text-3xl font-bold text-gray-800"><?= $totalFines ?></h2>
            </div>
        </div>

        <!-- Recent Issues -->
        <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
            <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Recent Book Issues</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="p-3 font-semibold">Issue ID</th>
                            <th class="p-3 font-semibold">Book</th>
                            <th class="p-3 font-semibold">Member</th>
                            <th class="p-3 font-semibold">Issue Date</th>
                            <th class="p-3 font-semibold">Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $issues->fetch_assoc()) { ?>
                        <tr class="border-b hover:bg-gray-50 transition-colors">
                            <td class="p-3 font-medium text-gray-900"><?= $row['Issue_ID'] ?></td>
                            <td class="p-3"><?= $row['Title'] ?></td>
                            <td class="p-3"><?= $row['Member_Name'] ?></td>
                            <td class="p-3"><?= $row['Issue_Date'] ?></td>
                            <td class="p-3"><?= $row['Due_Date'] ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Returns -->
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Recent Returns</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="p-3 font-semibold">Return ID</th>
                            <th class="p-3 font-semibold">Book</th>
                            <th class="p-3 font-semibold">Return Date</th>
                            <th class="p-3 font-semibold">Fine (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $returns->fetch_assoc()) { ?>
                        <tr class="border-b hover:bg-gray-50 transition-colors">
                            <td class="p-3 font-medium text-gray-900"><?= $row['Return_ID'] ?></td>
                            <td class="p-3"><?= $row['Title'] ?></td>
                            <td class="p-3"><?= $row['Return_Date'] ?></td>
                            <td class="p-3"><?= $row['Fine_Amount'] ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

</body>
</html>