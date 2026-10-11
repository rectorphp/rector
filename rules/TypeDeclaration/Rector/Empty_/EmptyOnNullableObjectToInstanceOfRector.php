<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Empty_;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated. Use ExplicitNullableObjectInstanceRector instead, which handles nullable object checks across all condition idioms.
 */
final class EmptyOnNullableObjectToInstanceOfRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `empty()` on nullable object to instanceof check', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(?AnotherObject $anotherObject)
    {
        if (empty($anotherObject)) {
            return false;
        }

        return true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(?AnotherObject $anotherObject)
    {
        if (! $anotherObject instanceof AnotherObject) {
            return false;
        }

        return true;
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
        return [Empty_::class, BooleanNot::class];
    }
    /**
     * @param Empty_|BooleanNot $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, use ExplicitNullableObjectInstanceRector instead', self::class));
    }
}
