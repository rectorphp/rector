<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerReturn\BlacklistedClassMethods;

class Foo
{
    /** @return Bar */
    function __construct() {}
    /** @return Bar */
    function __destruct() {}
    /** @return Bar */
    function __clone() {}
}

/** @return Bar */
function __construct() {}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\PhpCsFixerReturn\BlacklistedClassMethods;

class Foo
{
    /** @return Bar */
    function __construct() {}
    /** @return Bar */
    function __destruct() {}
    /** @return Bar */
    function __clone() {}
}

/** @return Bar */
function __construct(): Bar {}
?>
