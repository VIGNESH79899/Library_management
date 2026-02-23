/**
 * AURORALIB — Fine Payment Portal
 * payment.js  |  Vanilla JS — No Dependencies
 */

'use strict';

/* ─── Payment Method Selection ─────────────────────────────────── */
let selectedMethod = null;

function selectMethod(el, method) {
  // Deselect all
  document.querySelectorAll('.method-card').forEach(card => {
    card.classList.remove('active');
    card.setAttribute('aria-checked', 'false');
  });

  // Activate chosen
  el.classList.add('active');
  el.setAttribute('aria-checked', 'true');
  selectedMethod = method;

  // Hide validation error if showing
  const alertEl = document.getElementById('alertNoMethod');
  if (alertEl) alertEl.classList.remove('show');
}

/* ─── Modal Management ─────────────────────────────────────────── */
const modalOverlay = document.getElementById('paymentModal');
const modalBox     = document.getElementById('modalBox');

function setModalState(state) {
  modalBox.classList.remove('state-processing', 'state-success', 'state-failed');
  modalBox.classList.add('state-' + state);

  const iconWrap = document.getElementById('modalIconWrap');
  const titleEl  = document.getElementById('modalTitle');
  const msgEl    = document.getElementById('modalMsg');
  const closeBtn = document.getElementById('modalCloseBtn');

  switch (state) {
    case 'processing':
      iconWrap.innerHTML  = '<div class="modal-spinner"></div>';
      titleEl.textContent = 'Processing Payment\u2026';
      msgEl.textContent   = 'Please wait while we securely process your fine payment. Do not close this window.';
      closeBtn.style.display = 'none';
      break;

    case 'success':
      iconWrap.innerHTML  = '<i class="fas fa-check"></i>';
      titleEl.textContent = 'Payment Successful!';
      msgEl.innerHTML     = 'Your fine has been cleared.<br>A confirmation will be sent to your registered email.';
      closeBtn.style.display = 'inline-flex';
      break;

    case 'failed':
      iconWrap.innerHTML  = '<i class="fas fa-times"></i>';
      titleEl.textContent = 'Payment Failed';
      msgEl.textContent   = 'Something went wrong. Please try again or choose a different payment method.';
      closeBtn.style.display = 'inline-flex';
      break;
  }
}

function showModal(state) {
  setModalState(state);
  modalOverlay.classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  modalOverlay.classList.remove('show');
  document.body.style.overflow = '';

  // Re-enable pay button
  const payBtn = document.getElementById('payBtn');
  if (payBtn) {
    payBtn.disabled  = false;
    payBtn.innerHTML = payBtn.getAttribute('data-original-html');
  }
}

// Close modal on outside click
modalOverlay?.addEventListener('click', function (e) {
  if (e.target === modalOverlay) closeModal();
});

// Keyboard close
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape' && modalOverlay.classList.contains('show')) {
    closeModal();
  }
});

/* ─── Pay Button Handler ────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
  const payBtn = document.getElementById('payBtn');
  if (!payBtn) return;

  // Cache original button HTML
  payBtn.setAttribute('data-original-html', payBtn.innerHTML);

  payBtn.addEventListener('click', function () {
    // Validate method selection
    if (!selectedMethod) {
      const alertEl = document.getElementById('alertNoMethod');
      if (alertEl) {
        alertEl.classList.add('show');
        alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
      // Shake the method grid
      const grid = document.querySelector('.method-grid');
      if (grid) {
        grid.style.animation = 'none';
        void grid.offsetHeight; // reflow
        grid.style.animation = 'failShake 0.45s ease';
        setTimeout(() => { grid.style.animation = ''; }, 600);
      }
      return;
    }

    // Loading state on button
    payBtn.disabled  = true;
    payBtn.innerHTML = `
      <div class="spinner"></div>
      <span>Processing&hellip;</span>
    `;

    // Show processing modal after short delay
    setTimeout(() => {
      showModal('processing');

      // UI-only demo: transition to success after 2.8s
      // In production: replace with real AJAX/fetch call
      setTimeout(() => {
        setModalState('success');
      }, 2800);
    }, 400);
  });

  // Keyboard support on method cards
  document.querySelectorAll('.method-card').forEach(card => {
    card.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        card.click();
      }
    });
  });
});
