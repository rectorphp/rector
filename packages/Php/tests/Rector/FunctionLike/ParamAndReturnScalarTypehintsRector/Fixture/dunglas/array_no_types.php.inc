<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Dunglas;

# Typed arrays get simply converted to array
/**
 * @param int[] $ints
 * @param string[] $strings
 * @param Foo[] $someClasses
 * @return float[]
 */
function array_no_types($ints, $strings, $someClasses) {
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Dunglas;

# Typed arrays get simply converted to array
/**
 * @param int[] $ints
 * @param string[] $strings
 * @param Foo[] $someClasses
 * @return float[]
 */
function array_no_types(array $ints, array $strings, array $someClasses): array {
}

?>
