<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipInIfIsArray2
{
    private $data;

    public function set(array $data)
    {
        $this->data = $data;
    }

    public function countables()
    {
        if (is_array($this->data)) {
            echo count($this->data);
        }
    }
}
