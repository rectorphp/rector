<?php declare(strict_types=1);

namespace Rector\BetterReflection\NodeCompiler;

use PhpParser\Node;

function compileClassConstFetch(Node\Expr\ClassConstFetch $node)
{
    if ('class' === $node->name) {
        return 'className';
    }
    return $node->name->toString();
}
