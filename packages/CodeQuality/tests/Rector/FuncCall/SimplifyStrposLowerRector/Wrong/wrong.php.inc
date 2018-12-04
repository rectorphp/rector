<?php

$string = 'hey';
strpos(strtolower($string), 'find-me');

$funcName = 'strpos';
$funcName(strtolower($string), 'find-me');

?>
-----
<?php

$string = 'hey';
stripos($string, 'find-me');

$funcName = 'strpos';
$funcName(strtolower($string), 'find-me');

?>
