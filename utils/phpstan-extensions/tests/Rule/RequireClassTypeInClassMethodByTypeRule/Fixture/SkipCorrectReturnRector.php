<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\RequireClassTypeInClassMethodByTypeRule\Fixture;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\PHPStanExtensions\Tests\Rule\RequireClassTypeInClassMethodByTypeRule\Source\SomeType;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class SkipCorrectReturnRector implements PhpRectorInterface
{
    public function getNodeTypes(): array
    {
        return [String_::class];
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
