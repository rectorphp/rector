<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector\Fixture;

use Symfony\Component\Validator\ValidatorBuilder;

class SkipBoolArg
{
    public function run(ValidatorBuilder $builder)
    {
        $builder->enableAnnotationMapping(true);
    }
}
