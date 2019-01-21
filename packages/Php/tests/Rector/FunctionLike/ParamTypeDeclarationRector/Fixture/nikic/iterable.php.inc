<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Iterable;

/** @param \Traversable|array $a */
function test($a) {}

/** @param array|\Traversable|null $a */
function test2($a) {}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Iterable;

/** @param \Traversable|array $a */
function test(iterable $a) {}

/** @param array|\Traversable|null $a */
function test2(?iterable $a) {}
?>
