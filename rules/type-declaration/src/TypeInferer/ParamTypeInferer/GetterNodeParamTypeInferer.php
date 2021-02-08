<?php

declare(strict_types=1);

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
use Rector\Core\NodeManipulator\PropertyFetchAssignManipulator;
use Rector\Core\NodeManipulator\PropertyFetchManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class GetterNodeParamTypeInferer extends AbstractTypeInferer implements ParamTypeInfererInterface
{
    /**
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    /**
     * @var PropertyFetchAssignManipulator
     */
    private $propertyFetchAssignManipulator;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(
        PropertyFetchAssignManipulator $propertyFetchAssignManipulator,
        PropertyFetchManipulator $propertyFetchManipulator,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->propertyFetchManipulator = $propertyFetchManipulator;
        $this->propertyFetchAssignManipulator = $propertyFetchAssignManipulator;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function inferParam(Param $param): Type
    {
        $classLike = $param->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return new MixedType();
        }

        /** @var ClassMethod $classMethod */
        $classMethod = $param->getAttribute(AttributeKey::PARENT_NODE);

        /** @var string $paramName */
        $paramName = $this->nodeNameResolver->getName($param);

        $propertyNames = $this->propertyFetchAssignManipulator->getPropertyNamesOfAssignOfVariable(
            $classMethod,
            $paramName
        );
        if ($propertyNames === []) {
            return new MixedType();
        }

        $returnType = new MixedType();

        // resolve property assigns
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classLike, function (Node $node) use (
            $propertyNames,
            &$returnType
        ): ?int {
            if (! $node instanceof Return_) {
                return null;
            }
            if ($node->expr === null) {
                return null;
            }
            $isMatch = $this->propertyFetchManipulator->isLocalPropertyOfNames($node->expr, $propertyNames);
            if (! $isMatch) {
                return null;
            }

            // what is return type?
            $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
            if (! $classMethod instanceof ClassMethod) {
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
