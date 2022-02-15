<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class SkipTypedInheritDocParent {
    /** @var string */
    public $property;
}

final class SkipTypedInheritDoc extends SkipTypedInheritDocParent {
    /** @inheritDoc */
    public $property = 'test';
}
?>
