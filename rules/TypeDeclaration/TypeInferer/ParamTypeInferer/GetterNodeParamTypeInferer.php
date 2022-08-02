<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\NodeManipulator\PropertyFetchAssignManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;
use RectorPrefix202208\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class GetterNodeParamTypeInferer implements ParamTypeInfererInterface
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyFetchAssignManipulator
     */
    private $propertyFetchAssignManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(PropertyFetchAssignManipulator $propertyFetchAssignManipulator, PropertyFetchAnalyzer $propertyFetchAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, BetterNodeFinder $betterNodeFinder)
    {
        $this->propertyFetchAssignManipulator = $propertyFetchAssignManipulator;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function inferParam(Param $param) : Type
    {
        $class = $this->betterNodeFinder->findParentType($param, Class_::class);
        if (!$class instanceof Class_) {
            return new MixedType();
        }
        /** @var ClassMethod $classMethod */
        $classMethod = $param->getAttribute(AttributeKey::PARENT_NODE);
        /** @var string $paramName */
        $paramName = $this->nodeNameResolver->getName($param);
        $propertyNames = $this->propertyFetchAssignManipulator->getPropertyNamesOfAssignOfVariable($classMethod, $paramName);
        if ($propertyNames === []) {
            return new MixedType();
        }
        $returnType = new MixedType();
        // resolve property assigns
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class, function (Node $node) use($propertyNames, &$returnType) : ?int {
            if (!$node instanceof Return_) {
                return null;
            }
            if ($node->expr === null) {
                return null;
            }
            $isMatch = $this->propertyFetchAnalyzer->isLocalPropertyOfNames($node->expr, $propertyNames);
            if (!$isMatch) {
                return null;
            }
            // what is return type?
            $classMethod = $this->betterNodeFinder->findParentType($node, ClassMethod::class);
            if (!$classMethod instanceof ClassMethod) {
                return null;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $methodReturnType = $phpDocInfo->getReturnType();
            if ($methodReturnType instanceof MixedType) {
                return null;
            }
            $returnType = $methodReturnType;
            return NodeTraverser::STOP_TRAVERSAL;
        });
        return $returnType;
    }
}
