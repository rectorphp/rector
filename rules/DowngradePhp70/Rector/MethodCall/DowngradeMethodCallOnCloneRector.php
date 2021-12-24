<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration70.new-features.php#migration70.new-features.others
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeMethodCallOnCloneRector\DowngradeMethodCallOnCloneRectorTest
 */
final class DowngradeMethodCallOnCloneRector extends \Rector\Core\Rector\AbstractRector
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
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace (clone $obj)->call() to object assign and call', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
(clone $this)->execute();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$object = (clone $this);
$object->execute();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Expr\MethodCall
    {
        $isFoundCloneInAssign = \false;
        if (!$node->var instanceof \PhpParser\Node\Expr\Clone_) {
            if (!$node->var instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            $isFoundCloneInAssign = (bool) $this->betterNodeFinder->findFirstInstanceOf($node->var->expr, \PhpParser\Node\Expr\Clone_::class);
            if (!$isFoundCloneInAssign) {
                return null;
            }
        }
        $currentStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!$currentStatement instanceof \PhpParser\Node\Stmt) {
            return null;
        }
        if ($isFoundCloneInAssign) {
            /** @var Assign $assign */
            $assign = $node->var;
            $variable = $assign->var;
        } else {
            $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            $newVariableName = $this->variableNaming->createCountedValueName('object', $scope);
            $variable = new \PhpParser\Node\Expr\Variable($newVariableName);
            $assign = new \PhpParser\Node\Expr\Assign($variable, $node->var);
        }
        $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Stmt\Expression($assign), $currentStatement);
        $node->var = $variable;
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
