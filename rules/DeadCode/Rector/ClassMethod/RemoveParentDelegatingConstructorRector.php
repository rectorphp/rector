<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as removing a delegating constructor is risky and such constructors are nearly always kept on purpose.
 */
final class RemoveParentDelegatingConstructorRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove constructor that only delegates call to parent class with same values', [new CodeSample(<<<'CODE_SAMPLE'
class Node
{
    public function __construct(array $attributes)
    {
    }
}

class SomeParent extends Node
{
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class Node
{
    public function __construct(array $attributes)
    {
    }
}

class SomeParent extends Node
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as removing a delegating constructor is risky and such constructors are nearly always kept on purpose', self::class));
    }
}
