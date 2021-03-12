<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector\Fixture;

use Symfony\Component\Validator\ValidatorBuilder;

class SkipNotEnableAnnotatinoMapping
{
    public function run(ValidatorBuilder $builder)
    {
        $builder->foo();
    }
}
