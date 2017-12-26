<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;

final class StaticCallNameChanger
{
    public function renameNode(StaticCall $node, string $newMethodName): void
    {
        $node->name = new Identifier($newMethodName);
    }
}
