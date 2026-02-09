<?php
include "db.php";
?>

<h2>Reports</h2>
<p>Total Books: <?= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM Book"))['t']; ?></p>
<p>Total Members: <?= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM Member"))['t']; ?></p>