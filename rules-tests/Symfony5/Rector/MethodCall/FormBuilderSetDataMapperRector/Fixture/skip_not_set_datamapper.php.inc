<?php

namespace Rector\Tests\Symfony5\Rector\MethodCall\FormBuilderSetDataMapperRector\Fixture;

use Symfony\Component\Form\FormConfigBuilderInterface;

class SkipNotSetDataMapper
{
    public function run(FormConfigBuilderInterface $builder)
    {
        $builder->foo();
    }
}
