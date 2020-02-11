<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\BlacklistedClassMethods;

class Bar
{
}

class Foo
{
    /** @return Bar */
    function __construct() {}
    /** @return Bar */
    function __destruct() {}
    /** @return Bar */
    function __clone() {}
}
