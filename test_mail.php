<?php

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/emails/send_borrow_email.php';

$result = sendBorrowEmail(
    conn: $conn,
    member_db_id: 1,
    student_name: "Adhi Vignesh",
    student_email: "aadhevignesh65@gmail.com",
    book_title: "DBMS Concepts",
    due_date: "Mar 10, 2026",
    issue_date: "Feb 23, 2026",
    member_id: "ARI-0001"
);

var_dump($result);