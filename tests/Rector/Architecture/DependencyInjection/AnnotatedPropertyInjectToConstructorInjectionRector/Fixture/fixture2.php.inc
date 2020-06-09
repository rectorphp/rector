<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use DateTimeInterface;
use stdClass;

class ClassWithInjects2
{
    /**
     * @var stdClass
     * @autowired
     */
    public $property;

    /**
     * @var DateTimeInterface
     * @inject
     */
    protected $otherProperty;
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use DateTimeInterface;
use stdClass;

class ClassWithInjects2
{
    /**
     * @var stdClass
     * @autowired
     */
    public $property;

    /**
     * @var DateTimeInterface
     */
    private $otherProperty;
    public function __construct(\DateTimeInterface $otherProperty)
    {
        $this->otherProperty = $otherProperty;
    }
}

?>
