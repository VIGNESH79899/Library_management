<?php
include "db.php";
$data = mysqli_query($conn,"SELECT * FROM Librarian");
?>

<h2>Librarians</h2>
<ul>
<?php while($l = mysqli_fetch_assoc($data)) { ?>
<li><?= $l['Librarian_Name'] ?></li>
<?php } ?>
</ul>