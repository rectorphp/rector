<?php declare(strict_types=1);

namespace Rector\RectorBuilder;

use PhpParser\Node;
use Rector\Contract\Rector\RectorInterface;

final class CaseRector implements RectorInterface
{
    public function __construct()
    {
    }

    public function isCandidate(Node $node): bool
    {
        // TODO: Implement isCandidate() method.
    }

    public function refactor(Node $node): ?Node
    {
        // TODO: Implement refactor() method.
    }
}
