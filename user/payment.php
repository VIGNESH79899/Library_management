<?php
/* ============================================================
   AURORALIB – Fine Payment Portal
   payment.php  |  UI-Only Demo (No Gateway Integrated)
   ============================================================ */

// ── Session & Auth ──────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login_user.php");
    exit;
}

include "../config/db.php";

// ── Demo / Default Fine Data ────────────────────────────────
// In production: pull from database using $_GET['issue_id']
$issue_id = isset($_GET['issue_id']) ? (int)$_GET['issue_id'] : 0;

// Fallback demo values (shown when no issue_id passed)
$member_name = $_SESSION['user_name'] ?? "Vignesh S";
$member_id   = sprintf("ARI-%04d", $_SESSION['user_id']);
$book_title  = "DBMS Concepts";
$due_date    = "2026-02-20";   // Return date: Feb 20
$days_late   = 3;              // Feb 20 → Feb 23 = 3 days late
$fine_amount = 30;             // ₹10/day × 3 days

if ($issue_id > 0) {
    $sql = "
        SELECT I.Issue_ID, I.Due_Date,
               B.Title,
               U.Name AS member_name,
               U.Member_ID,
               DATEDIFF(CURDATE(), I.Due_Date) AS days_late
        FROM Issue I
        JOIN Book B         ON I.Book_ID   = B.Book_ID
        JOIN UserAccounts U ON I.Member_ID = U.Member_ID
        WHERE I.Issue_ID = ?
          AND I.Issue_ID NOT IN (SELECT Issue_ID FROM Return_Book)
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $issue_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            $member_name = htmlspecialchars($row['member_name']);
            $member_id   = sprintf("ARI-%04d", $row['Member_ID']);
            $book_title  = htmlspecialchars($row['Title']);
            $due_date    = $row['Due_Date'];
            $days_late   = max(0, (int)$row['days_late']);
            $fine_amount = $days_late * 10;
        }
    }
}

