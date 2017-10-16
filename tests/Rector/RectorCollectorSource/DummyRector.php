<?php declare(strict_types=1);

namespace Rector\Tests\Rector\RectorCollectorSource;

use PhpParser\Node;
use Rector\Contract\Rector\RectorInterface;

final class DummyRector implements RectorInterface
{
    public function isCandidate(Node $node): bool
    {
        // TODO: Implement isCandidate() method.
    }

    public function refactor(Node $node): ?Node
    {
        // TODO: Implement refactor() method.
    }
}
