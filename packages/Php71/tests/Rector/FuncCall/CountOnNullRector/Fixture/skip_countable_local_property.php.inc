<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipCountableTypeProperty
{
    private $titles = [];

    public function getTitle()
    {
        count($this->titles);
    }
}
