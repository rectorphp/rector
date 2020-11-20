<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureDocBlock;

class SkipAlreadyClassNameInVarDoc
{
    /**
     * @var \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SkipAlreadyClassNameInVarDoc
     */
    public $some;
}
