<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\CheckGetNodeTypesReturnPhpParserNodeRule\Fixture;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

class SomeRector
{

    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Class_::class];
    }
}
