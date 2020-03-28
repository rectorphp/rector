<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

class SkipExternalCall
{
    /**
     * @var ExternalClass
     */
    private $externalClass;

    public function __construct(ExternalClass $externalClass)
    {
        $this->externalClass = $externalClass;
    }

    /**
     * @api
     */
    public function run()
    {
        $this->externalClass->run();
    }
}

class ExternalClass
{
    public function run()
    {
    }
}
