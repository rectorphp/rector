<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\FastRunner;

class SkipDynamicName
{
    public function run($name)
    {
        /** @var FastRunner $a */
        $a = $this->{$name->callThat}()();
    }
}
