<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

class SkipInterfaceImplemented
{
    /**
     * @var SomeInterface
     */
    private $classImplementing;

    public function __construct(SomeInterface $classImplementing)
    {
        $this->classImplementing = $classImplementing;
    }

    /**
     * @api
     */
    public function run()
    {
        $this->classImplementing->run();
    }
}

class ClassImplementingInterface implements SomeInterface
{
    public function run()
    {
    }
}

interface SomeInterface
{
    public function run();
}
