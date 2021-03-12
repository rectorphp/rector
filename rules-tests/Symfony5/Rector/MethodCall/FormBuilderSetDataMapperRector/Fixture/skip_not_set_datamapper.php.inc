<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\FormBuilderSetDataMapperRector\Fixture;

use Symfony\Component\Form\FormConfigBuilderInterface;

class SkipNotSetDataMapper
{
    public function run(FormConfigBuilderInterface $builder)
    {
        $builder->foo();
    }
}
