<?php declare (strict_types=1);

use Symfony\Component\Form\AbstractTypeExtension;

class TypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return 'form';
    }
}
