<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector\Fixture;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'type' => 'choice',
        ]);

        $builder->add('tags', 'collection', [
            'type' => 'choice',
        ]);
    }
}

?>
-----
<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector\Fixture;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
        ]);

        $builder->add('tags', 'collection', [
            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
        ]);
    }
}

?>
