<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Nikic\Nullable;

/** @return Foo|null */
function test() {}

/**
 * @param Foo|null $a
 * @param Bar|null $b
 */
function test2($a, $b = null) {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Nikic\Nullable;

/** @return Foo|null */
function test(): ?Foo {}

/**
 * @param Foo|null $a
 * @param Bar|null $b
 */
function test2(?Foo $a, ?Bar $b = null) {}

?>
