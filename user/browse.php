<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

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
$sql .= " ORDER BY B.Book_ID DESC";
$books = $conn->query($sql);

$categories_sql = "SELECT * FROM category ORDER BY Category_Name";
$categories_result = $conn->query($categories_sql);

$popular_sql = "
    SELECT B.Book_ID, B.Title, B.Author, B.Status, B.Available_Quantity, C.Category_Name, COUNT(I.Issue_ID) AS borrow_count
    FROM book B
    LEFT JOIN category C ON B.Category_ID = C.Category_ID
    LEFT JOIN issue I ON I.Book_ID = B.Book_ID
    GROUP BY B.Book_ID, B.Title, B.Author, B.Status, B.Available_Quantity, C.Category_Name
    ORDER BY borrow_count DESC, B.Book_ID DESC
    LIMIT 6";
$popular_books = $conn->query($popular_sql);

$gradients = [
    'from-indigo-500 to-purple-600',
    'from-emerald-400 to-cyan-500',
    'from-rose-400 to-red-500',
    'from-amber-400 to-orange-500',
    'from-blue-500 to-indigo-600',
    'from-fuchsia-500 to-pink-600',
    'from-teal-400 to-emerald-500',
    'from-violet-500 to-fuchsia-500'
];
?>

<style>
.book-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.book-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
}
.borrow-btn {
    transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, color 0.2s ease;
}
.borrow-btn:hover:not(:disabled) {
    transform: scale(1.02);
}
</style>

<div id="borrowModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4"
     onclick="if(event.target===this)closeModal()">

    <div class="bg-white rounded-3xl shadow-2xl shadow-indigo-500/20 w-full max-w-md p-8 relative animate-fade-in-up">
        <button onclick="closeModal()"
                class="absolute top-5 right-5 text-slate-400 hover:text-slate-700 transition-colors duration-200">
            <i class="fas fa-times text-lg"></i>
        </button>

        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 text-2xl flex-shrink-0">
                <i class="fas fa-book-reader"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Borrow Book</h2>
                <p id="modalBookTitle" class="text-slate-500 text-sm mt-0.5 line-clamp-1"></p>
            </div>
        </div>

        <div class="mb-6">
            <label for="returnDateInput" class="block text-sm font-semibold text-slate-700 mb-2">
                <i class="fas fa-calendar-alt text-indigo-400 mr-1"></i> Select Return Date
            </label>
            <input type="date" id="returnDateInput"
                   class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-200 text-slate-700 font-medium text-base"
                   placeholder="Pick a date">
            <p class="text-xs text-slate-400 mt-2">
                <i class="fas fa-info-circle mr-1 text-indigo-300"></i>
                Choose a date between tomorrow and <strong id="maxDateLabel"></strong> (max 60 days).
            </p>
        </div>

        <div id="modalAlert" class="hidden mb-4 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2"></div>

        <div class="flex gap-3">
            <button onclick="closeModal()"
                    class="flex-1 py-3 rounded-xl border-2 border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 transition-colors duration-200">
                Cancel
            </button>
            <button id="confirmBorrowBtn" onclick="confirmBorrow()"
                    class="flex-1 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-lg shadow-indigo-500/30 transition-all duration-200 active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-check"></i> Confirm Borrow
            </button>
        </div>
    </div>
