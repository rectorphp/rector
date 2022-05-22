<?php

namespace Rector\Tests\Naming\Naming\UseImportsResolver\Fixture;

use Rector\Tests\Naming\Naming\UseImportsResolver\Source\OtherClass;
use Rector\Tests\Naming\Naming\UseImportsResolver\Source\{FirstClass, SecondClass};

class MyGroupUse1
{
    private FirstClass $firstClass;
    private SecondClass $secondClass;
    private OtherClass $otherClass;
}
