<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector\Fixture;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Validator\ValidatorBuilder;

class Fixture
{
    public function run(ValidatorBuilder $builder, Reader $reader)
    {
        $builder->enableAnnotationMapping($reader);
    }
}
-----
<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector\Fixture;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Validator\ValidatorBuilder;

class Fixture
{
    public function run(ValidatorBuilder $builder, Reader $reader)
    {
        $builder->enableAnnotationMapping(true)->setDoctrineAnnotationReader($reader);
    }
}