</div>

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

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 animate-fade-in-up">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-[#5f2eea] to-[#8e2de2] tracking-tight mb-4">Discover Your Next Read</h1>
        <p class="text-slate-500 max-w-2xl mx-auto text-lg mb-8">Search through our collection and borrow books instantly.</p>

        <form class="max-w-xl mx-auto relative group mb-8">
            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400 group-focus-within:text-[#5f2eea] transition-colors duration-200"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title, author, or category..."
                   class="w-full pl-14 pr-4 py-4 rounded-full border-2 border-slate-100/80 hover:border-slate-200 focus:border-[#5f2eea] focus:ring-4 focus:ring-[#5f2eea]/20 outline-none transition-all duration-200 shadow-[0_8px_30px_rgba(0,0,0,0.04)] text-slate-600 font-medium backdrop-blur-md bg-white/90">
            <?php if ($search || $category_filter): ?>
                <a href="browse.php" class="absolute inset-y-0 right-0 pr-6 flex items-center text-slate-400 hover:text-[#5f2eea] cursor-pointer transition-colors duration-200">
                    <i class="fas fa-times-circle text-lg"></i>
                </a>
            <?php endif; ?>
        </form>

        <div class="flex flex-wrap justify-center gap-3">
            <a href="browse.php<?= $search ? '?search='.urlencode($search) : '' ?>" class="<?= !$category_filter ? 'bg-gradient-to-r from-[#5f2eea] to-[#8e2de2] text-white shadow-[0_4px_15px_rgba(95,46,234,0.3)]' : 'bg-white/90 text-slate-600 hover:bg-white border border-slate-200' ?> px-6 py-2 rounded-full font-bold text-sm transition-all duration-200 hover:-translate-y-0.5">All</a>
            <?php while($cat = $categories_result->fetch_assoc()): ?>
                <a href="browse.php?category=<?= urlencode($cat['Category_ID']) ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="<?= $category_filter == $cat['Category_ID'] ? 'bg-gradient-to-r from-[#5f2eea] to-[#8e2de2] text-white shadow-[0_4px_15px_rgba(95,46,234,0.3)]' : 'bg-white/90 text-slate-600 hover:bg-white border border-slate-200' ?> px-6 py-2 rounded-full font-bold text-sm transition-all duration-200 hover:-translate-y-0.5"><?= htmlspecialchars($cat['Category_Name']) ?></a>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="mb-10">
        <div class="flex items-end justify-between mb-5">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Most Borrowed Books</h2>
                <p class="text-sm text-slate-500 mt-1">Popular picks based on borrowing activity.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($popular_books && $popular_books->num_rows > 0): ?>
                <?php while ($popular = $popular_books->fetch_assoc()): ?>
                    <?php
                        $gradient = $gradients[$popular['Book_ID'] % count($gradients)];
                        $is_available = strtolower((string) $popular['Status']) === 'available' && (int) $popular['Available_Quantity'] > 0;
                    ?>
                    <div class="book-card rounded-2xl p-5 bg-gradient-to-br <?= $gradient ?> text-white relative overflow-hidden flex flex-col min-h-[250px] cursor-pointer"
                         onclick='trackViewedBook(<?= (int) $popular['Book_ID'] ?>, <?= json_encode($popular['Title']) ?>, <?= json_encode($popular['Author']) ?>, <?= json_encode($popular['Category_Name'] ?? 'General') ?>)'>
                        <div class="absolute inset-0 bg-black/25"></div>
                        <div class="relative z-10 flex items-start justify-between gap-3 mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-3 py-1 rounded-full border border-white/20">
                                <?= htmlspecialchars($popular['Category_Name'] ?? 'General') ?>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full <?= $is_available ? 'bg-emerald-500/90' : 'bg-red-500/90' ?>">
                                <?= $is_available ? 'Available' : 'Unavailable' ?>
                            </span>
                        </div>

                        <h3 class="relative z-10 text-xl font-extrabold leading-tight mb-2 line-clamp-2"><?= htmlspecialchars($popular['Title']) ?></h3>
                        <p class="relative z-10 text-sm text-white/90 mb-2">by <?= htmlspecialchars($popular['Author']) ?></p>
                        <p class="relative z-10 text-xs text-white/80 mb-5">Borrowed <?= (int) $popular['borrow_count'] ?> times</p>

                        <div class="mt-auto relative z-10">
                            <!-- TEMPORARILY DISABLED: Student self-borrowing 
                            <button
                                <?= $is_available ? '' : 'disabled' ?>
                                onclick='event.stopPropagation(); trackViewedBook(<?= (int) $popular['Book_ID'] ?>, <?= json_encode($popular['Title']) ?>, <?= json_encode($popular['Author']) ?>, <?= json_encode($popular['Category_Name'] ?? 'General') ?>); <?= $is_available ? "openBorrowModal(" . (int) $popular['Book_ID'] . ", " . json_encode($popular['Title']) . ")" : "" ?>'
                                class="borrow-btn w-full text-center py-3 rounded-xl font-bold <?= $is_available ? 'bg-white text-[#5f2eea] hover:bg-white/95 shadow-lg shadow-black/15' : 'bg-white/30 text-white/80 cursor-not-allowed' ?>">
                                <?= $is_available ? 'Borrow Book' : 'Unavailable' ?>
                            </button>
                            -->
                            <button class="borrow-btn-disabled w-full text-center py-3 rounded-xl font-bold bg-slate-200 text-slate-500 cursor-not-allowed" disabled>
                                Borrow via Librarian
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-10 text-center rounded-2xl border border-slate-100 bg-white text-slate-500">
                    No borrowing data available yet.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="flex items-end justify-between mb-5">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Available Books</h2>
                <p class="text-sm text-slate-500 mt-1">Browse and borrow from currently available books.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if ($books && $books->num_rows > 0): ?>
                <?php while ($book = $books->fetch_assoc()): ?>
                    <?php $gradient = $gradients[$book['Book_ID'] % count($gradients)]; ?>
                    <div class="book-card rounded-2xl p-5 bg-gradient-to-br <?= $gradient ?> text-white relative overflow-hidden flex flex-col min-h-[250px] cursor-pointer"
                         data-book-id="<?= (int) $book['Book_ID'] ?>"
                         onclick='trackViewedBook(<?= (int) $book['Book_ID'] ?>, <?= json_encode($book['Title']) ?>, <?= json_encode($book['Author']) ?>, <?= json_encode($book['Category_Name'] ?? 'General') ?>)'>
                        <div class="absolute inset-0 bg-black/25"></div>

                        <div class="relative z-10 flex items-start justify-between gap-2 mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-3 py-1 rounded-full border border-white/20 truncate max-w-[58%]">
                                <?= htmlspecialchars($book['Category_Name'] ?? 'General') ?>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-emerald-500/90">
                                Available (<?= (int) $book['Available_Quantity'] ?>)
                            </span>
                        </div>

                        <h3 class="relative z-10 text-lg font-extrabold leading-tight mb-2 line-clamp-2" title="<?= htmlspecialchars($book['Title']) ?>">
                            <?= htmlspecialchars($book['Title']) ?>
                        </h3>
                        <p class="relative z-10 text-sm text-white/90 mb-6">by <?= htmlspecialchars($book['Author']) ?></p>

                        <div class="mt-auto relative z-10">
                            <!-- TEMPORARILY DISABLED: Student self-borrowing
                            <button
                                onclick='event.stopPropagation(); trackViewedBook(<?= (int) $book['Book_ID'] ?>, <?= json_encode($book['Title']) ?>, <?= json_encode($book['Author']) ?>, <?= json_encode($book['Category_Name'] ?? 'General') ?>); openBorrowModal(<?= (int) $book['Book_ID'] ?>, <?= json_encode($book['Title']) ?>)'
                                class="borrow-btn w-full text-center bg-white text-[#5f2eea] font-bold py-3 rounded-xl shadow-lg shadow-black/15 hover:bg-white/95">
                                Borrow Book
                            </button>
                            -->
                            <button class="borrow-btn-disabled w-full text-center py-3 rounded-xl font-bold bg-slate-200 text-slate-500 cursor-not-allowed" disabled>
                                Borrow via Librarian
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center bg-white rounded-2xl border border-slate-100">
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
</div>

<script>
let currentBookId = null;

const today = new Date();
const tomorrow = new Date(today); tomorrow.setDate(today.getDate() + 1);
const maxDate = new Date(today); maxDate.setDate(today.getDate() + 60);

function fmtDate(d) {
    return d.toISOString().split('T')[0];
}

function fmtDisplay(d) {
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function trackViewedBook(id, title, author, category) {
    const RECENT_KEY = 'aurora_recently_viewed_books';
    let recent = [];

    try {
        recent = JSON.parse(localStorage.getItem(RECENT_KEY) || '[]');
    } catch (e) {
        recent = [];
    }

    if (!Array.isArray(recent)) {
        recent = [];
    }

    recent = recent.filter((item) => Number(item.id) !== Number(id));
    recent.unshift({
        id: Number(id),
        title: title || '',
        author: author || '',
        category: category || 'General',
        viewedAt: new Date().toISOString()
    });

    localStorage.setItem(RECENT_KEY, JSON.stringify(recent.slice(0, 3)));
}

function openBorrowModal(bookId, bookTitle) {
    currentBookId = bookId;

    document.getElementById('modalBookTitle').textContent = bookTitle;
    const inp = document.getElementById('returnDateInput');
    inp.min = fmtDate(tomorrow);
    inp.max = fmtDate(maxDate);
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
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    const fd = new FormData();
    fd.append('book_id', currentBookId);
    fd.append('return_date', returnDate);

    fetch('borrow_book.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeModal();
                showToast(data.message);
                setTimeout(() => {
                    const card = document.querySelector(`[data-book-id="${currentBookId}"]`);
                    if (card) card.remove();
                    location.reload();
                }, 2500);
            } else {
                showModalAlert(data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Confirm Borrow';
            }
        })
        .catch(() => {
            showModalAlert('Network error. Please try again.');
            btn.disabled = false;
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

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>

</body>
</html>

