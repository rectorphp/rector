<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class SkipUntypedInheritDocParent {
    public $property;
}

final class SkipUntypedInheritDoc extends SkipUntypedInheritDocParent {
    /** @inheritDoc */
    public $property = 'test';
}
?>
