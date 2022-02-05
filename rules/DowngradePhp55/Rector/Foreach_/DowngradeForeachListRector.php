<?php

declare (strict_types=1);
namespace Rector\DowngradePhp55\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/foreachlist
 *
 * @see Rector\Tests\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector\DowngradeForeachListRectorTest
 */
final class DowngradeForeachListRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade list() support in foreach constructs', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->valueVar instanceof \PhpParser\Node\Expr\List_) {
            return null;
        }
        $variable = $this->createVariable($node);
        $expression = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($node->valueVar, $variable));
        $node->valueVar = $variable;
        $node->stmts = \array_merge([$expression], $node->stmts);
        return $node;
    }
    private function createVariable(\PhpParser\Node\Stmt\Foreach_ $foreach) : \PhpParser\Node\Expr\Variable
    {
        $currentStmt = $foreach->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!$currentStmt instanceof \PhpParser\Node) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $scope = $currentStmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        return new \PhpParser\Node\Expr\Variable($this->variableNaming->createCountedValueName('arrayItem', $scope));
    }
}
