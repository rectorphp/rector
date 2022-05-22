<?php

namespace Rector\Tests\Naming\Naming\UseImportsResolver\Fixture;

# Valid import for reference
use Rector\Tests\Naming\Naming\UseImportsResolver\Source\OtherClass;
use Rector\Tests\Naming\Naming\UseImportsResolver\Source\{FirstClass as ClassA, SecondClass as ClassB};

class MyGroupUse2
{
    private ClassA $firstClass;
    private ClassB $secondClass;
    private OtherClass $otherClass;
}
