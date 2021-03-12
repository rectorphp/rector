<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipByRefParam
{
    public function run()
    {
        $toInsert = [];
		$this->byRef($toInsert);
    }

    public function byRef(array & $data)
    {
    }
}

?>
