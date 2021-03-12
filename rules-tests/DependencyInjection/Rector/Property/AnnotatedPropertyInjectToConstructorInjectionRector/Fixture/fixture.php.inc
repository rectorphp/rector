<?php

namespace Rector\Tests\DependencyInjection\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use stdClass;
use DateTimeInterface;

class ClassWithInjects
{
    /**
     * @var stdClass
     * @inject
     */
    public $property;

    /**
     * @var DateTimeInterface
     * @inject
     */
    public $otherProperty;
}

?>
-----
<?php

namespace Rector\Tests\DependencyInjection\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use stdClass;
use DateTimeInterface;

class ClassWithInjects
{
    public function __construct(private \stdClass $property, private \DateTimeInterface $otherProperty)
    {
    }
}

?>
