<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\BooleanNot;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as risky and requires manual checking
 */
final class BooleanInBooleanNotRuleFixerRector extends AbstractRector implements DeprecatedInterface
{
    public const TREAT_AS_NON_EMPTY = 'treat_as_non_empty';
    public function getRuleDefinition(): RuleDefinition
    {
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\Rules\BooleansInConditions\BooleanInBooleanNotRule');
        return new RuleDefinition($errorMessage, [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string|null $name)
    {
        if (! $name) {
            return 'no name';
        }

        return 'name';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string|null $name)
    {
        if ($name === null) {
            return 'no name';
        }

        return 'name';
    }
}
CODE_SAMPLE
, [self::TREAT_AS_NON_EMPTY => \true])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [BooleanNot::class];
    }
    /**
     * @param BooleanNot $node
     */
    public function refactor(Node $node): ?Expr
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated as risky and not practical', self::class));
    }
}
