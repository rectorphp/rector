<?php

namespace Rector\Tests\Symfony5\Rector\MethodCall\FormBuilderSetDataMapperRector\Fixture;

use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormConfigBuilderInterface;

final class SkipCorrectArg
{
    public function run(FormConfigBuilderInterface $builder)
    {
        $propertyPathAccessor = new \Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor();
        $builder->setDataMapper(new \Symfony\Component\Form\Extension\Core\DataMapper\DataMapper($propertyPathAccessor));
    }
}
