<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector\Fixture;

use Symfony\Component\Validator\ValidatorBuilder;

class SkipNotReaderArg
{
    public function run(ValidatorBuilder $builder, $variable)
    {
        $builder->enableAnnotationMapping($variable);
    }
}
