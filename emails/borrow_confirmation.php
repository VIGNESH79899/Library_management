<?php
/**
 * ANTIGRAVITY — Book Borrow Email Template
 * File: emails/borrow_confirmation.php
 *
 * Usage with PHPMailer:
 *   $student_name = "Vignesh S";
 *   $book_title   = "DBMS Concepts";
 *   $due_date     = "Mar 05, 2026";
 *   $issue_date   = "Feb 23, 2026";
 *   $member_id    = "ARI-0007";
 *   ob_start();
 *   include 'emails/borrow_confirmation.php';
 *   $html_body = ob_get_clean();
 *   $mail->isHTML(true);
 *   $mail->Body = $html_body;
 */

// ── Fallback demo values (remove when integrating) ──────────
$student_name = $student_name ?? "Vignesh S";
$book_title   = $book_title   ?? "DBMS Concepts";
$due_date     = $due_date     ?? "Mar 05, 2026";
$issue_date   = $issue_date   ?? "Feb 23, 2026";
$member_id    = $member_id    ?? "ARI-0007";
$contact_email = $contact_email ?? "library@aurora.edu.in";
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Book Borrowed – Antigravity Library</title>
  <!--[if mso]>
  <noscript>
    <xml>
      <o:OfficeDocumentSettings>
        <o:PixelsPerInch>96</o:PixelsPerInch>
      </o:OfficeDocumentSettings>
    </xml>
  </noscript>
  <![endif]-->
  <style type="text/css">
    /* Reset for email clients */
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
    /* Responsive */
    @media only screen and (max-width: 600px) {
      .email-wrapper { width: 100% !important; }
      .email-card    { padding: 28px 20px !important; }
      .header-cell   { padding: 28px 20px !important; }
      .footer-cell   { padding: 20px !important; }
      .book-box      { padding: 16px !important; }
      .due-box       { padding: 16px !important; }
      .btn-td        { padding: 24px 0 !important; }
    }
  </style>
</head>

<!--
  ╔══════════════════════════════════════════════════╗
  ║      ANTIGRAVITY  —  Book Borrow Confirmation    ║
  ╚══════════════════════════════════════════════════╝
