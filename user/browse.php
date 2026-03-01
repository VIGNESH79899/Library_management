<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

// Search Logic
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$sql = "SELECT B.*, C.Category_Name FROM book B LEFT JOIN category C ON B.Category_ID = C.Category_ID WHERE B.Status='Available'";
if ($search) {
    $safe = $conn->real_escape_string($search);
    $sql .= " AND (B.Title LIKE '%$safe%' OR B.Author LIKE '%$safe%' OR C.Category_Name LIKE '%$safe%')";
}
if ($category_filter) {
    $safe_cat = $conn->real_escape_string($category_filter);
    $sql .= " AND B.Category_ID = '$safe_cat'";
}
$books = $conn->query($sql);

$categories_sql = "SELECT * FROM category ORDER BY Category_Name";
$categories_result = $conn->query($categories_sql);
?>

<!-- ─── Borrow Modal ─────────────────────────────────────────── -->
<div id="borrowModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4"
     onclick="if(event.target===this)closeModal()">

    <div class="bg-white rounded-3xl shadow-2xl shadow-indigo-500/20 w-full max-w-md p-8 relative animate-fade-in-up">

        <!-- Close -->
        <button onclick="closeModal()"
                class="absolute top-5 right-5 text-slate-400 hover:text-slate-700 transition-colors">
            <i class="fas fa-times text-lg"></i>
        </button>

        <!-- Icon + Title -->
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 text-2xl flex-shrink-0">
                <i class="fas fa-book-reader"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Borrow Book</h2>
                <p id="modalBookTitle" class="text-slate-500 text-sm mt-0.5 line-clamp-1"></p>
            </div>
        </div>

        <!-- Date field -->
        <div class="mb-6">
            <label for="returnDateInput" class="block text-sm font-semibold text-slate-700 mb-2">
                <i class="fas fa-calendar-alt text-indigo-400 mr-1"></i> Select Return Date
            </label>
            <input type="date" id="returnDateInput"
                   class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all text-slate-700 font-medium text-base"
                   placeholder="Pick a date">
            <p class="text-xs text-slate-400 mt-2">
                <i class="fas fa-info-circle mr-1 text-indigo-300"></i>
                Choose a date between tomorrow and <strong id="maxDateLabel"></strong> (max 60 days).
            </p>
        </div>

        <!-- Alert area -->
        <div id="modalAlert" class="hidden mb-4 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2"></div>

        <!-- Actions -->
        <div class="flex gap-3">
            <button onclick="closeModal()"
                    class="flex-1 py-3 rounded-xl border-2 border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 transition-colors">
                Cancel
            </button>
            <button id="confirmBorrowBtn" onclick="confirmBorrow()"
                    class="flex-1 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-lg shadow-indigo-500/30 transition-all active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-check"></i> Confirm Borrow
            </button>
        </div>
    </div>
</div>

<!-- ─── Success Toast ─────────────────────────────────────────── -->
<div id="successToast"
     class="fixed bottom-6 right-6 z-50 hidden max-w-sm bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-xl shadow-emerald-100/60 flex items-start gap-3 animate-fade-in-up">
    <div class="w-8 h-8 bg-emerald-500 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
        <i class="fas fa-check text-sm"></i>
    </div>
    <div>
        <p class="font-bold text-sm">Book Borrowed!</p>
        <p id="toastMsg" class="text-xs mt-0.5"></p>
    </div>
    <button onclick="this.parentElement.classList.add('hidden')"
            class="ml-auto text-emerald-400 hover:text-emerald-700">
        <i class="fas fa-times"></i>
    </button>
</div>

<!-- ─── Error Toast ─────────────────────────────────────────── -->
<div id="errorToast"
     class="fixed bottom-6 right-6 z-50 hidden max-w-sm bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl shadow-xl shadow-red-100/60 flex items-start gap-3 animate-fade-in-up">
    <div class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
        <i class="fas fa-exclamation text-sm"></i>
    </div>
    <div>
        <p class="font-bold text-sm">Error</p>
        <p id="errorMsg" class="text-xs mt-0.5"></p>
    </div>
    <button onclick="this.parentElement.classList.add('hidden')"
            class="ml-auto text-red-400 hover:text-red-700">
        <i class="fas fa-times"></i>
    </button>
</div>

