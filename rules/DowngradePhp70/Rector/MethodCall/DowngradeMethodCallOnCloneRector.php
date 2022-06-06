<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp70\Rector\MethodCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\Clone_;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Naming\Naming\VariableNaming;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
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
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression($assign), $node, $this->file->getSmartFileInfo());
        $node->var = $variable;
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
