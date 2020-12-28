<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector\Fixture;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SkipAnotherTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', 'text', [
            'type' => 'choice',
        ]);
    }
}
