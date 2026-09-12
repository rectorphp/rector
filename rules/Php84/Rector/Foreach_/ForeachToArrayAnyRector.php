<?php

declare (strict_types=1);
namespace Rector\Php84\Rector\Foreach_;

use PhpParser\Node;
use Rector\Php84\NodeFactory\ForeachToArrayAnyAllFactory;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\ValueObject\PolyfillPackage;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\Contract\RelatedPolyfillInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php84\Rector\Foreach_\ForeachToArrayAnyRector\ForeachToArrayAnyRectorTest
 */
final class ForeachToArrayAnyRector extends AbstractRector implements MinPhpVersionInterface, RelatedPolyfillInterface
{
    /**
     * @readonly
     */
    private ForeachToArrayAnyAllFactory $foreachToArrayAnyAllFactory;
    public function __construct(ForeachToArrayAnyAllFactory $foreachToArrayAnyAllFactory)
    {
        $this->foreachToArrayAnyAllFactory = $foreachToArrayAnyAllFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace foreach with boolean assignment + break OR foreach with early return with array_any', [new CodeSample(<<<'CODE_SAMPLE'
$found = false;
foreach ($animals as $animal) {
    if (str_starts_with($animal, 'c')) {
        $found = true;
        break;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$found = array_any($animals, fn($animal) => str_starts_with($animal, 'c'));
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
foreach ($animals as $animal) {
    if (str_starts_with($animal, 'c')) {
        return true;
    }
}
return false;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return array_any($animals, fn($animal) => str_starts_with($animal, 'c'));
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->foreachToArrayAnyAllFactory->refactorToArrayAnyAll($node, 'array_any', \false, \false, \true);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ARRAY_ANY;
    }
    public function providePolyfillPackage(): string
    {
        return PolyfillPackage::PHP_84;
    }
}
