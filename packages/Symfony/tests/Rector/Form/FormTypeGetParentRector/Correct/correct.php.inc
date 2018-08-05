<?php declare (strict_types=1);

use Rector\Symfony\Tests\Rector\Form\FormTypeGetParentRector\Source\AbstractType;

class PermissionCollectionType extends AbstractType
{
    public function getName()
    {
        return 'some_name';
    }

    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
    }
}
