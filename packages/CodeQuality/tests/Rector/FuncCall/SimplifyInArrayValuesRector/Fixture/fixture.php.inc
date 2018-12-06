<?php

$array = [];
in_array('key', array_values($array), true);

?>
-----
<?php

$array = [];
in_array('key', $array, true);

?>
