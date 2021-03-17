<?php
namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\Contract\Rector\PhpRectorInterface;

final class SkipRequiredByContract extends NodeVisitorAbstract implements PhpRectorInterface
{
    public function getNodeTypes(): array
    {
    }

    public function refactor(Node $node): ?Node
    {
    }
}
