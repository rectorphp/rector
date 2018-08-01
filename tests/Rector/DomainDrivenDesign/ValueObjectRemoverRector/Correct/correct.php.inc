<?php

namespace SomeNamespace;

use Rector\Tests\Rector\DomainDrivenDesign\ValueObjectRemoverRector\Source\SomeChildOfValueObject;

class ActionClass
{
    /**
     * @var string|null
     */
    private $someChildValueObject;

    public function someFunction()
    {
        $this->someChildValueObject = 'value';

        $someChildValueObject = new SomeChildOfValueObject();
    }
}
