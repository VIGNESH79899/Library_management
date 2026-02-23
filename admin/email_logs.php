<?php
/* ============================================================
   ANTIGRAVITY LMS — Email Logs Admin Page
   File: admin/email_logs.php
   ============================================================ */

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

// ── Constants ────────────────────────────────────────────────
const LOGS_PER_PAGE = 25;

// ── Input sanitisation ───────────────────────────────────────
$page        = max(1, (int)($_GET['page']       ?? 1));
$filter_type = $_GET['type']   ?? '';
$filter_stat = $_GET['status'] ?? '';
$search      = trim($_GET['q'] ?? '');

$allowed_types   = ['BORROW_CONFIRMATION', 'REMINDER', 'OVERDUE'];
$allowed_statuses = ['SUCCESS', 'FAILED'];

if (!in_array($filter_type, $allowed_types,    true)) $filter_type = '';
if (!in_array($filter_stat, $allowed_statuses, true)) $filter_stat = '';

$offset = ($page - 1) * LOGS_PER_PAGE;

// ── Build WHERE clause ───────────────────────────────────────
$where_parts = [];
$bind_types  = '';
$bind_values = [];

if ($filter_type !== '') {
    $where_parts[] = 'el.email_type = ?';
    $bind_types   .= 's';
    $bind_values[] = $filter_type;
}
if ($filter_stat !== '') {
    $where_parts[] = 'el.status = ?';
    $bind_types   .= 's';
    $bind_values[] = $filter_stat;
}
if ($search !== '') {
    $where_parts[] = '(el.recipient_email LIKE ? OR el.subject LIKE ?)';
    $bind_types   .= 'ss';
    $like          = '%' . $search . '%';
    $bind_values[] = $like;
    $bind_values[] = $like;
}

$where_sql = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';

// ── Summary Stats ─────────────────────────────────────────────
$stats_sql  = "SELECT
    COUNT(*) AS total,
    SUM(status = 'SUCCESS') AS success_count,
    SUM(status = 'FAILED')  AS failed_count,
    SUM(email_type = 'BORROW_CONFIRMATION') AS borrow_count,
    SUM(email_type = 'REMINDER')            AS reminder_count,
    SUM(email_type = 'OVERDUE')             AS overdue_count
FROM email_log";

$stats_res  = $conn->query($stats_sql);
$stats      = $stats_res ? $stats_res->fetch_assoc()
                          : ['total'=>0,'success_count'=>0,'failed_count'=>0,
                             'borrow_count'=>0,'reminder_count'=>0,'overdue_count'=>0];

// ── Total filtered count (for pagination) ───────────────────
$count_sql  = "SELECT COUNT(*) AS cnt FROM email_log el $where_sql";
$count_stmt = $conn->prepare($count_sql);
if ($bind_types && $count_stmt) {
    $count_stmt->bind_param($bind_types, ...$bind_values);
}
$count_stmt && $count_stmt->execute();
$total_rows = $count_stmt ? $count_stmt->get_result()->fetch_assoc()['cnt'] : 0;
$total_pages = max(1, (int)ceil($total_rows / LOGS_PER_PAGE));
$page = min($page, $total_pages);

// ── Fetch logs ───────────────────────────────────────────────
$logs_sql = "
    SELECT el.id, el.member_id, el.email_type, el.recipient_email,
           el.subject, el.status, el.error_message, el.sent_at
    FROM email_log el
    $where_sql
    ORDER BY el.sent_at DESC
    LIMIT ? OFFSET ?
";

$logs_types  = $bind_types . 'ii';
$logs_values = array_merge($bind_values, [LOGS_PER_PAGE, $offset]);

$logs_stmt = $conn->prepare($logs_sql);
$logs_stmt->bind_param($logs_types, ...$logs_values);
$logs_stmt->execute();
$logs_result = $logs_stmt->get_result();

