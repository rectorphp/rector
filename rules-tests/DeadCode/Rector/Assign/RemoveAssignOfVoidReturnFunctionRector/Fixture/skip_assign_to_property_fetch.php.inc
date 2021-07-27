<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

class SkipAssignToPropertyFetch
{
    public $value = 'value';

    public function run()
    {
        $this->value = $this->getOne();
    }

    private function getOne(): void
    {
    }
}

?>
