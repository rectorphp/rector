<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\List_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog http://php.net/manual/en/migration70.incompatible.php#migration70.incompatible.variable-handling.list
 * @see \Rector\Tests\Php70\Rector\List_\EmptyListRector\EmptyListRectorTest
 */
final class EmptyListRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('list() cannot be empty', [new CodeSample(<<<'CODE_SAMPLE'
'list() = $values;'
CODE_SAMPLE
, <<<'CODE_SAMPLE'
'list($unusedGenerated) = $values;'
CODE_SAMPLE
)]);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_EMPTY_LIST;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [List_::class];
    }
    /**
     * @param List_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($node->items as $item) {
            if ($item instanceof ArrayItem) {
                return null;
            }
        }
        $node->items[0] = new ArrayItem(new Variable('unusedGenerated'));
        return $node;
    }
}
