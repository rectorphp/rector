<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipCountableLocalProperty
{
    private $titles = [];

    public function getTitle()
    {
        count($this->titles);
    }
}
