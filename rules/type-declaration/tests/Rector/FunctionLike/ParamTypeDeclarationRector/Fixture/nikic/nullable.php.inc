<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Nikic\Nullable;

class Foo
{
}

class Bar
{
}

/**
 * @param Foo|null $a
 * @param Bar|null $b
 */
function test2($a, $b = null) {}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Nikic\Nullable;

class Foo
{
}

class Bar
{
}

/**
 * @param Foo|null $a
 * @param Bar|null $b
 */
function test2(?\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Nikic\Nullable\Foo $a, ?\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Nikic\Nullable\Bar $b = null) {}

?>
