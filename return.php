<?php
include "db.php";

if ($_POST) {
    mysqli_query($conn,"INSERT INTO Return_Book (Issue_ID,Return_Date,Fine_Amount)
    VALUES ($_POST[issue],CURDATE(),$_POST[fine])");

    mysqli_query($conn,"UPDATE Book SET Status='Available' WHERE Book_ID=$_POST[book]");
}
?>

<h2>Return Book</h2>
<form method="post">
Issue ID: <input name="issue"><br><br>
Book ID: <input name="book"><br><br>
Fine: <input name="fine"><br><br>
<button>Return</button>
</form>