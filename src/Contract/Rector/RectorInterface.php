<?php declare(strict_types=1);

namespace Rector\Contract\Rector;

use PhpParser\Node;
use PhpParser\NodeVisitor;

interface RectorInterface extends NodeVisitor
{
    public function isCandidate(Node $node): bool;

    public function refactor(Node $node): ?Node;
}
