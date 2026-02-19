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
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    $stmt = $conn->prepare("INSERT INTO Publisher (Publisher_Name, Email, Phone) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $phone);
    $stmt->execute();
    
    // Redirect to prevent form resubmission
    header("Location: publishers.php");
    exit;
}

/* Fetch Publishers */
$publishers = $conn->query("
    SELECT P.*, COUNT(B.Book_ID) AS Total_Books
    FROM Publisher P
    LEFT JOIN Book B ON P.Publisher_ID = B.Publisher_ID
    GROUP BY P.Publisher_ID
    ORDER BY P.Publisher_ID DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Publishers Management</title>
    <?php include "../includers/headers.php"; ?>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include "../includers/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="flex-1 ml-64 flex flex-col relative z-0 transition-all duration-300">
        <!-- Top Navigation -->
        <?php include "../includers/navbar.php"; ?>

        <main class="p-8 space-y-8">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Publishers Management</h1>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in-up">
                
                <!-- Add Publisher Form -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-fit sticky top-8">
                    <h2 class="text-lg font-bold text-slate-700 mb-4 pb-2 border-b border-gray-100">Add New Publisher</h2>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Publisher Name</label>
                            <input type="text" name="name" required 
                                   class="w-full px-4 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none placeholder-gray-400"
                                   placeholder="Enter publisher name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Email <span class="text-gray-400 font-normal">(Optional)</span></label>
                            <input type="email" name="email" 
                                   class="w-full px-4 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none placeholder-gray-400"
                                   placeholder="publisher@example.com">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Phone <span class="text-gray-400 font-normal">(Optional)</span></label>
                            <input type="text" name="phone" 
                                   class="w-full px-4 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none placeholder-gray-400"
                                   placeholder="+1 234 567 890">
                        </div>

                        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg font-medium transition-all shadow-lg shadow-indigo-500/30 flex items-center justify-center gap-2 transform active:scale-95">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Add Publisher</span>
                        </button>
                    </form>
                </div>

                <!-- Publishers List -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h2 class="font-bold text-slate-700">Existing Publishers</h2>
                        <div class="text-xs text-slate-400">
                            Total: <span class="font-semibold text-slate-600"><?= $publishers->num_rows ?></span>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider text-center">Books</th>
                                    <th class="p-4 font-semibold text-xs text-gray-500 uppercase tracking-wider text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php while ($row = $publishers->fetch_assoc()) { 
                                    $email = isset($row['Email']) ? htmlspecialchars($row['Email']) : 'N/A';
                                    $phone = isset($row['Phone']) && !empty($row['Phone']) ? htmlspecialchars($row['Phone']) : '';
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="p-4 font-mono text-xs text-slate-400">#<?= str_pad($row['Publisher_ID'], 3, '0', STR_PAD_LEFT) ?></td>
                                    <td class="p-4 font-medium text-slate-700">
                                        <?= htmlspecialchars($row['Publisher_Name']) ?>
                                    </td>
                                    <td class="p-4 text-xs">
                                        <div class="flex flex-col gap-1">
                                            <?php if($email !== 'N/A'): ?>
                                                <div class="flex items-center gap-1.5 text-slate-500">
                                                    <i class="fas fa-envelope text-[10px] text-slate-400"></i>
                                                    <span><?= $email ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($phone): ?>
                                                <div class="flex items-center gap-1.5 text-slate-500">
                                                    <i class="fas fa-phone text-[10px] text-slate-400"></i>
                                                    <span><?= $phone ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($email === 'N/A' && !$phone) echo '<span class="text-slate-400 italic">No contact info</span>'; ?>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-600 border border-indigo-100">
                                            <?= $row['Total_Books'] ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="#" class="h-8 w-8 rounded-full bg-slate-50 text-slate-500 flex items-center justify-center hover:bg-indigo-50 hover:text-indigo-600 transition-all shadow-sm" title="Edit">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                            <!-- Optional Delete -->
                                            <!-- <a href="#" class="h-8 w-8 rounded-full bg-slate-50 text-slate-500 flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition-all shadow-sm" title="Delete">
                                                <i class="fas fa-trash text-xs"></i>
                                            </a> -->
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php if($publishers->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-slate-400 italic">No publishers found. Add the first one!</td>
                                    </tr>
                                <?php endif; ?>
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