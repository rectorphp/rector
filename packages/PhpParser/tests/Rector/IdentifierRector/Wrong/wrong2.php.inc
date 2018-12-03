<?php

namespace Roave\BetterReflection\NodeCompiler;

use PhpParser\Node;

function compileClassConstFetch(Node\Expr\ClassConstFetch $node)
{
    if ('class' === $node->name) {
        return 'className';
    }

    return $node->name->toString();
}

?>
-----
<?php

namespace Roave\BetterReflection\NodeCompiler;

use PhpParser\Node;

function compileClassConstFetch(Node\Expr\ClassConstFetch $node)
{
    if ('class' === $node->name->toString()) {
        return 'className';
    }

    return $node->name->toString();
}

?>
