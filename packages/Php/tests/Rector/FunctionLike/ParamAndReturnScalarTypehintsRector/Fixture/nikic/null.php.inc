<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Nikic\Null_;

/** @return null */
function foo() {
    return null;
}

/** @param null $a */
function foo2($a) {}

/** @param null|A $a */
function foo3($a = null) {}

/** @param null|A $a */
function foo4($a) {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Nikic\Null_;

/** @return null */
function foo() {
    return null;
}

/** @param null $a */
function foo2($a) {}

/** @param null|A $a */
function foo3(?A $a = null) {}

/** @param null|A $a */
function foo4(?A $a) {}

?>
