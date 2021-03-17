<?php

namespace Rector\Tests\Symfony3\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector\Fixture;

use Symfony\Component\Form\AbstractType;

class DoubleNameType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'double_name';
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony3\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector\Fixture;

use Symfony\Component\Form\AbstractType;

class DoubleNameType extends AbstractType
{
}

?>
