<?php

namespace Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverRector;

use Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverRector\Source\SomeChildOfValueObject;

class ActionClass
{
    /**
     * @var SomeChildOfValueObject|null
     */
    private $someChildValueObject;

    public function someFunction()
    {
        $this->someChildValueObject = 'value';

        $someChildValueObject = new SomeChildOfValueObject();
    }
}
