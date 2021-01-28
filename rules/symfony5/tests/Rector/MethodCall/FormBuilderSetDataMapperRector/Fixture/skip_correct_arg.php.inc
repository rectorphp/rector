<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\FormBuilderSetDataMapperRector\Fixture;

use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormConfigBuilderInterface;

class SkipCorrectArg
{
    public function run(FormConfigBuilderInterface $builder)
    {
        $propertyPathAccessor = new \Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor();
        $builder->setDataMapper(new \Symfony\Component\Form\Extension\Core\DataMapper\DataMapper($propertyPathAccessor));
    }
}