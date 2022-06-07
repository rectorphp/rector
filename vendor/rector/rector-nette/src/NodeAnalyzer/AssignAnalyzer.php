<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToAddCollector;
final class AssignAnalyzer
{
    /**
     * @var string[]
     */
    private $alreadyInitializedAssignsClassMethodObjectHashes = [];
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator
     */
    private $varAnnotationManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NodesToAddCollector $nodesToAddCollector, VarAnnotationManipulator $varAnnotationManipulator, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->varAnnotationManipulator = $varAnnotationManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function addAssignExpressionForFirstCase(string $variableName, ArrayDimFetch $arrayDimFetch, ObjectType $controlObjectType) : void
    {
        if ($this->shouldSkipForAlreadyAddedInCurrentClassMethod($arrayDimFetch, $variableName)) {
            return;
        }
        $assignExpression = $this->createAnnotatedAssignExpression($variableName, $arrayDimFetch, $controlObjectType);
        $this->nodesToAddCollector->addNodeBeforeNode($assignExpression, $arrayDimFetch);
    }
    private function shouldSkipForAlreadyAddedInCurrentClassMethod(ArrayDimFetch $arrayDimFetch, string $variableName) : bool
    {
        $classMethod = $this->betterNodeFinder->findParentType($arrayDimFetch, ClassMethod::class);
        if (!$classMethod instanceof ClassMethod) {
            return \false;
        }
        $classMethodObjectHash = \spl_object_hash($classMethod) . $variableName;
        if (\in_array($classMethodObjectHash, $this->alreadyInitializedAssignsClassMethodObjectHashes, \true)) {
            return \true;
        }
        $this->alreadyInitializedAssignsClassMethodObjectHashes[] = $classMethodObjectHash;
        return \false;
    }
    private function createAnnotatedAssignExpression(string $variableName, ArrayDimFetch $arrayDimFetch, ObjectType $controlObjectType) : Expression
    {
        $assignExpression = $this->createAssignExpression($variableName, $arrayDimFetch);
        $this->varAnnotationManipulator->decorateNodeWithInlineVarType($assignExpression, $controlObjectType, $variableName);
        return $assignExpression;
    }
    private function createAssignExpression(string $variableName, ArrayDimFetch $arrayDimFetch) : Expression
    {
        $variable = new Variable($variableName);
        $assignedArrayDimFetch = clone $arrayDimFetch;
        $assign = new Assign($variable, $assignedArrayDimFetch);
        $variable->setAttribute(AttributeKey::PARENT_NODE, $assign);
        $assignedArrayDimFetch->setAttribute(AttributeKey::PARENT_NODE, $assign);
        return new Expression($assign);
    }
}
