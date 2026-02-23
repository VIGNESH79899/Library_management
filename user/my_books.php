<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

$user_id = $_SESSION['user_id'];

// Fetch borrowed books that are NOT returned yet
$sql = "SELECT I.Issue_ID, I.Book_ID, B.Title, B.Author, I.Issue_Date, I.Due_Date
        FROM issue I
        JOIN book B ON I.Book_ID = B.Book_ID
        WHERE I.Member_ID = ? AND I.Issue_ID NOT IN (SELECT Issue_ID FROM return_book)
        ORDER BY I.Due_Date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- ─── Return Confirmation Modal ─────────────────────────────── -->
<div id="returnModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4"
     onclick="if(event.target===this)closeReturnModal()">

    <div class="bg-white rounded-3xl shadow-2xl shadow-emerald-500/20 w-full max-w-md p-8 relative animate-fade-in-up">

        <!-- Close -->
        <button onclick="closeReturnModal()"
                class="absolute top-5 right-5 text-slate-400 hover:text-slate-700 transition-colors">
            <i class="fas fa-times text-lg"></i>
        </button>

        <!-- Icon + Title -->
        <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-2xl flex-shrink-0">
                <i class="fas fa-undo-alt"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Return Book</h2>
                <p class="text-slate-500 text-sm mt-0.5">Confirm early return</p>
            </div>
        </div>

        <!-- Book Info -->
        <div class="bg-slate-50 rounded-2xl p-4 mb-5 border border-slate-100">
            <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-1">Book</p>
            <p id="rModalTitle" class="font-bold text-slate-800 text-base leading-snug"></p>
            <div class="flex gap-6 mt-3">
                <div>
                    <p class="text-xs text-slate-400 font-medium">Borrowed On</p>
                    <p id="rModalIssueDate" class="text-sm font-semibold text-slate-700 mt-0.5"></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Return By</p>
                    <p id="rModalDueDate" class="text-sm font-semibold mt-0.5"></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Returning Today</p>
                    <p class="text-sm font-semibold text-slate-700 mt-0.5"><?= date('M d, Y') ?></p>
                </div>
            </div>
        </div>

        <!-- Fine Estimate -->
        <div id="rModalFine" class="hidden mb-5 bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 rounded-2xl p-4">
            <p class="text-xs font-bold uppercase tracking-widest text-orange-500 mb-2 flex items-center gap-1.5">
                <i class="fas fa-exclamation-triangle"></i> Overdue Fine
            </p>
            <div class="flex items-center justify-between">
                <p id="rModalFineInfo" class="text-sm text-slate-600"></p>
                <p id="rModalFineAmt" class="text-2xl font-extrabold text-red-600"></p>
            </div>
        </div>
        <div id="rModalNoFine" class="hidden mb-5 bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center gap-3">
            <i class="fas fa-award text-emerald-500 text-xl"></i>
            <div>
                <p class="font-bold text-emerald-700 text-sm">No Fine!</p>
                <p class="text-xs text-emerald-600">You're returning before the due date — no charges. 🎉</p>
            </div>
        </div>

        <!-- Alert -->
        <div id="rModalAlert" class="hidden mb-4 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2"></div>

        <!-- Actions -->
        <div class="flex gap-3">
            <button onclick="closeReturnModal()"
                    class="flex-1 py-3 rounded-xl border-2 border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 transition-colors">
                Cancel
            </button>
            <button id="confirmReturnBtn" onclick="confirmReturn()"
                    class="flex-1 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-lg shadow-emerald-500/30 transition-all active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-undo"></i> Confirm Return
            </button>
        </div>
    </div>
</div>

<!-- ─── Result Toast ─────────────────────────────────────────── -->
<div id="resultToast"
     class="fixed bottom-6 right-6 z-50 hidden max-w-sm px-5 py-4 rounded-2xl shadow-xl flex items-start gap-3">
    <div id="toastIcon" class="w-8 h-8 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-sm"></div>
    <div>
        <p id="toastTitle" class="font-bold text-sm"></p>
        <p id="toastMsg" class="text-xs mt-0.5"></p>
    </div>
    <button onclick="this.parentElement.classList.add('hidden')" class="ml-auto">
        <i class="fas fa-times text-xs opacity-50 hover:opacity-100"></i>
    </button>
</div>

