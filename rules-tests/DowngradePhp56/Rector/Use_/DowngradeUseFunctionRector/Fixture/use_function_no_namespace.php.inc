<?php

use function Foo\Bar\baz;

$var = baz();

?>
-----
<?php

$var = \Foo\Bar\baz();

?>
