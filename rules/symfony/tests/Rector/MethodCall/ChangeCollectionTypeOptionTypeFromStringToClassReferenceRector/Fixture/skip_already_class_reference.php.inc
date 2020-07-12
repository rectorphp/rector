<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector\Fixture;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SkipAlreadyClassReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'type' => CheckboxType::class,
        ]);
    }
}
