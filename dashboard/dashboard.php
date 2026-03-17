<?php
require_once "../config/app.php";
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

include "../config/db.php";

$adminRole = $_SESSION['admin_role'] ?? 'admin';
$isStaff = ($adminRole === 'staff');
$staffLibrarianId = null;

if ($isStaff) {
    // Find the librarian ID mapping to this staff user
    $staffEmail = '';
    $stmt_email = $conn->prepare("SELECT Email FROM admin WHERE username=?");
    $stmt_email->bind_param("s", $_SESSION['admin']);
    $stmt_email->execute();
    $res = $stmt_email->get_result()->fetch_assoc();
    if ($res) {
        $staffEmail = $res['Email'];
        $stmt_lib = $conn->prepare("SELECT Librarian_ID FROM librarian WHERE Email=?");
        $stmt_lib->bind_param("s", $staffEmail);
        $stmt_lib->execute();
        $lib_res = $stmt_lib->get_result()->fetch_assoc();
        if ($lib_res) $staffLibrarianId = $lib_res['Librarian_ID'];
    }
}

// Stats
$totalBooks = $conn->query("SELECT IFNULL(SUM(Quantity),0) AS total FROM book")->fetch_assoc()['total'];
$totalMembers = $conn->query("SELECT COUNT(*) AS total FROM member")->fetch_assoc()['total'];

