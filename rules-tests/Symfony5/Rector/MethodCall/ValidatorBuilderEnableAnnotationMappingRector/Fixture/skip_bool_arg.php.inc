<?php

namespace Rector\Tests\Symfony5\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector\Fixture;

use Symfony\Component\Validator\ValidatorBuilder;

class SkipBoolArg
{
    public function run(ValidatorBuilder $builder)
    {
        $builder->enableAnnotationMapping(true);
    }
}
