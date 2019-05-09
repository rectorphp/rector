<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Nikic\Nullable;

/**
 * @param Foo|null $a
 * @param Bar|null $b
 */
function test2($a, $b = null) {}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Nikic\Nullable;

/**
 * @param Foo|null $a
 * @param Bar|null $b
 */
function test2(?Foo $a, ?Bar $b = null) {}

?>
