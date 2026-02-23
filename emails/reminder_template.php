<?php
/**
 * ANTIGRAVITY — Due-Date Reminder Email Template
 * File: emails/reminder_template.php
 *
 * Variables expected (set by caller before ob_start/include):
 *   $member_name   string  e.g. "Vignesh S"
 *   $book_title    string  e.g. "DBMS Concepts"
 *   $due_date      string  formatted e.g. "Feb 25, 2026"
 *   $member_id     string  e.g. "ARI-0007"
 *   $days_left     int     should always be 2 when called from cron
 *   $contact_email string
 */

$member_name   = $member_name   ?? 'Member';
$book_title    = $book_title    ?? 'Your Book';
$due_date      = $due_date      ?? date('M d, Y', strtotime('+2 days'));
$member_id     = $member_id     ?? '';
$days_left     = $days_left     ?? 2;
$contact_email = $contact_email ?? 'library@aurora.edu.in';
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Return Reminder – Antigravity Library</title>
  <style type="text/css">
    body, table, td, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
    table, td { mso-table-lspace:0pt; mso-table-rspace:0pt; }
    @media only screen and (max-width:600px){
      .email-wrapper{ width:100%!important; }
      .email-card   { padding:24px 16px!important; }
      .header-cell  { padding:28px 20px!important; }
      .due-box      { padding:16px!important; }
    }
  </style>
</head>
<body style="margin:0;padding:0;background-color:#fef9f0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">

  <table role="presentation" border="0" cellpadding="0" cellspacing="0"
         width="100%" style="background-color:#fef9f0;min-width:100%;">
    <tr>
      <td align="center" style="padding:40px 16px;">

        <table class="email-wrapper" role="presentation" border="0"
               cellpadding="0" cellspacing="0" width="600"
               style="max-width:600px;width:100%;border-radius:16px;overflow:hidden;
                      box-shadow:0 4px 24px rgba(0,0,0,0.10);">

          <!-- ══ HEADER — Amber warning theme ══════════════ -->
          <tr>
            <td class="header-cell" align="center"
                style="background:linear-gradient(135deg,#78350f 0%,#b45309 55%,#d97706 100%);
                       padding:36px 40px;">
              <!-- Logo -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding-right:10px;vertical-align:middle;">
                    <div style="width:38px;height:38px;background:#f59e0b;border-radius:10px;
                                display:inline-block;text-align:center;line-height:38px;font-size:18px;
                                box-shadow:0 4px 12px rgba(0,0,0,0.25);">⏰</div>
                  </td>
                  <td style="vertical-align:middle;">
                    <span style="font-size:22px;font-weight:800;letter-spacing:-0.5px;color:#ffffff;
                                 font-family:'Segoe UI',Helvetica,Arial,sans-serif;">Antigravity</span>
                  </td>
                </tr>
              </table>
              <p style="margin:12px 0 0;font-size:12px;font-weight:600;letter-spacing:2.5px;
                         text-transform:uppercase;color:rgba(255,255,255,0.60);
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                Return Reminder · Library Management System
              </p>
            </td>
          </tr>

          <!-- ══ BODY ════════════════════════════════════════ -->
          <tr>
            <td class="email-card"
                style="background:#ffffff;padding:40px 44px;">

              <!-- Urgency label -->
              <p style="margin:0 0 6px;font-size:12px;font-weight:700;letter-spacing:2px;
                         text-transform:uppercase;color:#d97706;
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                ⚠&nbsp; Action Required
              </p>

              <h1 style="margin:0 0 16px;font-size:24px;font-weight:800;color:#0f172a;
                          letter-spacing:-0.5px;line-height:1.25;
                          font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                Hi <?php echo htmlspecialchars($member_name); ?>, your book is due in
                <span style="color:#d97706;"><?php echo (int)$days_left; ?> day<?php echo $days_left!=1?'s':''; ?>!</span>
              </h1>

              <p style="margin:0 0 28px;font-size:15px;color:#475569;line-height:1.7;
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                This is a friendly reminder that the book below is due back at the
                <strong style="color:#1e293b;">Antigravity Library</strong> very soon.
                Please return it on time to avoid late fines.
              </p>

              <!-- Book box -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                     width="100%"
                     style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;
                            margin-bottom:16px;">
                <tr>
                  <td style="padding:20px 24px;">
                    <p style="margin:0 0 5px;font-size:10px;font-weight:700;letter-spacing:1.5px;
                               text-transform:uppercase;color:#b45309;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      📗 &nbsp;Book to Return
                    </p>
                    <p style="margin:0;font-size:18px;font-weight:800;color:#78350f;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      <?php echo htmlspecialchars($book_title); ?>
                    </p>
                  </td>
                </tr>
              </table>

              <!-- Meta row: Member ID + Due Date -->
              <table role="presentation" border="0" cellpadding="0"
                     cellspacing="0" width="100%" style="margin-bottom:16px;">
                <tr>
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
                      <?php echo htmlspecialchars($member_id ?: 'N/A'); ?>
                    </p>
                  </td>
                  <td width="4%">&nbsp;</td>
                  <!-- Due date (highlighted red) -->
                  <td width="48%" valign="top"
                      style="background:#fff7f7;border:1.5px solid #fca5a5;
                             border-radius:12px;padding:16px 18px;">
                    <p style="margin:0 0 4px;font-size:10px;font-weight:700;
                               letter-spacing:1.4px;text-transform:uppercase;color:#f87171;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      📅 &nbsp;Due Date
                    </p>
                    <p style="margin:0;font-size:15px;font-weight:700;color:#dc2626;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      <?php echo htmlspecialchars($due_date); ?>
                    </p>
                  </td>
                </tr>
              </table>

              <!-- Fine warning banner -->
              <table role="presentation" border="0" cellpadding="0"
                     cellspacing="0" width="100%"
                     style="background:#fef2f2;border-left:4px solid #ef4444;
                            border-radius:0 8px 8px 0;margin-bottom:32px;">
                <tr>
                  <td style="padding:14px 18px;">
                    <p style="margin:0;font-size:13.5px;color:#7f1d1d;line-height:1.6;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      <strong>Late returns incur a fine of ₹10 per day.</strong>
                      Return the book by <strong><?php echo htmlspecialchars($due_date); ?></strong>
                      to avoid any charges.
                    </p>
                  </td>
                </tr>
              </table>

              <!-- Divider + footer note -->
              <table role="presentation" border="0" cellpadding="0"
                     cellspacing="0" width="100%">
                <tr>
                  <td style="border-top:1px solid #f1f5f9;padding-top:24px;">
                    <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.65;
                               font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                      Questions? Contact us at
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
            <td align="center"
                style="background:#0f172a;padding:24px 40px;">
              <p style="margin:0 0 6px;font-size:13px;font-weight:800;color:#ffffff;
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                Antigravity &mdash; Library Management System
              </p>
              <p style="margin:0 0 10px;font-size:11.5px;color:#64748b;
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                Aurora College &bull; Automated reminder — do not reply.
              </p>
              <p style="margin:0;font-size:11px;color:#334155;
                         font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                &copy; <?php echo date('Y'); ?> Antigravity Library. All rights reserved.
              </p>
            </td>
          </tr>

        </table>

        <p style="margin:20px 0 0;font-size:11px;color:#a3a3a3;text-align:center;
                   font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
          You received this reminder because you have an active loan at Antigravity Library.
        </p>

      </td>
    </tr>
  </table>

</body>
</html>
