<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector\Fixture;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EntryOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'options' => [],
        ]);
    }
}

?>
-----
<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector\Fixture;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EntryOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'entry_options' => [],
        ]);
    }
}

?>