<!-- ─── Main Content ─────────────────────────────────────────── -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 animate-fade-in-up">

    <!-- Header & Search -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-[#5f2eea] to-[#8e2de2] tracking-tight mb-4 font-['Inter']">Discover Your Next Read</h1>
        <p class="text-slate-500 max-w-2xl mx-auto text-lg mb-8 font-['Inter']">Search through our vast collection of books and borrow them instantly.</p>

        <form class="max-w-xl mx-auto relative group mb-8">
            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400 group-focus-within:text-[#5f2eea] transition-colors"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title, author, or category..."
                   class="w-full pl-14 pr-4 py-4 rounded-full border-2 border-slate-100/80 hover:border-slate-200 focus:border-[#5f2eea] focus:ring-4 focus:ring-[#5f2eea]/20 outline-none transition-all shadow-[0_8px_30px_rgba(0,0,0,0.04)] text-slate-600 font-medium font-['Inter'] backdrop-blur-md bg-white/90">
            <?php if ($search || $category_filter): ?>
                <a href="browse.php" class="absolute inset-y-0 right-0 pr-6 flex items-center text-slate-400 hover:text-[#5f2eea] cursor-pointer transition-colors">
                    <i class="fas fa-times-circle text-lg"></i>
                </a>
            <?php endif; ?>
        </form>

        <!-- Category filter chips -->
        <div class="flex flex-wrap justify-center gap-3">
            <a href="browse.php<?= $search ? '?search='.urlencode($search) : '' ?>" class="<?= !$category_filter ? 'bg-gradient-to-r from-[#5f2eea] to-[#8e2de2] text-white shadow-[0_4px_15px_rgba(95,46,234,0.3)]' : 'backdrop-blur-md bg-white/70 text-slate-600 hover:bg-white border border-slate-200' ?> px-6 py-2 rounded-full font-bold text-sm transition-all duration-300 hover:-translate-y-0.5 font-['Inter']">All</a>
            <?php while($cat = $categories_result->fetch_assoc()): ?>
                <a href="browse.php?category=<?= urlencode($cat['Category_ID']) ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="<?= $category_filter == $cat['Category_ID'] ? 'bg-gradient-to-r from-[#5f2eea] to-[#8e2de2] text-white shadow-[0_4px_15px_rgba(95,46,234,0.3)]' : 'backdrop-blur-md bg-white/70 text-slate-600 hover:bg-white border border-slate-200' ?> px-6 py-2 rounded-full font-bold text-sm transition-all duration-300 hover:-translate-y-0.5 font-['Inter']"><?= htmlspecialchars($cat['Category_Name']) ?></a>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Books Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php if ($books->num_rows > 0): ?>
            <?php while ($book = $books->fetch_assoc()): ?>
                <div class="group backdrop-blur-md bg-white/80 rounded-[18px] border border-slate-100/50 overflow-hidden hover:shadow-[0_8px_30px_rgba(95,46,234,0.15)] transition-all duration-300 hover:-translate-y-2 flex flex-col h-full relative" data-book-id="<?= $book['Book_ID'] ?>">

                    <!-- Cover -->
                    <div class="h-64 relative overflow-hidden flex items-center justify-center bg-slate-100">
                        <img src="https://picsum.photos/seed/<?= $book['Book_ID'] ?>/400/600" alt="Book Cover" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity duration-300"></div>

                        <div class="absolute top-4 left-4 z-20">
                            <span class="bg-white/95 backdrop-blur-md text-[10px] uppercase font-bold px-3 py-1.5 rounded-full shadow-sm text-[#5f2eea] tracking-wider font-['Inter']">
                                <?= $book['Category_Name'] ?? 'General' ?>
                            </span>
                        </div>
                        
                        <div class="absolute top-4 right-4 z-20">
                            <span class="bg-emerald-500/90 backdrop-blur-md text-white text-[10px] uppercase font-bold px-3 py-1.5 rounded-full shadow-sm tracking-wider flex items-center gap-1.5 font-['Inter']">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Available
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 flex-1 flex flex-col relative z-20 bg-white/50 backdrop-blur-sm">
                        <h3 class="font-bold text-lg text-slate-800 mb-1 leading-tight line-clamp-2 group-hover:text-[#5f2eea] transition-colors font-['Inter']"
                            title="<?= htmlspecialchars($book['Title']) ?>">
                            <?= htmlspecialchars($book['Title']) ?>
                        </h3>
                        <p class="text-slate-500 text-sm font-medium mb-6 font-['Inter']">by <span class="text-slate-700"><?= htmlspecialchars($book['Author']) ?></span></p>

                        <div class="mt-auto">
                            <button
                                onclick="openBorrowModal(<?= $book['Book_ID'] ?>, '<?= addslashes(htmlspecialchars($book['Title'])) ?>')"
                                class="block w-full text-center bg-[#5f2eea]/10 hover:bg-gradient-to-r hover:from-[#5f2eea] hover:to-[#8e2de2] text-[#5f2eea] hover:text-white font-bold py-3.5 rounded-xl transition-all duration-300 shadow-sm hover:shadow-[0_8px_20px_rgba(95,46,234,0.3)] active:scale-95 group/btn cursor-pointer font-['Inter']">
                                <span class="group-hover/btn:hidden">Borrow Book</span>
                                <span class="hidden group-hover/btn:inline-flex items-center gap-2"><i class="fas fa-calendar-check"></i> Set Return Date</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full py-20 text-center">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 text-4xl">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No books found</h3>
                <p class="text-slate-400">We couldn't find any available books<?= $search ? ' matching "' . htmlspecialchars($search) . '"' : '' ?>.</p>
                <?php if ($search): ?>
                    <a href="browse.php" class="inline-block mt-6 text-indigo-600 font-bold hover:underline">Clear Search</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ─── Scripts ─────────────────────────────────────────── -->
<script>
let currentBookId = null;

// Set date constraints
const today    = new Date();
const tomorrow = new Date(today); tomorrow.setDate(today.getDate() + 1);
const maxDate  = new Date(today); maxDate.setDate(today.getDate() + 60);

function fmtDate(d) {
    return d.toISOString().split('T')[0];
}

function fmtDisplay(d) {
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function openBorrowModal(bookId, bookTitle) {
    currentBookId = bookId;

    document.getElementById('modalBookTitle').textContent = bookTitle;
    const inp = document.getElementById('returnDateInput');
    inp.min   = fmtDate(tomorrow);
    inp.max   = fmtDate(maxDate);
    inp.value = '';

    document.getElementById('maxDateLabel').textContent = fmtDisplay(maxDate);
    document.getElementById('modalAlert').classList.add('hidden');
    document.getElementById('confirmBorrowBtn').disabled = false;

    const modal = document.getElementById('borrowModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('borrowModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    currentBookId = null;
}

function showModalAlert(msg, type = 'error') {
    const el = document.getElementById('modalAlert');
    el.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border', 'border-red-200',
                                  'bg-amber-50', 'text-amber-700', 'border-amber-200');
    if (type === 'error') {
        el.className = 'mb-4 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 bg-red-50 text-red-700 border border-red-200';
        el.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${msg}`;
    } else {
        el.className = 'mb-4 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 bg-amber-50 text-amber-700 border border-amber-200';
        el.innerHTML = `<i class="fas fa-info-circle"></i> ${msg}`;
    }
}

function confirmBorrow() {
    const returnDate = document.getElementById('returnDateInput').value;

    if (!returnDate) {
        showModalAlert('Please select a return date.');
        return;
    }
    if (returnDate <= fmtDate(today)) {
        showModalAlert('Return date must be after today.');
        return;
    }
    if (returnDate > fmtDate(maxDate)) {
        showModalAlert('Return date cannot exceed 60 days from today.');
        return;
    }

    const btn = document.getElementById('confirmBorrowBtn');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    const fd = new FormData();
    fd.append('book_id',     currentBookId);
    fd.append('return_date', returnDate);

    fetch('borrow_book.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeModal();
                showToast(data.message);
                // Remove the borrowed card from the grid after a short delay
                setTimeout(() => {
                    const card = document.querySelector(`[data-book-id="${currentBookId}"]`);
                    if (card) card.remove();
                    location.reload();
                }, 2500);
            } else {
                showModalAlert(data.message);
                btn.disabled  = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Confirm Borrow';
            }
        })
        .catch(() => {
            showModalAlert('Network error. Please try again.');
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Confirm Borrow';
        });
}

function showToast(msg) {
    document.getElementById('toastMsg').textContent = msg;
    const t = document.getElementById('successToast');
    t.classList.remove('hidden');
    t.classList.add('flex');
    setTimeout(() => { t.classList.add('hidden'); t.classList.remove('flex'); }, 5000);
}

// Close modal on Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>

</body>
</html>
