<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration70.new-features.php#migration70.new-features.others
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeMethodCallOnCloneRector\DowngradeMethodCallOnCloneRectorTest
 */
final class DowngradeMethodCallOnCloneRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(VariableNaming $variableNaming, NodesToAddCollector $nodesToAddCollector)
    {
        $this->variableNaming = $variableNaming;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace (clone $obj)->call() to object assign and call', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?MethodCall
    {
        $isFoundCloneInAssign = \false;
        if (!$node->var instanceof Clone_) {
            if (!$node->var instanceof Assign) {
                return null;
            }
            $isFoundCloneInAssign = (bool) $this->betterNodeFinder->findFirstInstanceOf($node->var->expr, Clone_::class);
            if (!$isFoundCloneInAssign) {
                return null;
            }
        }
        if ($isFoundCloneInAssign) {
            /** @var Assign $assign */
            $assign = $node->var;
            $variable = $assign->var;
        } else {
            $scope = $node->getAttribute(AttributeKey::SCOPE);
            $newVariableName = $this->variableNaming->createCountedValueName('object', $scope);
            $variable = new Variable($newVariableName);
            $assign = new Assign($variable, $node->var);
        }
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression($assign), $node);
        $node->var = $variable;
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
