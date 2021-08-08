<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class SkipModifiedValueProperty
{
    public $data;

    public function run(array $data)
    {
        if (! empty($this->data)) {
            return $this->data;
        }

        $this->data = $data;
        if (! empty($this->data)) {
            return $this->data;
        }
    }
}
