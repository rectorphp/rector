<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\TagType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder->add('tags', 'collection', [
            'entry_type' => new TagType()
        ]);
    }
}

?>
-----
<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\TagType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder->add('tags', 'collection', [
            'entry_type' => \Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\TagType::class
        ]);
    }
}

?>
