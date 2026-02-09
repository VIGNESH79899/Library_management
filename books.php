<?php
include "db.php";
$result = mysqli_query($conn,"SELECT * FROM Book");
?>

<h2>Books</h2>
<table border="1" cellpadding="8">
<tr>
<th>ID</th><th>Title</th><th>Author</th><th>Status</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>
<td><?= $row['Book_ID'] ?></td>
<td><?= $row['Title'] ?></td>
<td><?= $row['Author'] ?></td>
<td><?= $row['Status'] ?></td>
</tr>
<?php } ?>
</table>