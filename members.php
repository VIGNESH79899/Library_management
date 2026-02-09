<?php
include "db.php";
$data = mysqli_query($conn,"SELECT * FROM Member");
?>

<h2>Members</h2>
<ul>
<?php while($m = mysqli_fetch_assoc($data)) { ?>
<li><?= $m['Member_Name'] ?></li>
<?php } ?>
</ul>