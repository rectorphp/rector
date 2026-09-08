<?php

declare (strict_types=1);
namespace Rector\Unambiguous\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as breaking a fluent setter chain into standalone calls needs a more complex approach that depends on the use case - the safe transformation differs per method return semantics.
 */
final class FluentSettersToStandaloneCallMethodRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change fluent setter chain calls, to standalone line of setters', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return (new SomeFluentClass())
            ->setName('John')
            ->setAge(30);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $someFluentClass = new SomeFluentClass();
        $someFluentClass->setName('John');
        $someFluentClass->setAge(30);

        return $someFluentClass;
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
        return [Expression::class, Return_::class];
    }
    /**
     * @param Expression|Return_ $node
     */
    public function refactor(Node $node): ?array
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as breaking a fluent setter chain needs a more complex approach that depends on the use case', self::class));
    }
}
