<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

class SkipExternalFetch
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
        $this->externalClass->externalProperty;
    }
}

class ExternalClass
{
    public $externalProperty;
}
