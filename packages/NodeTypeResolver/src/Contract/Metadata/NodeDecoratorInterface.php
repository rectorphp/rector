<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Contract\Metadata;

use PhpParser\Node;

interface NodeDecoratorInterface
{
    public function reset(): void;

    public function decorateNode(Node $node): void;
}
