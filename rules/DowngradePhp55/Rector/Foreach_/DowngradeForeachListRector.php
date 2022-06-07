<?php

declare (strict_types=1);
namespace Rector\DowngradePhp55\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\PhpParser\Node\NamedVariableFactory;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/foreachlist
 *
 * @see \Rector\Tests\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector\DowngradeForeachListRectorTest
 */
final class DowngradeForeachListRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NamedVariableFactory
     */
    private $namedVariableFactory;
    public function __construct(NamedVariableFactory $namedVariableFactory)
    {
        $this->namedVariableFactory = $namedVariableFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade list() support in foreach constructs', [new CodeSample(<<<'CODE_SAMPLE'
foreach ($array as $key => list($item1, $item2)) {
    var_dump($item1, $item2);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
foreach ($array as $key => arrayItem) {
    list($item1, $item2) = $arrayItem;
    var_dump($item1, $item2);
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->valueVar instanceof List_) {
            return null;
        }
        $variable = $this->namedVariableFactory->createVariable($node, 'arrayItem');
        $expression = new Expression(new Assign($node->valueVar, $variable));
        $node->valueVar = $variable;
        $node->stmts = \array_merge([$expression], $node->stmts);
        return $node;
    }
}
