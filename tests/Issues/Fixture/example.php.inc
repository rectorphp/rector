<?php
$dt = new DateTime();
$dt->modify('+1 day');
?>
-----
<?php
$dt = new DateTime();
$dt = $dt->modify('+1 day');
?>
