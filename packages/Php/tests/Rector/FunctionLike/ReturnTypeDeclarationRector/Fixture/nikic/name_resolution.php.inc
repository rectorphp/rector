<?php

namespace Foo;

use ABC\Baz;
use ABC\{XYZ};

/** @return \Bar */
function test1($value) {
    return $value;
}

/** @return Bar */
function test2($value) {
    return $value;
}

/** @return Bar\Baz */
function test3($value) {
    return $value;
}

/** @return Baz */
function test4($value) {
    return $value;
}

/** @return Baz\Foo */
function test5($value) {
    return $value;
}

/** @return XYZ */
function test6($value) {
    return $value;
}

?>
-----
<?php

namespace Foo;

use ABC\Baz;
use ABC\{XYZ};

/** @return \Bar */
function test1($value): \Bar {
    return $value;
}

/** @return Bar */
function test2($value): Bar {
    return $value;
}

/** @return Bar\Baz */
function test3($value): Bar\Baz {
    return $value;
}

/** @return Baz */
function test4($value): Baz {
    return $value;
}

/** @return Baz\Foo */
function test5($value): Baz\Foo {
    return $value;
}

/** @return XYZ */
function test6($value): XYZ {
    return $value;
}

?>