<!-- ─── Main Content ─────────────────────────────────────────── -->
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in-up">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800">My Bookshelf</h1>
        <p class="text-slate-500 mt-1">Track your borrowed books and return dates.</p>
    </div>

    <!-- Active Loans -->
    <div class="space-y-4" id="bookList">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()):
                $due_date = strtotime($row['Due_Date']);
                $today    = time();
                $is_late  = $today > $due_date;

                // Calculate fine
                $fine = 0;
                $days_late = 0;
                if ($is_late) {
                    $days_late = floor(($today - $due_date) / (60 * 60 * 24));
                    $fine = $days_late * 10;
                }
            ?>
            <div id="book-card-<?= $row['Issue_ID'] ?>"
                 class="bg-white rounded-2xl border border-slate-100 p-6 flex flex-col md:flex-row items-center gap-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">

                <?php if ($is_late): ?>
                    <div class="absolute top-0 right-0 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-bl-xl">Overdue</div>
                <?php else: ?>
                    <div class="absolute top-0 right-0 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-bl-xl">Active</div>
                <?php endif; ?>

                <!-- Book Icon -->
                <div class="w-16 h-16 rounded-xl <?= $is_late ? 'bg-red-50' : 'bg-slate-100' ?> flex items-center justify-center text-<?= $is_late ? 'red-400' : 'slate-400' ?> text-2xl flex-shrink-0">
                    <i class="fas fa-book"></i>
                </div>

                <!-- Info -->
                <div class="flex-1 text-center md:text-left">
                    <h3 class="font-bold text-lg text-slate-800"><?= htmlspecialchars($row['Title']) ?></h3>
                    <p class="text-slate-500 text-sm">by <?= htmlspecialchars($row['Author']) ?></p>
                </div>

                <!-- Dates -->
                <div class="flex items-center gap-6 text-sm flex-shrink-0">
                    <div class="text-center">
                        <p class="text-slate-400 text-xs font-bold uppercase">Borrowed On</p>
                        <p class="font-semibold text-slate-700"><?= date('M d, Y', strtotime($row['Issue_Date'])) ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-slate-400 text-xs font-bold uppercase">Return By</p>
                        <p class="font-semibold <?= $is_late ? 'text-red-500' : 'text-slate-700' ?>">
                            <?= date('M d, Y', strtotime($row['Due_Date'])) ?>
                        </p>
                    </div>
                    <?php if ($fine > 0): ?>
                    <div class="text-center">
                        <p class="text-slate-400 text-xs font-bold uppercase">Late Fine</p>
                        <p class="font-bold text-red-600">₹<?= $fine ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Return Button -->
                <div class="w-full md:w-auto mt-2 md:mt-0 flex-shrink-0">
                    <button
                        onclick="openReturnModal(
                            <?= $row['Issue_ID'] ?>,
                            '<?= addslashes(htmlspecialchars($row['Title'])) ?>',
                            '<?= date('M d, Y', strtotime($row['Issue_Date'])) ?>',
                            '<?= $row['Due_Date'] ?>',
                            <?= $is_late ? 'true' : 'false' ?>,
                            <?= $days_late ?>,
                            <?= $fine ?>
                        )"
                        class="w-full flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm transition-all active:scale-95
                               <?= $is_late
                                   ? 'bg-red-50 hover:bg-red-600 text-red-600 hover:text-white border-2 border-red-200 hover:border-red-600 hover:shadow-lg hover:shadow-red-500/20'
                                   : 'bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border-2 border-emerald-200 hover:border-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20'
                               ?>">
                        <i class="fas fa-undo-alt"></i>
                        Return Early
                    </button>
                    <?php if ($is_late && $fine > 0): ?>
                    <a href="payment.php?issue_id=<?= $row['Issue_ID'] ?>"
                       class="w-full mt-2 flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm transition-all active:scale-95
                              bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white shadow-md hover:shadow-cyan-500/30 hover:-translate-y-0.5">
                        <i class="fas fa-receipt"></i>
                        Pay Fine ₹<?= $fine ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-2xl">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700">No active loans</h3>
                <p class="text-slate-500 mb-6">You haven't borrowed any books yet.</p>
                <a href="browse.php" class="inline-flex items-center gap-2 text-brand-600 font-bold hover:underline">
                    Browse Collection <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ─── Scripts ─────────────────────────────────────────── -->
<script>
const FINE_RATE = 10;
let currentIssueId = null;

