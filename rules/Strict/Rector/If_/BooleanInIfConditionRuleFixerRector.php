<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated this rule is risky and requires manual checking
 */
final class BooleanInIfConditionRuleFixerRector extends AbstractRector implements DeprecatedInterface
{
    public const TREAT_AS_NON_EMPTY = 'treat_as_non_empty';
    public function getRuleDefinition(): RuleDefinition
    {
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\Rules\BooleansInConditions\BooleanInIfConditionRule');
        return new RuleDefinition($errorMessage, [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class NegatedString
{
    public function run(string $name)
    {
        if ($name) {
            return 'name';
        }

        return 'no name';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class NegatedString
{
    public function run(string $name)
    {
        if ($name !== '') {
            return 'name';
        }

        return 'no name';
    }
}
CODE_SAMPLE
, [self::TREAT_AS_NON_EMPTY => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?If_
    {
        throw new ShouldNotHappenException('This rule is deprecated and will be removed as risky and requires manual checking');
    }
}
