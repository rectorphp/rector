<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

use Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Source\ExternalClass;

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
