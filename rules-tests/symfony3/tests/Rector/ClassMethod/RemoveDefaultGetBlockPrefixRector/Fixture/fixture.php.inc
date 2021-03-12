<?php

namespace Rector\Symfony3\Tests\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector\Fixture;

use Symfony\Component\Form\AbstractType;

class TaskType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'task';
    }
}

?>
-----
<?php

namespace Rector\Symfony3\Tests\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector\Fixture;

use Symfony\Component\Form\AbstractType;

class TaskType extends AbstractType
{
}

?>
