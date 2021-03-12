<?php

namespace Rector\Tests\Symfony5\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector\Fixture;

class SkipNotValidatorBuilder
{
    public function run()
    {
        $d = new \DateTime('now');
        $d->format('Y-m-d');
    }
}
