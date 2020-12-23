<?php

namespace Rector\SOLID\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipByRefParam
{
    public function byRef(array & $data)
    {
    }

    public function run()
    {
        $toInsert = [];
		$this->byRef($toInsert);
    }
}

?>
