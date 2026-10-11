<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\While_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\While_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated. Use ExplicitNullableObjectInstanceRector instead, which handles nullable object checks across all condition idioms.
 */
final class WhileNullableToInstanceofRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change while null compare to strict instanceof check', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(?SomeClass $someClass)
    {
        while ($someClass !== null) {
            // do something
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(?SomeClass $someClass)
    {
        while ($someClass instanceof SomeClass) {
            // do something
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [While_::class, Do_::class];
    }
    /**
     * @param While_|Do_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, use ExplicitNullableObjectInstanceRector instead', self::class));
    }
}