// ── Helpers ──────────────────────────────────────────────────
function typeBadge(string $type): string {
    $map = [
        'BORROW_CONFIRMATION' => ['bg-blue-100 text-blue-700 border-blue-200',    'fa-book-open',       'Borrow'],
        'REMINDER'            => ['bg-amber-100 text-amber-700 border-amber-200',  'fa-bell',            'Reminder'],
        'OVERDUE'             => ['bg-red-100 text-red-700 border-red-200',        'fa-exclamation-circle','Overdue'],
    ];
    [$cls, $icon, $label] = $map[$type] ?? ['bg-slate-100 text-slate-600 border-slate-200', 'fa-envelope', $type];
    return "<span class=\"inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border $cls\">
                <i class=\"fas $icon text-[0.65rem]\"></i>$label</span>";
}

function statusBadge(string $status): string {
    if ($status === 'SUCCESS') {
        return "<span class=\"inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold
                             bg-emerald-50 text-emerald-700 border border-emerald-200\">
                    <i class=\"fas fa-check-circle text-[0.65rem]\"></i>Success</span>";
    }
    return "<span class=\"inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold
                         bg-red-50 text-red-700 border border-red-200\">
                <i class=\"fas fa-times-circle text-[0.65rem]\"></i>Failed</span>";
}

