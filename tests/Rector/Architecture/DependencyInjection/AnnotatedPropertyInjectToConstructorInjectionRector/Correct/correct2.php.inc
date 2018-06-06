<?php declare (strict_types=1);

class ClassWithInjects
{
    /**
     * @var stdClass
     */
    private $property;

    /**
     * @var DateTimeInterface
     * @inject
     */
    public $otherProperty;
    public function __construct(\stdClass $property)
    {
        $this->property = $property;
    }
}
