<?php

use Rector\NodeTypeResolver\Tests\Source\AnotherClass;

class ChildClass extends AnotherClass
{
    public function getParameters()
    {
        parent::getParameters();
    }
}
