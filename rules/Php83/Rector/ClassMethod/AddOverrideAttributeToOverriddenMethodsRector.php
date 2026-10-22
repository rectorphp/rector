<?php

declare (strict_types=1);
namespace Rector\Php83\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @deprecated This rule is deprecated, as never used and not part of any set. Adding the #[\Override] attribute requires careful per-project context, so apply it explicitly on the methods that override, based on your own requirements.
 */
final class AddOverrideAttributeToOverriddenMethodsRector extends AbstractRector implements MinPhpVersionInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add override attribute to overridden methods', [new CodeSample(<<<'CODE_SAMPLE'
class ParentClass
{
    public function foo()
    {
        echo 'default';
    }
}

final class ChildClass extends ParentClass
{
    public function foo()
    {
        echo 'override default';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class ParentClass
{
    public function foo()
    {
        echo 'default';
    }
}

final class ChildClass extends ParentClass
{
    #[\Override]
    public function foo()
    {
        echo 'override default';
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as never used and not part of any set. Apply the #[\Override] attribute explicitly on the methods that override, based on your own requirements', self::class));
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::OVERRIDE_ATTRIBUTE;
    }
}
