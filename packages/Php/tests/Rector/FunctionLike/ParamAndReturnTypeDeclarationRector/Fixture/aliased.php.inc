<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture;

use stdClass as Another;

/**
 * @param Another $another
 */
function someFunction($another) {
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture;

use stdClass as Another;

/**
 * @param Another $another
 */
function someFunction(Another $another) {
}
?>
