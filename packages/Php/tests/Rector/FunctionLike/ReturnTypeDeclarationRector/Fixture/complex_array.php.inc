<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\ComplextArray;

/**
 * @return Foo[][]
 */
function someFunction()
{
}

/**
 * @return real
 */
function someFunction2()
{
}

/**
 * @return Foo[]|iterable
 */
function someFunction3()
{
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\ComplextArray;

/**
 * @return Foo[][]
 */
function someFunction(): array
{
}

/**
 * @return real
 */
function someFunction2(): float
{
}

/**
 * @return Foo[]|iterable
 */
function someFunction3(): array
{
}

?>