function paginationUrl(int $p, array $get): string {
    $q = array_merge($get, ['page' => $p]);
    unset($q['page']);
    return '?' . http_build_query(array_filter(array_merge(['page' => $p], $q)));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Email Logs</title>
    <?php include "../includers/headers.php"; ?>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <?php include "../includers/sidebar.php"; ?>

    <div class="flex-1 ml-64 flex flex-col">
        <?php include "../includers/navbar.php"; ?>

        <main class="p-8 space-y-6">

            <!-- ── Page Header ──────────────────────────────── -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Email Logs</h1>
                    <p class="text-slate-500 mt-1 text-sm">
                        Audit trail for all outbound emails — sorted by latest first.
                    </p>
                </div>
                <!-- Quick refresh -->
                <a href="email_logs.php"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900
                          text-white text-sm font-semibold shadow-sm transition-all">
                    <i class="fas fa-rotate-right"></i> Refresh
                </a>
            </div>

            <!-- ── Summary Cards ─────────────────────────────── -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Total -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 border-indigo-500">
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Total Sent</p>
                    <h2 class="text-3xl font-extrabold text-slate-800 mt-1"><?= number_format($stats['total']) ?></h2>
                </div>
                <!-- Success -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 border-emerald-500">
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Successful</p>
                    <h2 class="text-3xl font-extrabold text-emerald-600 mt-1"><?= number_format($stats['success_count']) ?></h2>
                </div>
                <!-- Failed -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 border-red-500">
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Failed</p>
                    <h2 class="text-3xl font-extrabold text-red-600 mt-1"><?= number_format($stats['failed_count']) ?></h2>
                </div>
                <!-- Borrow -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 border-blue-500">
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Borrows</p>
                    <h2 class="text-3xl font-extrabold text-blue-600 mt-1"><?= number_format($stats['borrow_count']) ?></h2>
                </div>
                <!-- Reminders -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 border-amber-500">
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Reminders</p>
                    <h2 class="text-3xl font-extrabold text-amber-600 mt-1"><?= number_format($stats['reminder_count']) ?></h2>
                </div>
                <!-- Overdue -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 border-orange-500">
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Overdue</p>
                    <h2 class="text-3xl font-extrabold text-orange-600 mt-1"><?= number_format($stats['overdue_count']) ?></h2>
                </div>
            </div>

            <!-- ── Failure rate bar ───────────────────────────── -->
            <?php if ($stats['total'] > 0):
                $fail_pct = round(($stats['failed_count'] / $stats['total']) * 100, 1);
                $bar_cls  = $fail_pct > 10 ? 'bg-red-500' : ($fail_pct > 0 ? 'bg-amber-400' : 'bg-emerald-400');
            ?>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-5">
                <div class="flex-shrink-0">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Failure Rate</p>
                    <p class="text-2xl font-extrabold <?= $fail_pct > 10 ? 'text-red-600' : 'text-emerald-600' ?>">
                        <?= $fail_pct ?>%
                    </p>
                </div>
                <div class="flex-1">
                    <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full <?= $bar_cls ?> rounded-full transition-all"
                             style="width:<?= min(100, $fail_pct) ?>%"></div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        <?= $stats['failed_count'] ?> failed out of <?= $stats['total'] ?> attempts
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- ── Filters + Search ───────────────────────────── -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <form method="GET" action="email_logs.php"
                      class="flex flex-wrap items-end gap-3">

                    <!-- Search -->
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Search
                        </label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Email or subject…"
                                   class="w-full pl-9 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400
                                          transition-all">
                        </div>
                    </div>

                    <!-- Type filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Email Type
                        </label>
                        <select name="type"
                                class="px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-white
                                       focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400
                                       transition-all cursor-pointer">
                            <option value="">All Types</option>
                            <option value="BORROW_CONFIRMATION" <?= $filter_type==='BORROW_CONFIRMATION'?'selected':'' ?>>Borrow Confirmation</option>
                            <option value="REMINDER"            <?= $filter_type==='REMINDER'           ?'selected':'' ?>>Reminder</option>
                            <option value="OVERDUE"             <?= $filter_type==='OVERDUE'            ?'selected':'' ?>>Overdue</option>
                        </select>
                    </div>

                    <!-- Status filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Status
                        </label>
                        <select name="status"
                                class="px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-white
                                       focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400
                                       transition-all cursor-pointer">
                            <option value="">All Statuses</option>
                            <option value="SUCCESS" <?= $filter_stat==='SUCCESS'?'selected':'' ?>>Success</option>
                            <option value="FAILED"  <?= $filter_stat==='FAILED' ?'selected':'' ?>>Failed</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold
                                   rounded-xl shadow-sm transition-all flex items-center gap-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="email_logs.php"
                       class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold
                              rounded-xl transition-all flex items-center gap-2">
                        <i class="fas fa-xmark"></i> Clear
                    </a>
                </form>
            </div>

            <!-- ── Log Table ───────────────────────────────────── -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                <!-- Table header bar -->
                <div class="p-5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-700 flex items-center gap-2">
                            <i class="fas fa-envelope-open-text text-indigo-400"></i>
                            Email Activity Log
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Showing <?= number_format($total_rows) ?> record<?= $total_rows!=1?'s':'' ?>
                            <?= ($filter_type || $filter_stat || $search) ? '(filtered)' : '' ?>
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="p-4 font-semibold text-xs text-slate-400 uppercase tracking-wider w-12">#</th>
                                <th class="p-4 font-semibold text-xs text-slate-400 uppercase tracking-wider">Type</th>
                                <th class="p-4 font-semibold text-xs text-slate-400 uppercase tracking-wider">Recipient</th>
                                <th class="p-4 font-semibold text-xs text-slate-400 uppercase tracking-wider">Subject</th>
                                <th class="p-4 font-semibold text-xs text-slate-400 uppercase tracking-wider text-center">Status</th>
                                <th class="p-4 font-semibold text-xs text-slate-400 uppercase tracking-wider">Sent At</th>
                                <th class="p-4 font-semibold text-xs text-slate-400 uppercase tracking-wider w-8"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if ($logs_result->num_rows === 0): ?>
                            <tr>
                                <td colspan="7" class="p-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-slate-400">
                                        <i class="fas fa-inbox text-4xl"></i>
                                        <p class="font-semibold">No email logs found</p>
                                        <p class="text-xs">Try adjusting your filters.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php while ($log = $logs_result->fetch_assoc()):
                                $is_failed = ($log['status'] === 'FAILED');
                                $row_cls   = $is_failed
                                    ? 'bg-red-50/60 hover:bg-red-50 border-l-2 border-l-red-400'
                                    : 'hover:bg-slate-50/80';
                            ?>
                            <tr class="<?= $row_cls ?> transition-colors" id="log-<?= $log['id'] ?>">

                                <!-- ID -->
                                <td class="p-4 text-slate-400 text-xs font-mono"><?= $log['id'] ?></td>

                                <!-- Type badge -->
                                <td class="p-4"><?= typeBadge($log['email_type']) ?></td>

                                <!-- Recipient -->
                                <td class="p-4">
                                    <span class="font-medium text-slate-700">
                                        <?= htmlspecialchars($log['recipient_email']) ?>
                                    </span>
                                    <?php if ($log['member_id']): ?>
                                    <span class="ml-1 text-xs text-slate-400">(#<?= $log['member_id'] ?>)</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Subject (truncated) -->
                                <td class="p-4 max-w-xs">
                                    <p class="truncate text-slate-600 text-sm"
                                       title="<?= htmlspecialchars($log['subject']) ?>">
                                        <?= htmlspecialchars($log['subject']) ?>
                                    </p>
                                </td>

                                <!-- Status -->
                                <td class="p-4 text-center"><?= statusBadge($log['status']) ?></td>

                                <!-- Sent At -->
                                <td class="p-4 text-xs text-slate-500 whitespace-nowrap">
                                    <i class="fas fa-clock mr-1 text-slate-300"></i>
                                    <?= date('M d, Y  H:i:s', strtotime($log['sent_at'])) ?>
                                </td>

                                <!-- Error expand -->
                                <td class="p-4 text-center">
                                    <?php if ($is_failed && $log['error_message']): ?>
                                    <button onclick="toggleError(<?= $log['id'] ?>)"
                                            title="View error"
                                            class="w-7 h-7 rounded-lg bg-red-100 hover:bg-red-200 text-red-500
                                                   flex items-center justify-center transition-all">
                                        <i class="fas fa-circle-info text-xs"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Error Detail Row (hidden by default) -->
                            <?php if ($is_failed && $log['error_message']): ?>
                            <tr id="error-<?= $log['id'] ?>" class="hidden bg-red-50">
                                <td colspan="7" class="px-6 pb-4 pt-0">
                                    <div class="bg-red-100 border border-red-200 rounded-xl p-4">
                                        <p class="text-xs font-bold text-red-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                            <i class="fas fa-bug"></i> Error Message
                                        </p>
                                        <code class="text-xs text-red-700 font-mono break-all leading-relaxed">
                                            <?= htmlspecialchars($log['error_message']) ?>
                                        </code>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>

                            <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ── Pagination ─────────────────────────────── -->
                <?php if ($total_pages > 1): ?>
                <div class="p-5 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-400">
                        Page <strong><?= $page ?></strong> of <strong><?= $total_pages ?></strong>
                        &bull; <?= number_format($total_rows) ?> records
                    </p>
                    <div class="flex gap-1.5">
                        <!-- Prev -->
                        <?php if ($page > 1): ?>
                        <a href="<?= paginationUrl($page - 1, $_GET) ?>"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>

                        <!-- Page numbers (window of 5) -->
                        <?php
                        $start = max(1, $page - 2);
                        $end   = min($total_pages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                            $active = $i === $page;
                        ?>
                        <a href="<?= paginationUrl($i, $_GET) ?>"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors
                                  <?= $active ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>

                        <!-- Next -->
                        <?php if ($page < $total_pages): ?>
                        <a href="<?= paginationUrl($page + 1, $_GET) ?>"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<script>
function toggleError(id) {
    const row = document.getElementById('error-' + id);
    if (!row) return;
    row.classList.toggle('hidden');
}
</script>

</body>
</html>
