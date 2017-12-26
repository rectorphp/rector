<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;

final class ClassConstFetchNameChanger
{
    public function renameNode(ClassConstFetch $node, string $newMethodName): void
    {
        $node->name = new Identifier($newMethodName);
    }
}
