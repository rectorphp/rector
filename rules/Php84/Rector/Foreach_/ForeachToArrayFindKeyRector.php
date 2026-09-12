<?php

declare (strict_types=1);
namespace Rector\Php84\Rector\Foreach_;

use PhpParser\Node;
use Rector\Php84\NodeFactory\ForeachToArrayFindFactory;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\ValueObject\PolyfillPackage;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\Contract\RelatedPolyfillInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php84\Rector\Foreach_\ForeachToArrayFindKeyRector\ForeachToArrayFindKeyRectorTest
 */
final class ForeachToArrayFindKeyRector extends AbstractRector implements MinPhpVersionInterface, RelatedPolyfillInterface
{
    /**
     * @readonly
     */
    private ForeachToArrayFindFactory $foreachToArrayFindFactory;
    public function __construct(ForeachToArrayFindFactory $foreachToArrayFindFactory)
    {
        $this->foreachToArrayFindFactory = $foreachToArrayFindFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace foreach with assignment and break with array_find_key', [new CodeSample(<<<'CODE_SAMPLE'
$animals = ['dog', 'cat', 'cow', 'duck', 'goose'];

$found = null;
foreach ($animals as $idx => $animal) {
    if (str_starts_with($animal, 'c')) {
        $found = $idx;
        break;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$animals = ['dog', 'cat', 'cow', 'duck', 'goose'];

$found = array_find_key($animals, fn($animal) => str_starts_with($animal, 'c'));
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
        return $this->foreachToArrayFindFactory->createArrayFindAssign($node, 'array_find_key', \true);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ARRAY_FIND_KEY;
    }
    public function providePolyfillPackage(): string
    {
        return PolyfillPackage::PHP_84;
    }
}
