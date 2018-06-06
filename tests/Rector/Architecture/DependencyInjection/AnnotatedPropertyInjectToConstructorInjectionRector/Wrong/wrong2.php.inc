<?php declare (strict_types=1);

class ClassWithInjects
{
    /**
     * @var stdClass
     * @autowired
     */
    protected $property;

    /**
     * @var DateTimeInterface
     * @inject
     */
    public $otherProperty;
}
