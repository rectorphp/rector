<?php declare(strict_types=1);

namespace Rector\Contract\Rector;

use PhpParser\Node;
use PhpParser\NodeVisitor;

interface PhpRectorInterface extends NodeVisitor, RectorInterface
{
    /**
     * A node this Rector listens to
     *
     * @return string[]
     */
    public function getNodeTypes(): array;

    /**
     * Process Node of matched type
     */
    public function refactor(Node $node): ?Node;
}