function fmtDate(dateStr) {
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function openReturnModal(issueId, title, issueDate, dueDateStr, isLate, daysLate, fine) {
    currentIssueId = issueId;

    document.getElementById('rModalTitle').textContent     = title;
    document.getElementById('rModalIssueDate').textContent = issueDate;

    const dueDateEl = document.getElementById('rModalDueDate');
    dueDateEl.textContent = fmtDate(dueDateStr);
    dueDateEl.className   = 'text-sm font-semibold mt-0.5 ' + (isLate ? 'text-red-500' : 'text-emerald-600');

    const fineBox   = document.getElementById('rModalFine');
    const noFineBox = document.getElementById('rModalNoFine');

    if (isLate && fine > 0) {
        document.getElementById('rModalFineInfo').textContent = `${daysLate} day(s) overdue × ₹${FINE_RATE}/day`;
        document.getElementById('rModalFineAmt').textContent  = `₹${fine.toFixed(2)}`;
        fineBox.classList.remove('hidden');
        fineBox.classList.add('flex');
        noFineBox.classList.add('hidden');
    } else {
        fineBox.classList.add('hidden');
        noFineBox.classList.remove('hidden');
        noFineBox.classList.add('flex');
    }

    document.getElementById('rModalAlert').classList.add('hidden');

    const btn = document.getElementById('confirmReturnBtn');
    btn.disabled  = false;
    btn.innerHTML = '<i class="fas fa-undo"></i> Confirm Return';

    const modal = document.getElementById('returnModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeReturnModal() {
    const modal = document.getElementById('returnModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    currentIssueId = null;
}

function confirmReturn() {
    if (!currentIssueId) return;

    // ⚠️ Save BEFORE closeReturnModal() nullifies currentIssueId
    const issueIdToReturn = currentIssueId;

    const btn = document.getElementById('confirmReturnBtn');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Returning...';

    const fd = new FormData();
    fd.append('issue_id', issueIdToReturn);

    fetch('return_book.php', { method: 'POST', body: fd })
        .then(r => {
            if (!r.ok) throw new Error('Server returned ' + r.status);
            return r.json();
        })
        .then(data => {
            if (data.success) {
                closeReturnModal();
                // Find and animate-out the card using the SAVED id
                const card = document.getElementById('book-card-' + issueIdToReturn);
                if (card) {
                    card.style.transition = 'all 0.4s ease';
                    card.style.opacity    = '0';
                    card.style.transform  = 'scale(0.95)';
                    setTimeout(() => {
                        card.remove();
                        const list = document.getElementById('bookList');
                        if (list && list.children.length === 0) {
                            list.innerHTML = `
                                <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-2xl">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-700">No active loans</h3>
                                    <p class="text-slate-500 mb-6">All books have been returned.</p>
                                    <a href="browse.php" class="inline-flex items-center gap-2 text-blue-600 font-bold hover:underline">
                                        Browse Collection <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>`;
                        }
                    }, 450);
                }
                showToast(data.on_time ? 'success' : 'warning',
                          data.on_time ? 'Returned! 🎉' : 'Returned with Fine',
                          data.message);
            } else {
                showRModalAlert(data.message || 'Return failed. Please try again.');
                btn.disabled  = false;
                btn.innerHTML = '<i class="fas fa-undo"></i> Confirm Return';
            }
        })
        .catch(err => {
            showRModalAlert('Request failed: ' + err.message + '. Please try again.');
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-undo"></i> Confirm Return';
        });
}

function showRModalAlert(msg) {
    const el = document.getElementById('rModalAlert');
    el.className = 'mb-4 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 bg-red-50 text-red-700 border border-red-200';
    el.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${msg}`;
    el.classList.remove('hidden');
}

function showToast(type, title, msg) {
    const toast    = document.getElementById('resultToast');
    const icon     = document.getElementById('toastIcon');
    const titleEl  = document.getElementById('toastTitle');
    const msgEl    = document.getElementById('toastMsg');

    if (type === 'success') {
        toast.className = 'fixed bottom-6 right-6 z-50 max-w-sm px-5 py-4 rounded-2xl shadow-xl flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800';
        icon.className  = 'w-8 h-8 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-sm bg-emerald-500';
        icon.innerHTML  = '<i class="fas fa-check"></i>';
    } else {
        toast.className = 'fixed bottom-6 right-6 z-50 max-w-sm px-5 py-4 rounded-2xl shadow-xl flex items-start gap-3 bg-orange-50 border border-orange-200 text-orange-800';
        icon.className  = 'w-8 h-8 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-sm bg-orange-500';
        icon.innerHTML  = '<i class="fas fa-receipt"></i>';
    }

    titleEl.textContent = title;
    msgEl.textContent   = msg;
    toast.classList.remove('hidden');

    setTimeout(() => toast.classList.add('hidden'), 7000);
}

// Close modal on Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeReturnModal(); });
</script>

</body>
</html>
