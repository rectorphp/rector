<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Dunglas;

/**
 * @param string $a
 * @param array $b
 * @param int $c
 * @return bool
 */
function pass_by_reference($a, &$b, int &$c)
{
}
$closure = function (&$a) {
};
function pass_by_reference_undocumented(&$a, $b = 1 & 1)
{
}
/**
 * @param string $a
 */
function not_by_reference($a)
{
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Dunglas;

/**
 * @param string $a
 * @param array $b
 * @param int $c
 * @return bool
 */
function pass_by_reference(string $a, array &$b, int &$c): bool
{
}
$closure = function (&$a) {
};
function pass_by_reference_undocumented(&$a, $b = 1 & 1)
{
}
/**
 * @param string $a
 */
function not_by_reference(string $a)
{
}
?>
