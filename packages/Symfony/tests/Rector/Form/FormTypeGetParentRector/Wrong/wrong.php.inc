<?php

use Rector\Symfony\Tests\Rector\Form\FormTypeGetParentRector\Source\AbstractType;

class PermissionCollectionType extends AbstractType
{
    public function getName()
    {
        return 'some_name';
    }

    public function getParent()
    {
        return 'collection';
    }
}

?>
-----
<?php

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

?>
