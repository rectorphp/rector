<?php declare(strict_types=1);

namespace Roave\BetterReflection\NodeCompiler;

use PhpParser\Node;

function compileClassConstFetch(Node\Expr\ClassConstFetch $node)
{
    if ('class' === $node->name->toString()) {
        return 'className';
    }

    return $node->name->toString();
}
