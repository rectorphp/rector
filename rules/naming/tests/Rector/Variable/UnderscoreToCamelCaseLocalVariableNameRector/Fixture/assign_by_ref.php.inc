<?php

$_a = 'a';
$_some_value = &$_a;
-----
<?php

$a = 'a';
$someValue = &$a;
