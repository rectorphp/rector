<?php declare (strict_types=1);

use Rector\Symfony\Tests\Rector\Form\FormTypeGetParentRector\Source\AbstractTypeExtension;

class TypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return 'form';
    }
}
