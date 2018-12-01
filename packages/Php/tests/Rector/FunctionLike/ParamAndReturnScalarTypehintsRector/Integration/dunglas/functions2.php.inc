<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Dunglas;

/**
 * @param string|null $a
 *
 * @return int
 */
function test($a = null)
{
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Dunglas;

/**
 * @param string|null $a
 *
 * @return int
 */
function test(?string $a = null): int
{
}
?>
