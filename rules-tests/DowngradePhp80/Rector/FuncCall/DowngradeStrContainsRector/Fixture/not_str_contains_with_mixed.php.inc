<?php

! str_contains($haystack, 'ab');
! str_contains('abc', $needle);

?>
-----
<?php

strpos($haystack, 'ab') === false;
strpos('abc', $needle) === false;

?>
