<?php

namespace Rector\Tests\Symfony5\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector\Fixture;

use Symfony\Component\PropertyAccess\PropertyAccessor;

class SkipAlreadyHasConstant
{
    public function createPropertyAccessor()
    {
        return new PropertyAccessor(PropertyAccessor::MAGIC_CALL);
    }
}
