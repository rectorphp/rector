<?php

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\FastRunner;

class SkipDynamicName
{
    public function run($name)
    {
        /** @var FastRunner $a */
        $a = $this->{$name->callThat}()();
    }
}
