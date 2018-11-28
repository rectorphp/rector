<?php

namespace Foo;

use ABC\Baz;
use ABC\{XYZ};

/** @return \Bar */
function test1() {}

/** @return Bar */
function test2() {}

/** @return Bar\Baz */
function test3() {}

/** @return Baz */
function test4() {}

/** @return Baz\Foo */
function test5() {}

/** @return XYZ */
function test6() {}

?>
-----
<?php

namespace Foo;

use ABC\Baz;
use ABC\{XYZ};

/** @return \Bar */
function test1(): \Bar {}

/** @return Bar */
function test2(): Bar {}

/** @return Bar\Baz */
function test3(): Bar\Baz {}

/** @return Baz */
function test4(): Baz {}

/** @return Baz\Foo */
function test5(): Baz\Foo {}

/** @return XYZ */
function test6(): XYZ {}

?>
