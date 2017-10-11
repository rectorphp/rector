<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

/**
 * @todo collect cases and prepare tests for them
 *
 * Possible options so far:
 * - new argument
 * - argument removed
 * - new default value for argument
 */
final class MethodArgumentChangerRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        return true;
    }

    public function refactor(Node $node): ?Node
    {
        return null;
    }
}
