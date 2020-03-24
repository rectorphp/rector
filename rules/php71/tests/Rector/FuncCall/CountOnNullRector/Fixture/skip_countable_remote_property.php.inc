<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Source\ClassWithCountableProperty;

final class SkipCountableRemoteProperty
{
    private $remote;

    public function __construct()
    {
        $this->remote = new ClassWithCountableProperty();
    }

    public function getTitle()
    {
        count($this->remote->countable);
    }
}
