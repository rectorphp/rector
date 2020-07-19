<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

use Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Source\FastRunner;

class SkipDynamicName
{
    public function run($name)
    {
        /** @var FastRunner $a */
        $a = $this->{$name->callThat}()();
    }
}
