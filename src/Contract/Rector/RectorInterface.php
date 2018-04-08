<?php declare(strict_types=1);

namespace Rector\Contract\Rector;

use PhpParser\Node;
use PhpParser\NodeVisitor;
use Rector\RectorDefinition\RectorDefinition;

interface RectorInterface extends NodeVisitor
{
    public function getDefinition(): RectorDefinition;

    public function isCandidate(Node $node): bool;

    public function refactor(Node $node): ?Node;
}
