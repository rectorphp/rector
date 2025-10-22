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
 * Fixer Rector for PHPStan rule:
 * https://github.com/phpstan/phpstan-strict-rules/blob/master/src/Rules/DisallowedConstructs/DisallowedShortTernaryRule.php
 *
 * @deprecated as risky and not practical
 */
final class DisallowedShortTernaryRuleFixerRector extends AbstractRector implements DeprecatedInterface
{
    public const TREAT_AS_NON_EMPTY = 'treat_as_non_empty';
    public function getRuleDefinition(): RuleDefinition
    {
        $errorMessage = sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\Rules\DisallowedConstructs\DisallowedShortTernaryRule');
        return new RuleDefinition($errorMessage, [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class ShortTernaryArray
{
    public function run(array $array)
    {
        return $array ?: 2;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class ShortTernaryArray
{
    public function run(array $array)
    {
        return $array !== [] ? $array : 2;
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
