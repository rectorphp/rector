<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\Double;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Double;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\Renaming\Rector\Cast\RenameCastRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Use same but configurable `Rector\Renaming\Rector\Cast\RenameCastRector` with configuration instead
 */
final class RealToFloatTypeCastRector extends AbstractRector implements MinPhpVersionInterface, DeprecatedInterface
{
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATE_REAL;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change deprecated `(real)` to `(float)`', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $number = (real) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $number = (float) 5;
        $number = (float) 5;
        $number = (double) 5;
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
        return [Double::class];
    }
    /**
     * @param Double $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('This rule is deprecated. Use configurable "%s" rule instead.', RenameCastRector::class));
    }
}
