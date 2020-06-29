<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Scalar\LNumber;

class SkipOverrideInOtherBranch
{
    public function run(string $serviceType)
    {
        if (Strings::contains($serviceType, '_') && ! Strings::contains($serviceType, '\\')) {
            $getArgumentValue = new String_($serviceType);
        }  else {
            $getArgumentValue = new LNumber(100);
        }

        return $getArgumentValue;
    }
}