-->
<body style="margin:0;padding:0;background-color:#eef2f7;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">

  <!-- ── Outer wrapper ───────────────────────────────────── -->
  <table role="presentation" border="0" cellpadding="0" cellspacing="0"
         width="100%" style="background-color:#eef2f7;min-width:100%;">
    <tr>
      <td align="center" style="padding:40px 16px;">

        <!-- ── Email card ─────────────────────────────────── -->
        <table class="email-wrapper" role="presentation" border="0"
               cellpadding="0" cellspacing="0" width="600"
               style="max-width:600px;width:100%;border-radius:16px;
                      overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.10);">

          <!-- ══ HEADER ══════════════════════════════════════ -->
          <tr>
            <td class="header-cell" align="center"
                style="background:linear-gradient(135deg,#0f1c3f 0%,#1a3a8f 60%,#1d4ed8 100%);
                       padding:36px 40px;">

              <!-- Logo row -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding-right:10px;vertical-align:middle;">
                    <!-- Icon box -->
                    <div style="width:38px;height:38px;background:#2563eb;border-radius:10px;
                                display:inline-block;text-align:center;line-height:38px;
                                font-size:18px;box-shadow:0 4px 12px rgba(37,99,235,0.40);">
                      📚
                    </div>
                  </td>
                  <td style="vertical-align:middle;">
                    <span style="font-size:22px;font-weight:800;letter-spacing:-0.5px;
                                 color:#ffffff;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      AuroraLib
                    </span>
                  </td>
                </tr>
              </table>

              <!-- Tagline -->
              <p style="margin:12px 0 0;font-size:12px;font-weight:600;letter-spacing:2.5px;
                         text-transform:uppercase;color:rgba(255,255,255,0.55);
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                Library Management System
              </p>
            </td>
          </tr>

          <!-- ══ BODY ════════════════════════════════════════ -->
          <tr>
            <td class="email-card"
                style="background:#ffffff;padding:40px 44px;">

              <!-- ── Greeting ─────────────────────────────── -->
              <p style="margin:0 0 6px;font-size:13px;font-weight:700;letter-spacing:1.8px;
                         text-transform:uppercase;color:#2563eb;
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                Book Confirmed
              </p>
              <h1 style="margin:0 0 16px;font-size:24px;font-weight:800;color:#0f172a;
                          letter-spacing:-0.5px;line-height:1.25;
                          font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                Happy Reading, <?php echo htmlspecialchars($student_name); ?>! 📖
              </h1>
              <p style="margin:0 0 28px;font-size:15px;color:#475569;line-height:1.7;
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                Your book has been successfully issued from the
                <strong style="color:#1e293b;">Antigravity Library</strong>.
                Here are your borrowing details:
              </p>

              <!-- ── Book detail box ──────────────────────── -->
              <table class="book-box" role="presentation" border="0"
                     cellpadding="0" cellspacing="0" width="100%"
                     style="background:#f0f7ff;border:1px solid #bfdbfe;
                            border-radius:12px;padding:0;margin-bottom:16px;">
                <tr>
                  <td style="padding:20px 24px;">

                    <!-- Label -->
                    <p style="margin:0 0 6px;font-size:11px;font-weight:700;
                               letter-spacing:1.6px;text-transform:uppercase;color:#60a5fa;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      📗 &nbsp;Book Title
                    </p>
                    <!-- Book name -->
                    <p style="margin:0;font-size:18px;font-weight:800;color:#1d4ed8;
                               letter-spacing:-0.2px;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      <?php echo htmlspecialchars($book_title); ?>
                    </p>
                  </td>
                </tr>
              </table>

              <!-- ── Meta detail row ──────────────────────── -->
              <table role="presentation" border="0" cellpadding="0"
                     cellspacing="0" width="100%" style="margin-bottom:16px;">
                <tr>
                  <!-- Issue Date -->
                  <td width="48%" valign="top"
                      style="background:#f8fafc;border:1px solid #e2e8f0;
                             border-radius:12px;padding:16px 18px;">
                    <p style="margin:0 0 4px;font-size:10px;font-weight:700;
                               letter-spacing:1.4px;text-transform:uppercase;color:#94a3b8;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      📅 &nbsp;Issue Date
                    </p>
                    <p style="margin:0;font-size:15px;font-weight:700;color:#1e293b;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      <?php echo htmlspecialchars($issue_date); ?>
                    </p>
                  </td>

                  <td width="4%">&nbsp;</td>

                  <!-- Member ID -->
                  <td width="48%" valign="top"
                      style="background:#f8fafc;border:1px solid #e2e8f0;
                             border-radius:12px;padding:16px 18px;">
                    <p style="margin:0 0 4px;font-size:10px;font-weight:700;
                               letter-spacing:1.4px;text-transform:uppercase;color:#94a3b8;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      🪪 &nbsp;Member ID
                    </p>
                    <p style="margin:0;font-size:15px;font-weight:700;color:#1e293b;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      <?php echo htmlspecialchars($member_id); ?>
                    </p>
                  </td>
                </tr>
              </table>

              <!-- ── Due Date box (highlighted red) ──────── -->
              <table class="due-box" role="presentation" border="0"
                     cellpadding="0" cellspacing="0" width="100%"
                     style="background:#fff7f7;border:1.5px solid #fca5a5;
                            border-radius:12px;padding:0;margin-bottom:28px;">
                <tr>
                  <td style="padding:20px 24px;">
                    <table role="presentation" border="0" cellpadding="0"
                           cellspacing="0" width="100%">
                      <tr>
                        <td>
                          <p style="margin:0 0 4px;font-size:10px;font-weight:700;
                                     letter-spacing:1.6px;text-transform:uppercase;color:#f87171;
                                     font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                            ⚠️ &nbsp;Return By (Due Date)
                          </p>
                          <p style="margin:0;font-size:20px;font-weight:800;color:#dc2626;
                                     font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                            <?php echo htmlspecialchars($due_date); ?>
                          </p>
                        </td>
                        <td align="right" valign="middle">
                          <!-- Fine rate badge -->
                          <span style="display:inline-block;background:#fef2f2;
                                       border:1px solid #fecaca;color:#ef4444;
                                       font-size:11px;font-weight:700;border-radius:20px;
                                       padding:4px 12px;white-space:nowrap;
                                       font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                            ₹10 / day fine
                          </span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- ── Warning message ───────────────────────── -->
              <table role="presentation" border="0" cellpadding="0"
                     cellspacing="0" width="100%"
                     style="background:#fffbeb;border-left:4px solid #f59e0b;
                            border-radius:0 8px 8px 0;margin-bottom:32px;">
                <tr>
                  <td style="padding:14px 18px;">
                    <p style="margin:0;font-size:13.5px;color:#92400e;line-height:1.6;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      <strong>Please return the book before the due date</strong>
                      to avoid late fines. A ₹10 fine will be charged for each day past the due date.
                    </p>
                  </td>
                </tr>
              </table>

              <!-- ── CTA Button ─────────────────────────────── -->
              <table class="btn-td" role="presentation" border="0"
                     cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td align="center" style="padding:4px 0 32px;">
                    <a href="#" target="_blank"
                       style="display:inline-block;background:linear-gradient(135deg,#1d4ed8,#2563eb);
                              color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;
                              padding:14px 36px;border-radius:10px;letter-spacing:0.3px;
                              box-shadow:0 4px 14px rgba(37,99,235,0.35);
                              font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      View My Books &rarr;
                    </a>
                  </td>
                </tr>
              </table>

              <!-- ── Divider ────────────────────────────────── -->
              <table role="presentation" border="0" cellpadding="0"
                     cellspacing="0" width="100%">
                <tr>
                  <td style="border-top:1px solid #f1f5f9;padding-top:24px;">
                    <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.65;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      If you have any questions, contact us at
                      <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>"
                         style="color:#2563eb;text-decoration:none;font-weight:600;">
                        <?php echo htmlspecialchars($contact_email); ?>
                      </a>.
                      Do not reply directly to this email.
                    </p>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- ══ FOOTER ══════════════════════════════════════ -->
          <tr>
            <td class="footer-cell" align="center"
                style="background:#0f172a;padding:24px 40px;">

              <p style="margin:0 0 6px;font-size:13px;font-weight:800;color:#ffffff;
                         letter-spacing:-0.2px;
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                AuroraLib &mdash; Library Management System
              </p>

              <p style="margin:0 0 12px;font-size:11.5px;color:#64748b;
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                Aurora College &bull; This is an automated notification. Please do not reply.
              </p>

              <p style="margin:0;font-size:11px;color:#334155;
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                &copy; <?php echo date('Y'); ?> Antigravity Library. All rights reserved.
              </p>

            </td>
          </tr>

        </table>
        <!-- /.email-wrapper -->

        <!-- ── Below-card note ───────────────────────────── -->
        <p style="margin:20px 0 0;font-size:11px;color:#94a3b8;text-align:center;
                   font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
          You received this email because you borrowed a book from AuroraLib Library.
        </p>

      </td>
    </tr>
  </table>

</body>
</html>
