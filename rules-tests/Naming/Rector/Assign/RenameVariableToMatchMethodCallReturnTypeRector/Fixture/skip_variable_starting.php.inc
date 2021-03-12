<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use PhpParser\Node\Scalar\String_;

class SkipVariableStarting
{
    public function run()
    {
        $stringToTest = $this->getString();
    }

    public function getString(): String_
    {
        return new String_('someValue');
    }
}
