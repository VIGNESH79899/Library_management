<?php
include "db.php";
$data = mysqli_query($conn,"SELECT * FROM Publisher");
?>

<h2>Publishers</h2>
<ul>
<?php while($p = mysqli_fetch_assoc($data)) { ?>
<li><?= $p['Publisher_Name'] ?></li>
<?php } ?>
</ul>