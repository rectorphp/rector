<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

use stdClass as Another;

/**
 * @param Another $another
 */
function someFunction($another) {
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

use stdClass as Another;

/**
 * @param Another $another
 */
function someFunction(Another $another) {
}
?>
