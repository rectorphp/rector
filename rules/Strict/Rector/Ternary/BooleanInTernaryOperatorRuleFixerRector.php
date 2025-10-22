<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as risky and requires manual checking
 */
final class BooleanInTernaryOperatorRuleFixerRector extends AbstractRector implements DeprecatedInterface
{
    public const TREAT_AS_NON_EMPTY = 'treat_as_non_empty';
    public function getRuleDefinition(): RuleDefinition
    {
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\Rules\BooleansInConditions\BooleanInTernaryOperatorRule');
        return new RuleDefinition($errorMessage, [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class ArrayCompare
{
    public function run(array $data)
    {
        return $data ? 1 : 2;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class ArrayCompare
{
    public function run(array $data)
    {
        return $data !== [] ? 1 : 2;
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
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Ternary
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated as risky and not practical', self::class));
    }
}
