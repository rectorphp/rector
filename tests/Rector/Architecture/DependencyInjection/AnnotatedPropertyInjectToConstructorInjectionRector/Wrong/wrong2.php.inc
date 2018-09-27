<?php declare (strict_types=1);

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
