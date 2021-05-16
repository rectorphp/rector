<?php

$dateTime = new DateTime();
$dateTime->modify('+1');

?>
-----
<?php

$dateTime = new DateTime();
$dateTime = $dateTime->modify('+1');

?>
