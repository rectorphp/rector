<?php

declare(strict_types=1);

namespace Rector\Core\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;

abstract class AbstractMockeryRector extends AbstractPHPUnitRector
{
    protected function isCallToMockery(Node $node): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        return $this->isName($node->class, 'Mockery');
    }
}
