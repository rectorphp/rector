<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\SomeInterface;

final class ParentDefinition implements SomeInterface
{
    public function someMethod(): array
    {
        return ['test', 'test2'];
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\SomeInterface;

final class ParentDefinition implements SomeInterface
{
    /**
     * @return string[]
     */
    public function someMethod(): array
    {
        return ['test', 'test2'];
    }
}

?>
