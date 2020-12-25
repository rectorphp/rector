<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

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
