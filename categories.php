<?php
include "db.php";
$data = mysqli_query($conn,"SELECT * FROM Category");
?>

<h2>Categories</h2>
<ul>
<?php while($c = mysqli_fetch_assoc($data)) { ?>
<li><?= $c['Category_Name'] ?></li>
<?php } ?>
</ul>