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
use Rector\Nette\NodeAdding\FunctionLikeFirstLevelStatementResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToAddCollector;
final class AssignAnalyzer
{
    /**
     * @var string[]
     */
    private $alreadyInitializedAssignsClassMethodObjectHashes = [];
    /**
     * @var \Rector\Nette\NodeAdding\FunctionLikeFirstLevelStatementResolver
     */
    private $functionLikeFirstLevelStatementResolver;
    /**
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator
     */
    private $varAnnotationManipulator;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Nette\NodeAdding\FunctionLikeFirstLevelStatementResolver $functionLikeFirstLevelStatementResolver, \Rector\PostRector\Collector\NodesToAddCollector $nodesToAddCollector, \Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator $varAnnotationManipulator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->functionLikeFirstLevelStatementResolver = $functionLikeFirstLevelStatementResolver;
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->varAnnotationManipulator = $varAnnotationManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function addAssignExpressionForFirstCase(string $variableName, \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch, \PHPStan\Type\ObjectType $controlObjectType) : void
    {
        if ($this->shouldSkipForAlreadyAddedInCurrentClassMethod($arrayDimFetch, $variableName)) {
            return;
        }
        $assignExpression = $this->createAnnotatedAssignExpression($variableName, $arrayDimFetch, $controlObjectType);
        $currentStatement = $this->functionLikeFirstLevelStatementResolver->resolveFirstLevelStatement($arrayDimFetch);
        $this->nodesToAddCollector->addNodeBeforeNode($assignExpression, $currentStatement);
    }
    private function shouldSkipForAlreadyAddedInCurrentClassMethod(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch, string $variableName) : bool
    {
        $classMethod = $this->betterNodeFinder->findParentType($arrayDimFetch, \PhpParser\Node\Stmt\ClassMethod::class);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        $classMethodObjectHash = \spl_object_hash($classMethod) . $variableName;
        if (\in_array($classMethodObjectHash, $this->alreadyInitializedAssignsClassMethodObjectHashes, \true)) {
            return \true;
        }
        $this->alreadyInitializedAssignsClassMethodObjectHashes[] = $classMethodObjectHash;
        return \false;
    }
    private function createAnnotatedAssignExpression(string $variableName, \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch, \PHPStan\Type\ObjectType $controlObjectType) : \PhpParser\Node\Stmt\Expression
    {
        $assignExpression = $this->createAssignExpression($variableName, $arrayDimFetch);
        $this->varAnnotationManipulator->decorateNodeWithInlineVarType($assignExpression, $controlObjectType, $variableName);
        return $assignExpression;
    }
    private function createAssignExpression(string $variableName, \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : \PhpParser\Node\Stmt\Expression
    {
        $variable = new \PhpParser\Node\Expr\Variable($variableName);
        $assignedArrayDimFetch = clone $arrayDimFetch;
        $assign = new \PhpParser\Node\Expr\Assign($variable, $assignedArrayDimFetch);
        $variable->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE, $assign);
        $assignedArrayDimFetch->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE, $assign);
        return new \PhpParser\Node\Stmt\Expression($assign);
    }
}
