<?php

declare(strict_types=1);

function simpleWithStrictTypes() {
    $baz = new \Foo\Bar;
}

?>
-----
<?php

declare(strict_types=1);

use Foo\Bar;

function simpleWithStrictTypes() {
    $baz = new Bar;
}

?>
