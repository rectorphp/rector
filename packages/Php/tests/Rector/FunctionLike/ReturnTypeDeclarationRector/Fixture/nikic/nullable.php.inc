<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Nikic\Nullable;

/** @return Foo|null */
function test() {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Nikic\Nullable;

/** @return Foo|null */
function test(): ?Foo {}

?>
