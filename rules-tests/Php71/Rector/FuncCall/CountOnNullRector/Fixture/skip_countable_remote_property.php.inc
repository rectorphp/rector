<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

use Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Source\ClassWithCountableProperty;

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
