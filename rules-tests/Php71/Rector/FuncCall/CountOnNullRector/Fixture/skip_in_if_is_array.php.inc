<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipInIfIsArray
{
    private $data;

    function countables()
    {
        $this->data = rand(0, 1)
            ? []
            : null;

        if (is_array($this->data)) {
            echo count($this->data);
        }
    }
}
