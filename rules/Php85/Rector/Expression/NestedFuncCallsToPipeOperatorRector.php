<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as unwrapping nested calls to |> pipes depends on the context of surrounding code. It can create extremely long chains that break readability, and where to draw the line is a personal preference.
 */
final class NestedFuncCallsToPipeOperatorRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface, DeprecatedInterface
{
    /**
     * @api
     * @var string
     */
    public const MINIMUM_DEPTH = 'minimum_depth';
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert multiple nested function calls in single line to |> pipe operator', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($input)
    {
        $result = trim(strtolower(htmlspecialchars($input)));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($input)
    {
        $result = $input
            |> htmlspecialchars(...)
            |> strtolower(...)
            |> trim(...);
    }
}
CODE_SAMPLE
, [self::MINIMUM_DEPTH => 3])]);
    }
    public function getNodeTypes(): array
    {
        return [Expression::class, Return_::class];
    }
    /**
     * @param Expression|Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as the pipe chain length that stays readable depends on the context of surrounding code', self::class));
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::PIPE_OPERATOER;
    }
}
