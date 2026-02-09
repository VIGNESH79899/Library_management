<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

/* Add Publisher */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO Publisher (Publisher_Name, Email, Phone) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $phone);
    $stmt->execute();
}

/* Fetch Publishers */
$publishers = $conn->query("
    SELECT P.*, COUNT(B.Book_ID) AS Total_Books
    FROM Publisher P
    LEFT JOIN Book B ON P.Publisher_ID = B.Publisher_ID
    GROUP BY P.Publisher_ID
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>LMS | Publishers</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex text-sm">

<?php include "../includers/sidebar.php"; ?>

<div class="flex-1 flex flex-col min-h-screen ml-64">
    <?php include "../includers/headers.php"; ?>

    <main class="p-8">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Publishers Management</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Add Publisher Form -->
            <div class="bg-white p-6 rounded-lg shadow-sm h-fit">
                <h2 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">Add New Publisher</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Publisher Name</label>
                        <input type="text" name="name" required 
                               class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Email</label>
                        <input type="email" name="email" 
                               class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Phone</label>
                        <input type="text" name="phone" 
                               class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>

                    <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition font-medium shadow">
                        Add Publisher
                    </button>
                </form>
            </div>

            <!-- Publishers List -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-700">Existing Publishers</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 font-semibold border-b">
                            <tr>
                                <th class="p-4">ID</th>
                                <th class="p-4">Name</th>
                                <th class="p-4">Email</th>
                                <th class="p-4 text-center">Books</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while ($row = $publishers->fetch_assoc()) { ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 text-gray-600"><?= $row['Publisher_ID'] ?></td>
                                <td class="p-4 font-medium text-gray-900"><?= $row['Publisher_Name'] ?></td>
                                <td class="p-4 text-gray-600"><?= $row['Email'] ?></td>
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