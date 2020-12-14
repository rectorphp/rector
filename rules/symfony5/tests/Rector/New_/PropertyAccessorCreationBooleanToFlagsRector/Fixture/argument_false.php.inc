<?php

namespace Rector\Symfony5\Tests\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector\Fixture;

use Symfony\Component\PropertyAccess\PropertyAccessor;

class ArgumentFalse
{
    public function createPropertyAccessor()
    {
        return new PropertyAccessor(false);
    }
}

?>
-----
<?php

namespace Rector\Symfony5\Tests\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector\Fixture;

use Symfony\Component\PropertyAccess\PropertyAccessor;

class ArgumentFalse
{
    public function createPropertyAccessor()
    {
        return new PropertyAccessor(\Symfony\Component\PropertyAccess\PropertyAccessor::MAGIC_GET | \Symfony\Component\PropertyAccess\PropertyAccessor::MAGIC_SET);
    }
}

?>
