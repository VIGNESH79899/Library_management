<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

/* Add Category */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $shelf = $_POST['shelf'];

    $stmt = $conn->prepare("
        INSERT INTO Category (Category_Name, Description, Shelf_Number)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("sss", $name, $desc, $shelf);
    $stmt->execute();
}

/* Fetch Categories */
$categories = $conn->query("
    SELECT C.*, COUNT(B.Book_ID) AS Total_Books
    FROM Category C
    LEFT JOIN Book B ON C.Category_ID = B.Category_ID
    GROUP BY C.Category_ID
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>LMS | Categories</title>
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
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Categories Management</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Add Category Form -->
            <div class="bg-white p-6 rounded-lg shadow-sm h-fit">
                <h2 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">Add New Category</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Category Name</label>
                        <input type="text" name="name" required 
                               class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none"
                               placeholder="e.g. Science Fiction">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Description</label>
                        <textarea name="description" rows="3" 
                                  class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none"
                                  placeholder="Optional description..."></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Shelf Number</label>
                        <input type="text" name="shelf" 
                               class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none"
                               placeholder="e.g. A-12">
                    </div>

                    <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition font-medium shadow">
                        Add Category
                    </button>
                </form>
            </div>

            <!-- Categories List -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-700">Existing Categories</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 font-semibold border-b">
                            <tr>
                                <th class="p-4">ID</th>
                                <th class="p-4">Name</th>
                                <th class="p-4">Shelf</th>
                                <th class="p-4 text-center">Books</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while ($row = $categories->fetch_assoc()) { ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 text-gray-600"><?= $row['Category_ID'] ?></td>
                                <td class="p-4 font-medium text-gray-900"><?= $row['Category_Name'] ?></td>
                                <td class="p-4 text-gray-600"><?= $row['Shelf_Number'] ?></td>
                                <td class="p-4 text-center">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full">
                                        <?= $row['Total_Books'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <a href="#" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>