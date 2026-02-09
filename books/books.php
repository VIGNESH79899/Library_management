<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

// Fetch Books
$books = $conn->query("
    SELECT B.Book_ID, B.Title, B.Author, B.ISBN, B.Status, C.Category_Name, P.Publisher_Name
    FROM Book B
    LEFT JOIN Category C ON B.Category_ID = C.Category_ID
    LEFT JOIN Publisher P ON B.Publisher_ID = P.Publisher_ID
    ORDER BY B.Book_ID DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>LMS | Books</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex text-sm">

<!-- Sidebar -->
<?php include "../includers/sidebar.php"; ?>

<!-- Main Content Wrapper -->
<div class="flex-1 flex flex-col min-h-screen ml-64">

    <!-- Header -->
    <?php include "../includers/headers.php"; ?>

    <!-- Main Content -->
    <main class="p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Books Management</h1>
            <a href="addBook.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
                + Add New Book
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-semibold">
                        <th class="p-4">ID</th>
                        <th class="p-4">Title</th>
                        <th class="p-4">Author</th>
                        <th class="p-4">ISBN</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Publisher</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while ($row = $books->fetch_assoc()) { ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 text-gray-600"><?= $row['Book_ID'] ?></td>
                        <td class="p-4 font-medium text-gray-900"><?= $row['Title'] ?></td>
                        <td class="p-4 text-gray-600"><?= $row['Author'] ?></td>
                        <td class="p-4 text-gray-600"><?= $row['ISBN'] ?></td>
                        <td class="p-4 text-gray-600"><?= $row['Category_Name'] ?></td>
                        <td class="p-4 text-gray-600"><?= $row['Publisher_Name'] ?></td>
                        <td class="p-4">
                            <?php if ($row['Status'] == 'Available') { ?>
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-bold">Available</span>
                            <?php } else { ?>
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-bold">Issued</span>
                            <?php } ?>
                        </td>
                        <td class="p-4 text-center">
                            <a href="editBook.php?id=<?= $row['Book_ID'] ?>" class="text-blue-600 hover:text-blue-800 mr-2 font-medium">Edit</a>
                            <a href="deleteBook.php?id=<?= $row['Book_ID'] ?>" class="text-red-600 hover:text-red-800 font-medium" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>