<?php

declare(strict_types=1);

namespace Rector\Core\Contract\Rector;

use PhpParser\Node;
use PhpParser\NodeVisitor;

interface PhpRectorInterface extends NodeVisitor, RectorInterface
{
    /**
     * List of nodes this class checks, classes that implements \PhpParser\Node
     * See beautiful map of all nodes https://github.com/rectorphp/rector/blob/master/docs/nodes_overview.md
     *
     * @return class-string[]
     */
    public function getNodeTypes(): array;

    /**
     * Process Node of matched type
     */
    public function refactor(Node $node): ?Node;
}
