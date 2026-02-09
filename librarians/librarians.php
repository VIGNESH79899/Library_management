<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

/* Add Librarian */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("
        INSERT INTO Librarian (Librarian_Name, Phone_Number, Email)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("sss", $name, $phone, $email);
    $stmt->execute();
}

/* Fetch Librarians with issue count */
$librarians = $conn->query("
    SELECT L.Librarian_ID, L.Librarian_Name, L.Phone_Number, L.Email,
           COUNT(I.Issue_ID) AS Issued_Count
    FROM Librarian L
    LEFT JOIN Issue I ON L.Librarian_ID = I.Librarian_ID
    GROUP BY L.Librarian_ID
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>LMS | Librarians</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex text-sm">

<?php include "../includers/sidebar.php"; ?>

<div class="flex-1 flex flex-col min-h-screen ml-64">
    <?php include "../includers/headers.php"; ?>

    <main class="p-8">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Librarians Management</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Add Librarian Form -->
            <div class="bg-white p-6 rounded-lg shadow-sm h-fit">
                <h2 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">Add New Librarian</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Full Name</label>
                        <input type="text" name="name" required 
                               class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none"
                               placeholder="Librarian Name">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Phone Number</label>
                        <input type="text" name="phone" required
                               class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none"
                               placeholder="(555) 123-4567">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Email</label>
                        <input type="email" name="email" required
                               class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none"
                               placeholder="librarian@example.com">
                    </div>

                    <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition font-medium shadow">
                        Add Librarian
                    </button>
                </form>
            </div>

            <!-- Librarians List -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-700">Staff List</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 font-semibold border-b">
                            <tr>
                                <th class="p-4">ID</th>
                                <th class="p-4">Name</th>
                                <th class="p-4">Phone</th>
                                <th class="p-4">Email</th>
                                <th class="p-4 text-center">Issues Handled</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while ($row = $librarians->fetch_assoc()) { ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 text-gray-600"><?= $row['Librarian_ID'] ?></td>
                                <td class="p-4 font-medium text-gray-900"><?= $row['Librarian_Name'] ?></td>
                                <td class="p-4 text-gray-600"><?= $row['Phone_Number'] ?></td>
                                <td class="p-4 text-gray-600"><?= $row['Email'] ?></td>
                                <td class="p-4 text-center">
                                    <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2 py-1 rounded-full">
                                        <?= $row['Issued_Count'] ?>
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