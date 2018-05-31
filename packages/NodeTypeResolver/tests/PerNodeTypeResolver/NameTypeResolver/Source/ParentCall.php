<?php

use Rector\NodeTypeResolver\Tests\Source\AnotherClass;

class ParentCall extends AnotherClass
{
    public function getParameters()
    {
        parent::getParameters();
    }
}
