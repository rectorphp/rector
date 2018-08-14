<?php

namespace Rector\Tests\Rector\DomainDrivenDesign\ValueObjectRemoverRector;

use Rector\Tests\Rector\DomainDrivenDesign\ValueObjectRemoverRector\Source\SomeChildOfValueObject;

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
