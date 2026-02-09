<?php
include "db.php";

if ($_POST) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    mysqli_query($conn,"INSERT INTO Book (Title,Author) VALUES ('$title','$author')");
}
?>

<h2>Add Book</h2>
<form method="post">
Title: <input name="title"><br><br>
Author: <input name="author"><br><br>
<button>Add</button>
</form>