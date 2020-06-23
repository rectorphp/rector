<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\ComplextArray;

/**
 * @return Foo[][]
 */
function someFunction($value)
{
    return $value;
}

/**
 * @return real
 */
function someFunction2($value)
{
    return $value;
}

/**
 * @return Foo[]|iterable
 */
function someFunction3($value)
{
    return $value;
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\ComplextArray;

/**
 * @return Foo[][]
 */
function someFunction($value): array
{
    return $value;
}

function someFunction2($value): float
{
    return $value;
}

/**
 * @return Foo[]|iterable
 */
function someFunction3($value): iterable
{
    return $value;
}

?>
