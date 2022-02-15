<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

final class PrivateWithInheritDoc {
    /**
     * @inheritDoc
     */
    private $property = 'test';
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

final class PrivateWithInheritDoc {
    /**
     * @inheritDoc
     * @var string
     */
    private $property = 'test';
}
?>
