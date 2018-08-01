<?php declare(strict_types=1);

namespace Rector\Contract\Rector;

use PhpParser\Node;
use PhpParser\NodeVisitor;

interface PhpRectorInterface extends NodeVisitor, RectorInterface
{
    public function isCandidate(Node $node): bool;

    public function refactor(Node $node): ?Node;
}
