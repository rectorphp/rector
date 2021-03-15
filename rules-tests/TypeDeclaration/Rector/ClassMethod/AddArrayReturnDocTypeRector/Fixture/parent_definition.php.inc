<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\SomeInterface;

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

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\SomeInterface;

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
