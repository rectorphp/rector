<?php

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source\SomeChild;

class ActionClass
{
    /**
     * @var SomeChild|null
     */
    private $someChildValueObject;

    public function someFunction()
    {
        $this->someChildValueObject = new SomeChild('value');

        $someChildValueObject = new SomeChild();
    }
}
