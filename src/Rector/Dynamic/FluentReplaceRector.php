<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class FluentReplaceRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
    }

    public function refactor(Node $node): Node
    {
    }
}
