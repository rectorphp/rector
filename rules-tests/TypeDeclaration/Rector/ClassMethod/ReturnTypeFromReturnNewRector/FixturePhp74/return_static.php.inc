<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\FixturePhp74;

final class ReturnStatic
{
    public function run()
    {
        return new static();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\FixturePhp74;

final class ReturnStatic
{
    public function run(): self
    {
        return new static();
    }
}

?>
