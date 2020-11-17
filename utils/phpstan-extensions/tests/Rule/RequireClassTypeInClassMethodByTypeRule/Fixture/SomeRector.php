<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\RequireClassTypeInClassMethodByTypeRule\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class SomeRector implements PhpRectorInterface
{
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Class_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
    }

    public function beforeTraverse(array $nodes)
    {
    }

    public function enterNode(Node $node)
    {
    }

    public function leaveNode(Node $node)
    {
    }

    public function afterTraverse(array $nodes)
    {
    }

    public function refactor(Node $node): ?Node
    {
    }
}
