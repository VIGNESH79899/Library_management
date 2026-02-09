<?php
include "db.php";
$data = mysqli_query($conn,"SELECT * FROM Issue");
?>

<h2>Issued Books</h2>
<table border="1">
<tr><th>ID</th><th>Book</th><th>Member</th><th>Due</th></tr>
<?php while($i = mysqli_fetch_assoc($data)) { ?>
<tr>
<td><?= $i['Issue_ID'] ?></td>
<td><?= $i['Book_ID'] ?></td>
<td><?= $i['Member_ID'] ?></td>
<td><?= $i['Due_Date'] ?></td>
</tr>
<?php } ?>
</table>