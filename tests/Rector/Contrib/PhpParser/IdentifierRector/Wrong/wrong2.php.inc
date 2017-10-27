<?php declare(strict_types=1);

function compileClassConstFetch(\PhpParser\Node\Expr\ClassConstFetch $node)
{
    if ('class' === $node->name) {
        return 'className';
    }
}
