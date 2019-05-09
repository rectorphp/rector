<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\InvalidClass;

/** @return \9 */ function my_foo($value)
{
    return $value;
}

/** @return \\Foo\\\\Bar */ function my_foo2($value)
{
    return $value;
}
