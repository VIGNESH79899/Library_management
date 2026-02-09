<?php
session_start();
include "db.php";
if (!isset($_SESSION["admin"])) header("Location: login.php");

$books = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM Book"))['t'];
$members = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM Member"))['t'];
$issued = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM Book WHERE Status='Issued'"))['t'];
$fines = mysqli_fetch_assoc(mysqli_query($conn,"SELECT IFNULL(SUM(Fine_Amount),0) t FROM Return_Book"))['t'];
?>

<h1>Dashboard</h1>
<ul>
<li>Total Books: <?= $books ?></li>
<li>Total Members: <?= $members ?></li>
<li>Issued Books: <?= $issued ?></li>
<li>Total Fines: ₹<?= $fines ?></li>
</ul>