// Format display values
$due_date_fmt = date("M d, Y", strtotime($due_date));
$fine_display = number_format($fine_amount, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AuroraLib – Secure Fine Payment Portal">
    <title>Fine Payment – AuroraLib</title>

    <!-- Tailwind (matches rest of AuroraLib) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts — Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Payment Stylesheet -->
    <link rel="stylesheet" href="../assets/css/payment.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.5s ease-out forwards',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%':   { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-nav {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.4);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

<?php include "includes/navbar.php"; ?>

<!-- ── Page ────────────────────────────────────────────────── -->
<div class="pay-page">
<div class="pay-container">

    <!-- ① Header ────────────────────────────────────────── -->
    <div class="pay-header anim-card anim-d1">
        <div class="pay-logo">
            <div class="pay-logo-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="pay-logo-text">Aurora<span>Lib</span></div>
        </div>
        <div class="pay-subtitle">Fine Payment Portal</div>
    </div>

    <!-- ② Fine Details Card ─────────────────────────────── -->
    <div class="pay-card anim-card anim-d2">
        <div class="pay-section-title">
            <i class="fas fa-file-invoice-dollar"></i>
            Fine Details
        </div>

        <div class="detail-grid">
            <!-- Member Name -->
            <div class="detail-item">
                <span class="detail-label">Member Name</span>
                <span class="detail-value"><?php echo $member_name; ?></span>
            </div>

            <!-- Member ID -->
            <div class="detail-item">
                <span class="detail-label">Member ID</span>
                <span class="detail-value"><?php echo $member_id; ?></span>
            </div>

            <!-- Book Title — full width -->
            <div class="detail-item" style="grid-column: 1 / -1;">
                <span class="detail-label">Book Title</span>
                <span class="detail-value"><?php echo $book_title; ?></span>
            </div>

            <!-- Due Date -->
            <div class="detail-item">
                <span class="detail-label">Due Date</span>
                <span class="detail-value"><?php echo $due_date_fmt; ?></span>
            </div>

            <!-- Days Late -->
            <div class="detail-item">
                <span class="detail-label">Days Late</span>
                <span class="detail-value overdue">
                    <i class="fas fa-clock" style="font-size:0.78rem;margin-right:3px;"></i>
                    <?php echo $days_late; ?> day<?php echo $days_late != 1 ? 's' : ''; ?>
                </span>
            </div>
        </div>

        <!-- Fine Amount Hero -->
        <div class="fine-hero">
            <div class="fine-hero-label">Total Fine Amount</div>
            <div class="fine-hero-amount">₹<?php echo $fine_display; ?></div>
            <div class="fine-hero-note">
                <i class="fas fa-info-circle"></i>
                Calculated at <strong>₹10 / day</strong> &times; <?php echo $days_late; ?> day<?php echo $days_late != 1 ? 's' : ''; ?> late
            </div>
        </div>
    </div>

    <!-- ③ Payment Method Card ────────────────────────────── -->
    <div class="pay-card anim-card anim-d3">
        <div class="pay-section-title">
            <i class="fas fa-wallet"></i>
            Select Payment Method
        </div>

        <div class="method-grid" role="radiogroup" aria-label="Payment Methods">

            <!-- UPI -->
            <div class="method-card"
                 id="method-upi"
                 onclick="selectMethod(this,'upi')"
                 role="radio" aria-checked="false" tabindex="0">
                <div class="method-check"><i class="fas fa-check"></i></div>
                <span class="method-icon"><i class="fas fa-mobile-screen-button"></i></span>
                <div class="method-name">UPI</div>
            </div>

            <!-- Credit / Debit Card -->
            <div class="method-card"
                 id="method-card"
                 onclick="selectMethod(this,'card')"
                 role="radio" aria-checked="false" tabindex="0">
                <div class="method-check"><i class="fas fa-check"></i></div>
                <span class="method-icon"><i class="fas fa-credit-card"></i></span>
                <div class="method-name">Card</div>
            </div>

            <!-- Net Banking -->
            <div class="method-card"
                 id="method-netbanking"
                 onclick="selectMethod(this,'netbanking')"
                 role="radio" aria-checked="false" tabindex="0">
                <div class="method-check"><i class="fas fa-check"></i></div>
                <span class="method-icon"><i class="fas fa-building-columns"></i></span>
                <div class="method-name">Net Banking</div>
            </div>

        </div>

        <!-- Validation alert -->
        <div class="alert-no-method" id="alertNoMethod">
            <i class="fas fa-triangle-exclamation"></i>
            Please select a payment method to continue.
        </div>
    </div>

    <!-- ④ Pay Button Card ───────────────────────────────── -->
    <div class="pay-card anim-card anim-d4" style="padding: 22px 26px;">
        <button id="payBtn" class="btn-pay" type="button"
                aria-label="Pay ₹<?php echo $fine_display; ?> Securely">
            <i class="fas fa-lock" style="font-size:0.85rem;"></i>
            <span>Pay ₹<?php echo $fine_display; ?> Securely</span>
            <i class="fas fa-arrow-right btn-arrow"></i>
        </button>

        <div class="security-badge">
            <i class="fas fa-shield-halved"></i>
            256-bit SSL Encrypted
            &nbsp;·&nbsp;
            <i class="fas fa-eye-slash"></i>
            No card data stored
        </div>
    </div>

    <!-- Back link -->
    <div class="anim-card anim-d5" style="text-align:center; padding-top:4px;">
        <a href="my_books.php"
           class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left" style="font-size:0.65rem;"></i>
            Back to My Books
        </a>
    </div>

</div><!-- /.pay-container -->
</div><!-- /.pay-page -->


<!-- ⑤ Payment Status Modal ─────────────────────────────── -->
<div class="modal-overlay" id="paymentModal"
     role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-box state-processing" id="modalBox">

        <div class="modal-icon-wrap" id="modalIconWrap">
            <div class="modal-spinner"></div>
        </div>

        <h2 class="modal-title" id="modalTitle">Processing Payment…</h2>
        <p  class="modal-msg"   id="modalMsg">
            Please wait while we securely process your fine payment.
            Do not close this window.
        </p>

        <button class="modal-close-btn" id="modalCloseBtn"
                onclick="closeModal()" style="display:none;">
            <i class="fas fa-times-circle"></i> Close
        </button>
    </div>
</div>

<script src="../assets/js/payment.js"></script>

</body>
</html>
