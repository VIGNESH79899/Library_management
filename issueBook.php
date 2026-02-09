<?php
include "db.php";

if ($_POST) {
    mysqli_query($conn,"INSERT INTO Issue (Book_ID,Member_ID,Librarian_ID,Issue_Date,Due_Date)
    VALUES ($_POST[book],$_POST[member],1,CURDATE(),DATE_ADD(CURDATE(),INTERVAL 7 DAY))");

    mysqli_query($conn,"UPDATE Book SET Status='Issued' WHERE Book_ID=$_POST[book]");
}
?>

<h2>Issue Book</h2>
<form method="post">
Book ID: <input name="book"><br><br>
Member ID: <input name="member"><br><br>
<button>Issue</button>
</form>