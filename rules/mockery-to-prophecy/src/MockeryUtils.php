<?php

declare(strict_types=1);

namespace Rector\MockeryToProphecy;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;

trait MockeryUtils
{
    private function isCallToMockery(Node $node)
    {
        return $node instanceof StaticCall && $node->class->toString() === 'Mockery';
    }
}
