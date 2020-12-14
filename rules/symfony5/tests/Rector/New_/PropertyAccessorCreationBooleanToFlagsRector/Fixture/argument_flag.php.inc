<?php

namespace Rector\Symfony5\Tests\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector\Fixture;

use Symfony\Component\PropertyAccess\PropertyAccessor;

class ArgumentFlag
{
    public function createPropertyAccessor()
    {
        return new PropertyAccessor(PropertyAccessor::MAGIC_CALL);
    }
}

?>
