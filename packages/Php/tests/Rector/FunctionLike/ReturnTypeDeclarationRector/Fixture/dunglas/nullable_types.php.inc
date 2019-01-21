<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Dunglas\NullableTypes;

/**
 * @return string|null
 */
function foo()
{
}

/**
 * @return bool[]|null
 */
function bazbaz()
{
}

/**
 * @param int|null $a
 */
function foobat(int $a = null)
{
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Dunglas\NullableTypes;

/**
 * @return string|null
 */
function foo(): ?string
{
}

/**
 * @return bool[]|null
 */
function bazbaz(): ?array
{
}

/**
 * @param int|null $a
 */
function foobat(int $a = null)
{
}

?>
