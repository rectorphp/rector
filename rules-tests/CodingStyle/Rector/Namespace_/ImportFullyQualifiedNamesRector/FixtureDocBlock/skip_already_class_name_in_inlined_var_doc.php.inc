<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureDocBlock;

class SkipAlreadyClassNameInInlinedVarDoc
{
    public function run($anotherVariable)
    {
        /** @var \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SkipAlreadyClassNameInInlinedVarDoc $typedVariable */
        $typedVariable = $anotherVariable;
    }
}
