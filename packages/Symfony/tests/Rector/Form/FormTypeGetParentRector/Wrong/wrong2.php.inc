<?php

use Rector\Symfony\Tests\Rector\Form\FormTypeGetParentRector\Source\AbstractTypeExtension;

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

use Rector\Symfony\Tests\Rector\Form\FormTypeGetParentRector\Source\AbstractTypeExtension;

class TypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return \Symfony\Component\Form\Extension\Core\Type\FormType::class;
    }
}

?>
