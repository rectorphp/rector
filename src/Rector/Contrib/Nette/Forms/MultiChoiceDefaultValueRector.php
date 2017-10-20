<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Forms;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

/**
 * Before:
 *
 * After:
 */
final class MultiChoiceDefaultValueRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        dump($node);
        die;

        return true;
    }

    public function refactor(Node $node): ?Node
    {
        return $node;
    }
}
