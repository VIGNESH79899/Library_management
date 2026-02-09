<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

// Detailed Queries for Reports
$totalBooks = $conn->query("SELECT COUNT(*) AS total FROM Book")->fetch_assoc()['total'];
$totalMembers = $conn->query("SELECT COUNT(*) AS total FROM Member")->fetch_assoc()['total'];
$totalIssued = $conn->query("SELECT COUNT(*) AS total FROM Book WHERE Status='Issued'")->fetch_assoc()['total'];
$totalReturned = $conn->query("SELECT COUNT(*) AS total FROM Return_Book")->fetch_assoc()['total'];
$totalFines = $conn->query("SELECT IFNULL(SUM(Fine_Amount),0) AS total FROM Return_Book")->fetch_assoc()['total'];

// Most Popular Books (Most Issued)
$popularBooks = $conn->query("
    SELECT B.Title, COUNT(I.Issue_ID) as Issue_Count
    FROM Issue I
    JOIN Book B ON I.Book_ID = B.Book_ID
    GROUP BY I.Book_ID
    ORDER BY Issue_Count DESC
    LIMIT 5
");

// Member Activity (Most Books Issued)
$activeMembers = $conn->query("
    SELECT M.Member_Name, COUNT(I.Issue_ID) as Issue_Count
    FROM Issue I
    JOIN Member M ON I.Member_ID = M.Member_ID
    GROUP BY I.Member_ID
    ORDER BY Issue_Count DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>LMS | Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex text-sm">

<?php include "../includers/sidebar.php"; ?>

<div class="flex-1 flex flex-col min-h-screen ml-64">
    <?php include "../includers/headers.php"; ?>

    <main class="p-8">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">System Reports</h1>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                <p class="text-gray-500 font-medium">Total Books Inventory</p>
                <h2 class="text-3xl font-bold text-gray-800"><?= $totalBooks ?></h2>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
                <p class="text-gray-500 font-medium">Registered Members</p>
                <h2 class="text-3xl font-bold text-gray-800"><?= $totalMembers ?></h2>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                <p class="text-gray-500 font-medium">Books Currently Issued</p>
                <h2 class="text-3xl font-bold text-gray-800"><?= $totalIssued ?></h2>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-indigo-500">
                <p class="text-gray-500 font-medium">Total Returns Processed</p>
                <h2 class="text-3xl font-bold text-gray-800"><?= $totalReturned ?></h2>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-red-500">
                <p class="text-gray-500 font-medium">Total Fines Collected</p>
                <h2 class="text-3xl font-bold text-gray-800">₹<?= number_format($totalFines, 2) ?></h2>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Popular Books -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">Top 5 Most Popular Books</h2>
                <ul class="divide-y divide-gray-100">
                    <?php while ($b = $popularBooks->fetch_assoc()) { ?>
                    <li class="py-3 flex justify-between items-center">
                        <span class="font-medium text-gray-800"><?= $b['Title'] ?></span>
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full">
                            <?= $b['Issue_Count'] ?> Issues
                        </span>
                    </li>
                    <?php } ?>
                    <?php if ($popularBooks->num_rows == 0) echo "<p class='text-gray-500 text-sm'>No data available yet.</p>"; ?>
                </ul>
            </div>

            <!-- Active Members -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">Top 5 Most Active Members</h2>
                <ul class="divide-y divide-gray-100">
                    <?php while ($m = $activeMembers->fetch_assoc()) { ?>
                    <li class="py-3 flex justify-between items-center">
                        <span class="font-medium text-gray-800"><?= $m['Member_Name'] ?></span>
                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">
                            <?= $m['Issue_Count'] ?> Issues
                        </span>
                    </li>
                    <?php } ?>
                    <?php if ($activeMembers->num_rows == 0) echo "<p class='text-gray-500 text-sm'>No data available yet.</p>"; ?>
                </ul>
            </div>

        </div>

        <div class="mt-8 text-center">
             <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-2 rounded shadow hover:bg-gray-900 transition">
                Print Report
             </button>
        </div>

    </main>
</div>

</body>
</html>