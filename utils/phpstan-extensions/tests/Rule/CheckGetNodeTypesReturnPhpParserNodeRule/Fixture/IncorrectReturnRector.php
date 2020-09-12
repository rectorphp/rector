<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\CheckGetNodeTypesReturnPhpParserNodeRule\Fixture;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PHPStanExtensions\Tests\Rule\CheckGetNodeTypesReturnPhpParserNodeRule\Source\ClassNotOfPhpParserNode;

class IncorrectReturnRector
{

    public function getNodeTypes(): array
    {
        return [ClassNotOfPhpParserNode::class, Class_::class];
    }
}
