<?php declare (strict_types=1);

class ClassWithInjects
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
