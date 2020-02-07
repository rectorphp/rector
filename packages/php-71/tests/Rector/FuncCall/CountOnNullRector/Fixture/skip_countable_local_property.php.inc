<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipCountableLocalProperty
{
    private $titles = [];

    public function getTitle()
    {
        count($this->titles);
    }
}
