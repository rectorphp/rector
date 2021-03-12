<?php

namespace Rector\Symfony3\Tests\Rector\ClassMethod\FormTypeGetParentRector\Fixture;

use Symfony\Component\Form\AbstractTypeExtension;

class TypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return 'form';
    }
}

?>
-----
<?php

namespace Rector\Symfony3\Tests\Rector\ClassMethod\FormTypeGetParentRector\Fixture;

use Symfony\Component\Form\AbstractTypeExtension;

class TypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return \Symfony\Component\Form\Extension\Core\Type\FormType::class;
    }
}

?>
