<?php

use Foo\Bar;

function importFromThisFunctionToo() {
    $baz = new \Foo\Bar;
}

?>
-----
<?php

use Foo\Bar;

function importFromThisFunctionToo() {
    $baz = new Bar;
}

?>
