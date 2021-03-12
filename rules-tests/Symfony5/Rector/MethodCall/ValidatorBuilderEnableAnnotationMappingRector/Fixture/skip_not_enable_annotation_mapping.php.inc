<?php

namespace Rector\Tests\Symfony5\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector\Fixture;

use Symfony\Component\Validator\ValidatorBuilder;

class SkipNotEnableAnnotatinoMapping
{
    public function run(ValidatorBuilder $builder)
    {
        $builder->foo();
    }
}
