<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Dunglas;

/**
 * @return float[]
 */
function array_no_types() {
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Dunglas;

/**
 * @return float[]
 */
function array_no_types(): array {
}

?>
