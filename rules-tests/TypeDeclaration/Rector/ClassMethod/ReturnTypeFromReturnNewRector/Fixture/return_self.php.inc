<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

final class ReturnSelf
{
    public function run()
    {
        return new self();
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

final class ReturnSelf
{
    public function run(): self
    {
        return new self();
    }
}
?>
