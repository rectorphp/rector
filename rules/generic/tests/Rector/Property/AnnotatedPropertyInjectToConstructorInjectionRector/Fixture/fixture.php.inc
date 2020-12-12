<?php

namespace Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use stdClass;
use DateTimeInterface;

class Fixture
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

namespace Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use stdClass;
use DateTimeInterface;

class Fixture
{
    /**
     * @var stdClass
     */
    private $property;

    /**
     * @var DateTimeInterface
     */
    private $otherProperty;
    public function __construct(\stdClass $property, \DateTimeInterface $otherProperty)
    {
        $this->property = $property;
        $this->otherProperty = $otherProperty;
    }
}

?>
