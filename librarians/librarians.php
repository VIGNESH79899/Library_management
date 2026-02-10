<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

$message = "";
$editData = null;

/* Handle Form Submission */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    if (isset($_POST['update_id']) && !empty($_POST['update_id'])) {
        // Update
        $id = $_POST['update_id'];
        $stmt = $conn->prepare("UPDATE Librarian SET Librarian_Name=?, Phone_Number=?, Email=? WHERE Librarian_ID=?");
        $stmt->bind_param("sssi", $name, $phone, $email, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Librarian updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update librarian.";
        }
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO Librarian (Librarian_Name, Phone_Number, Email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $phone, $email);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Librarian added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add librarian.";
        }
    }
    header("Location: librarians.php");
    exit;
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

/* Handle Edit Request */
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM Librarian WHERE Librarian_ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

/* Fetch Librarians with issue count */
$librarians = $conn->query("
    SELECT L.Librarian_ID, L.Librarian_Name, L.Phone_Number, L.Email,
           COUNT(I.Issue_ID) AS Issued_Count
    FROM Librarian L
    LEFT JOIN Issue I ON L.Librarian_ID = I.Librarian_ID
    GROUP BY L.Librarian_ID
    ORDER BY L.Librarian_ID DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Librarians Management</title>
    <?php include "../includers/headers.php"; ?>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include "../includers/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="flex-1 ml-64 flex flex-col relative z-0">
        
        <main class="p-8 space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Librarians Management</h1>
                    <p class="text-slate-500 mt-1">Manage staff and librarians who issue books.</p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-start gap-3 animate-fade-in" role="alert">
                    <i class="fas fa-check-circle mt-0.5 text-green-500"></i>
                    <div>
                        <p class="font-bold text-sm">Success</p>
                        <p class="text-sm"><?= $message ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Form Card -->
                <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-gray-100 overflow-hidden animate-fade-in-up md:sticky md:top-24">
                    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="font-bold text-slate-700">
                            <?= $editData ? 'Edit Librarian' : 'Add New Librarian' ?>
                        </h2>
                        <?php if($editData): ?>
                            <a href="librarians.php" class="text-xs text-red-500 hover:text-red-700 font-medium">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" class="p-6 space-y-5">
                        <input type="hidden" name="update_id" value="<?= $editData['Librarian_ID'] ?? '' ?>">
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Full Name</label>
                            <div class="relative">
                                <i class="fas fa-user-tie absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" name="name" required 
                                       value="<?= $editData['Librarian_Name'] ?? '' ?>"
                                       class="w-full pl-8 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all text-sm"
                                       placeholder="Librarian Name">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Phone Number</label>
                            <div class="relative">
                                <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" name="phone" required
                                       value="<?= $editData['Phone_Number'] ?? '' ?>"
                                       class="w-full pl-8 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all text-sm"
                                       placeholder="(555) 123-4567">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="email" name="email" required
                                       value="<?= $editData['Email'] ?? '' ?>"
                                       class="w-full pl-8 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all text-sm"
                                       placeholder="librarian@example.com">
                            </div>
                        </div>

                        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg font-bold shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center justify-center gap-2 text-sm">
                            <i class="fas <?= $editData ? 'fa-save' : 'fa-plus' ?>"></i>
                            <span><?= $editData ? 'Update Librarian' : 'Add Librarian' ?></span>
                        </button>
                    </form>
                </div>

                <!-- List Card -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-gray-100 overflow-hidden animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100">
                        <h2 class="font-bold text-slate-700">Staff List</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Details</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider text-center">Managed</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php while ($row = $librarians->fetch_assoc()) { ?>
                                <tr class="hover:bg-purple-50/30 transition-colors group">
                                    <td class="p-4 font-mono text-xs text-slate-400">#<?= $row['Librarian_ID'] ?></td>
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800"><?= $row['Librarian_Name'] ?></div>
                                        <div class="text-xs text-slate-400">Staff Member</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs"><i class="fas fa-envelope w-4 text-slate-400"></i> <?= $row['Email'] ?></span>
                                            <span class="text-xs"><i class="fas fa-phone w-4 text-slate-400"></i> <?= $row['Phone_Number'] ?></span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                                            <?= $row['Issued_Count'] ?> issues
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="librarians.php?edit=<?= $row['Librarian_ID'] ?>" class="text-indigo-600 hover:text-indigo-800 font-medium text-xs bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                                            Edit
                                        </a>
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
</div>

</body>
</html>