if ($isStaff && $staffLibrarianId) {
    $issuedBooks = $conn->query("SELECT COUNT(*) AS total FROM issue WHERE Librarian_ID = $staffLibrarianId AND Issue_ID NOT IN (SELECT Issue_ID FROM return_book)")->fetch_assoc()['total'];
    $totalFines = $conn->query("SELECT IFNULL(SUM(R.Fine_Amount),0) AS total FROM return_book R JOIN issue I ON R.Issue_ID = I.Issue_ID WHERE I.Librarian_ID = $staffLibrarianId")->fetch_assoc()['total'];

    // Recent Issues
    $issues = $conn->query("
        SELECT I.Issue_ID, B.Title, M.Member_Name, I.Issue_Date, I.Due_Date
        FROM issue I
        JOIN book B ON I.Book_ID = B.Book_ID
        JOIN member M ON I.Member_ID = M.Member_ID
        WHERE I.Librarian_ID = $staffLibrarianId
        ORDER BY I.Issue_Date DESC
        LIMIT 5
    ");

    // Recent Returns
    $returns = $conn->query("
        SELECT R.Return_ID, B.Title, R.Return_Date, R.Fine_Amount
        FROM return_book R
        JOIN issue I ON R.Issue_ID = I.Issue_ID
        JOIN book B ON I.Book_ID = B.Book_ID
        WHERE I.Librarian_ID = $staffLibrarianId
        ORDER BY R.Return_Date DESC
        LIMIT 5
    ");
} else {
    $issuedBooks = $conn->query("SELECT COUNT(*) AS total FROM issue WHERE Issue_ID NOT IN (SELECT Issue_ID FROM return_book)")->fetch_assoc()['total'];
    $totalFines = $conn->query("SELECT IFNULL(SUM(Fine_Amount),0) AS total FROM return_book")->fetch_assoc()['total'];

    // Recent Issues
    $issues = $conn->query("
        SELECT I.Issue_ID, B.Title, M.Member_Name, I.Issue_Date, I.Due_Date
        FROM issue I
        JOIN book B ON I.Book_ID = B.Book_ID
        JOIN member M ON I.Member_ID = M.Member_ID
        ORDER BY I.Issue_Date DESC
        LIMIT 5
    ");

    // Recent Returns
    $returns = $conn->query("
        SELECT R.Return_ID, B.Title, R.Return_Date, R.Fine_Amount
        FROM return_book R
        JOIN issue I ON R.Issue_ID = I.Issue_ID
        JOIN book B ON I.Book_ID = B.Book_ID
        ORDER BY R.Return_Date DESC
        LIMIT 5
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Dashboard</title>
    <!-- Header includes CSS now, so we just need to include the header file in the body, 
         but technically the header file has the <head> tags. 
         Wait, includers/headers.php has <head> tags.
         So I should NOT put <head> here if I include headers.php first. 
         However, standard practice in this project seems to be including headers.php inside the body?
         Let's check headers.php again. It has <head> ... </head> followed by the header <div>.
         This is a bit weird structure (head inside body or just included).
         Let's stick to the pattern: headers.php contains the <head> section AND the top bar.
    -->
    <?php include "../includers/headers.php"; ?>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include "../includers/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="main-content flex-1 ml-0 md:ml-64 flex flex-col relative z-0 min-h-screen bg-slate-50/50">
        <!-- Ambient Background Orbs -->
        <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none bg-[#f8fafc]">
            <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-[120px] mix-blend-multiply opacity-80 animate-blob"></div>
            <div class="absolute top-[20%] right-[-10%] w-[400px] h-[400px] bg-purple-500/10 rounded-full blur-[100px] mix-blend-multiply opacity-80 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-10%] left-[20%] w-[600px] h-[600px] bg-pink-500/10 rounded-full blur-[120px] mix-blend-multiply opacity-80 animate-blob animation-delay-4000"></div>
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-[0.03] mix-blend-overlay"></div>
        </div>
        <!-- Top Navigation -->
        <?php include "../includers/navbar.php"; ?>
        
        <!-- Dashboard Content -->
        
        <!-- Dashboard Content -->
        <main class="p-4 md:p-8 space-y-8">
            
            <!-- Welcome Section -->
            <div class="relative rounded-[2.5rem] p-8 md:p-12 text-white overflow-hidden shadow-2xl shadow-indigo-600/20 animate-enter border border-white/10" style="background: linear-gradient(135deg, #3730a3 0%, #4f46e5 50%, #7c3aed 100%);">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-[0.15] mix-blend-overlay"></div>
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-pink-500 opacity-20 rounded-full blur-3xl"></div>
                
                <div class="absolute top-1/2 right-10 transform -translate-y-1/2 opacity-[0.08] pointer-events-none">
                    <i class="fas fa-layer-group text-[16rem]"></i>
                </div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-8">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-xs font-bold tracking-widest text-white border border-white/20 mb-6 shadow-inner uppercase">
                            <span class="relative flex h-2.5 w-2.5">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                            </span>
                            System Network Online
                        </div>
                        <h1 class="text-4xl md:text-5xl font-extrabold font-inter tracking-tight mb-4 leading-tight">
                            Welcome Back, <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-200 to-pink-200">System Admin! 👋</span>
                        </h1>
                        <p class="text-indigo-100 font-medium text-base md:text-lg leading-relaxed max-w-xl opacity-90">
                            Your library command center. Monitor activity, track inventory, and manage your community effectively from this dashboard.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="bg-white/10 backdrop-blur-xl border border-white/20 px-5 py-3.5 rounded-2xl flex items-center gap-3 text-sm font-semibold text-white shadow-[0_8px_32px_rgba(0,0,0,0.12)]">
                            <i class="far fa-clock text-indigo-300 text-lg"></i>
                            <span class="tracking-wide"><?= date('F j, Y') ?></span>
                        </div>
                        <button class="bg-white text-indigo-600 hover:bg-indigo-50 px-6 py-3.5 rounded-2xl text-sm font-extrabold flex items-center gap-2 transition-all transform hover:-translate-y-1 shadow-[0_8px_24px_rgba(255,255,255,0.2)] hover:shadow-[0_12px_32px_rgba(255,255,255,0.3)] w-full sm:w-auto justify-center group" onclick="window.location.href='../issue/issueBook.php'">
                            <i class="fas fa-bolt text-indigo-500 group-hover:scale-110 transition-transform"></i> Quick Issue
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                <!-- Total Books -->
                <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl p-6 md:p-8 rounded-[2rem] border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(99,102,241,0.12)] group animate-enter delay-100 hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-indigo-100/50 to-indigo-50/10 rounded-full blur-2xl -mr-10 -mt-10 transition-transform group-hover:scale-150 duration-700"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-white shadow-inner border border-indigo-100/50 flex items-center justify-center text-indigo-600 group-hover:from-indigo-600 group-hover:to-indigo-500 group-hover:text-white transition-all duration-500 shadow-sm relative overflow-hidden">
                                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out"></div>
                                <i class="fas fa-book-open text-xl relative z-10"></i>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm text-xs font-bold font-inter">
                                <i class="fas fa-arrow-up text-[10px]"></i> 2.5%
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Total Books</p>
                            <h3 class="text-4xl font-extrabold text-slate-800 font-inter tracking-tight flex items-baseline gap-1">
                                <span class="count-up" data-target="<?= htmlspecialchars($totalBooks) ?>">0</span>
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Total Members -->
                <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl p-6 md:p-8 rounded-[2rem] border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(236,72,153,0.12)] group animate-enter delay-200 hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-pink-100/50 to-pink-50/10 rounded-full blur-2xl -mr-10 -mt-10 transition-transform group-hover:scale-150 duration-700"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-pink-50 to-white shadow-inner border border-pink-100/50 flex items-center justify-center text-pink-600 group-hover:from-pink-600 group-hover:to-pink-500 group-hover:text-white transition-all duration-500 shadow-sm relative overflow-hidden">
                                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out"></div>
                                <i class="fas fa-users text-xl relative z-10"></i>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm text-xs font-bold font-inter">
                                <i class="fas fa-arrow-up text-[10px]"></i> 12%
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Members</p>
                            <h3 class="text-4xl font-extrabold text-slate-800 font-inter tracking-tight flex items-baseline gap-1">
                                <span class="count-up" data-target="<?= htmlspecialchars($totalMembers) ?>">0</span>
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Issued Books -->
                <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl p-6 md:p-8 rounded-[2rem] border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(245,158,11,0.12)] group animate-enter delay-300 hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-amber-100/50 to-amber-50/10 rounded-full blur-2xl -mr-10 -mt-10 transition-transform group-hover:scale-150 duration-700"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-amber-50 to-white shadow-inner border border-amber-100/50 flex items-center justify-center text-amber-500 group-hover:from-amber-500 group-hover:to-amber-400 group-hover:text-white transition-all duration-500 shadow-sm relative overflow-hidden">
                                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out"></div>
                                <i class="fas fa-hand-holding flex items-center justify-center text-xl relative z-10"></i>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 shadow-sm text-xs font-bold font-inter">
                                Active Issue
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Issued Books</p>
                            <h3 class="text-4xl font-extrabold text-slate-800 font-inter tracking-tight flex items-baseline gap-1">
                                <span class="count-up" data-target="<?= htmlspecialchars($issuedBooks) ?>">0</span>
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Fines -->
                <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl p-6 md:p-8 rounded-[2rem] border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(239,68,68,0.12)] group animate-enter delay-400 hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-red-100/50 to-red-50/10 rounded-full blur-2xl -mr-10 -mt-10 transition-transform group-hover:scale-150 duration-700"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-red-50 to-white shadow-inner border border-red-100/50 flex items-center justify-center text-red-500 group-hover:from-red-500 group-hover:to-red-400 group-hover:text-white transition-all duration-500 shadow-sm relative overflow-hidden">
                                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out"></div>
                                <i class="fas fa-rupee-sign text-xl relative z-10"></i>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 border border-red-100 shadow-sm text-xs font-bold font-inter">
                                Warning
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Unpaid Fines</p>
                            <h3 class="text-4xl font-extrabold text-slate-800 font-inter tracking-tight flex items-baseline gap-1">
                                <span class="text-2xl text-slate-400 font-medium">₹</span>
                                <span class="count-up" data-target="<?= htmlspecialchars($totalFines) ?>">0</span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                <!-- Recent Issues -->
                <div class="bg-white/60 backdrop-blur-2xl rounded-[2rem] overflow-hidden animate-enter delay-500 flex flex-col border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all hover:shadow-[0_8px_30px_rgb(99,102,241,0.08)] group">
                    <div class="p-6 md:p-8 border-b border-indigo-50/50 flex justify-between items-center bg-gradient-to-r from-white/50 to-transparent">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500">
                                <i class="fas fa-barcode"></i>
                            </div>
                            <h2 class="font-extrabold text-slate-800 text-lg tracking-tight">Recent Scans</h2>
                        </div>
                        <a href="../issue/issueBook.php" class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg hover:bg-indigo-600 hover:text-white transition-colors uppercase tracking-widest shadow-sm">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto flex-1 p-2">
                        <table class="w-full text-left text-sm table-modern">
                            <thead>
                                <tr>
                                    <th class="pl-6 text-[10px] tracking-[0.15em] font-bold text-slate-400 py-4">BOOK ITEM</th>
                                    <th class="text-[10px] tracking-[0.15em] font-bold text-slate-400 py-4">RECIPIENT</th>
                                    <th class="text-[10px] tracking-[0.15em] font-bold text-slate-400 py-4">DATE</th>
                                    <th class="pr-6 text-right text-[10px] tracking-[0.15em] font-bold text-slate-400 py-4">DUE</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/50">
                                <?php if($issues->num_rows > 0): ?>
                                    <?php while ($row = $issues->fetch_assoc()) { ?>
                                    <tr class="table-row-modern group/row cursor-default hover:bg-white/80 transition-colors">
                                        <td class="p-4 pl-6">
                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 rounded bg-indigo-50 flex flex-shrink-0 items-center justify-center text-indigo-400 border border-indigo-100/50">
                                                    <i class="fas fa-book text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800 text-sm group-hover/row:text-indigo-600 transition-colors"><?= $row['Title'] ?></div>
                                                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">ID: #<?= $row['Issue_ID'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-100 to-white border border-indigo-200 shadow-sm flex items-center justify-center text-[10px] text-indigo-700 font-extrabold shadow-inner">
                                                    <?= substr($row['Member_Name'], 0, 1) ?>
                                                </div>
                                                <span class="text-slate-600 font-semibold text-sm"><?= $row['Member_Name'] ?></span>
                                            </div>
                                        </td>
                                        <td class="p-4 text-slate-500 font-medium text-xs">
                                            <i class="far fa-calendar-alt text-slate-300 mr-1.5"></i>
                                            <?= date('M d', strtotime($row['Issue_Date'])) ?>
                                        </td>
                                        <td class="p-4 pr-6 text-right">
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100 shadow-sm">
                                                <i class="far fa-clock text-amber-400"></i>
                                                <?= date('M d', strtotime($row['Due_Date'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="p-12 text-center text-slate-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-3"><i class="fas fa-inbox text-2xl"></i></div>
                                                <p class="font-semibold text-sm">No recent transactions</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Returns -->
                <div class="bg-white/60 backdrop-blur-2xl rounded-[2rem] overflow-hidden animate-enter delay-500 flex flex-col border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all hover:shadow-[0_8px_30px_rgb(16,185,129,0.08)] group">
                    <div class="p-6 md:p-8 border-b border-emerald-50/50 flex justify-between items-center bg-gradient-to-r from-white/50 to-transparent">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500">
                                <i class="fas fa-undo-alt"></i>
                            </div>
                            <h2 class="font-extrabold text-slate-800 text-lg tracking-tight">Recent Returns</h2>
                        </div>
                        <a href="../return/returnBook.php" class="inline-flex items-center gap-2 text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg hover:bg-emerald-600 hover:text-white transition-colors uppercase tracking-widest shadow-sm">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto flex-1 p-2">
                        <table class="w-full text-left text-sm table-modern">
                            <thead>
                                <tr>
                                    <th class="pl-6 text-[10px] tracking-[0.15em] font-bold text-slate-400 py-4">BOOK ITEM</th>
                                    <th class="text-[10px] tracking-[0.15em] font-bold text-slate-400 py-4">RETURN DATE</th>
                                    <th class="pr-6 text-right text-[10px] tracking-[0.15em] font-bold text-slate-400 py-4">STATUS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/50">
                                <?php if($returns->num_rows > 0): ?>
                                    <?php while ($row = $returns->fetch_assoc()) { ?>
                                    <tr class="table-row-modern group/row cursor-default hover:bg-white/80 transition-colors">
                                        <td class="p-4 pl-6">
                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 rounded bg-emerald-50 flex flex-shrink-0 items-center justify-center text-emerald-400 border border-emerald-100/50">
                                                    <i class="fas fa-book-reader text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800 text-sm group-hover/row:text-emerald-600 transition-colors"><?= $row['Title'] ?></div>
                                                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Ref: #<?= $row['Return_ID'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4 text-slate-500 font-medium text-xs">
                                            <i class="far fa-calendar-check text-slate-300 mr-1.5"></i>
                                            <?= date('M d, Y', strtotime($row['Return_Date'])) ?>
                                        </td>
                                        <td class="p-4 pr-6 text-right">
                                            <?php if($row['Fine_Amount'] > 0): ?>
                                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-600 border border-red-100 shadow-sm">
                                                    <i class="fas fa-exclamation-circle"></i> Fine: ₹<?= $row['Fine_Amount'] ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm">
                                                    <i class="fas fa-check-circle"></i> Cleared
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="p-12 text-center text-slate-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-3"><i class="fas fa-check-double text-2xl"></i></div>
                                                <p class="font-semibold text-sm">No recent returns</p>
                                            </div>
                                        </td>
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

<script>
document.addEventListener("DOMContentLoaded", () => {
    const counters = document.querySelectorAll('.count-up');
    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            // Remove commas for parsing, but not strict since our data-target is raw number
            const count = +counter.innerText.replace(/,/g, '');
            const speed = 250; 
            const inc = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + inc).toLocaleString();
                setTimeout(updateCount, 15);
            } else {
                counter.innerText = target.toLocaleString();
            }
        };
        updateCount();
    });
});
</script>

</body>
</html>



