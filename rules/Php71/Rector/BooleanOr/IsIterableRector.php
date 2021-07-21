<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\BooleanOr;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Name;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php71\IsArrayAndDualCheckToAble;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Php71\Rector\BooleanOr\IsIterableRector\IsIterableRectorTest
 */
final class IsIterableRector extends AbstractRector
{
    public function __construct(
        private IsArrayAndDualCheckToAble $isArrayAndDualCheckToAble,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes is_array + Traversable check to is_iterable',
            [new CodeSample('is_array($foo) || $foo instanceof Traversable;', 'is_iterable($foo);')]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [BooleanOr::class];
    }

    /**
     * @param BooleanOr $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip()) {
            return null;
        }

        return $this->isArrayAndDualCheckToAble->processBooleanOr($node, 'Traversable', 'is_iterable') ?: $node;
    }

    private function shouldSkip(): bool
    {
        if ($this->reflectionProvider->hasFunction(new Name('is_iterable'), null)) {
            return false;
        }

        return ! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::IS_ITERABLE);
    }
}
