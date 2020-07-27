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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\PhpParser\Node\Manipulator\PropertyFetchAssignManipulator;
use Rector\Core\PhpParser\Node\Manipulator\PropertyFetchManipulator;
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

    public function __construct(
        PropertyFetchAssignManipulator $propertyFetchAssignManipulator,
        PropertyFetchManipulator $propertyFetchManipulator
    ) {
        $this->propertyFetchManipulator = $propertyFetchManipulator;
        $this->propertyFetchAssignManipulator = $propertyFetchAssignManipulator;
    }

    public function inferParam(Param $param): Type
    {
        /** @var Class_|null $classLike */
        $classLike = $param->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
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
        $this->callableNodeTraverser->traverseNodesWithCallable($classLike, function (Node $node) use (
            $propertyNames,
            &$returnType
        ): ?int {
            if (! $node instanceof Return_ || $node->expr === null) {
                return null;
            }

            $isMatch = $this->propertyFetchManipulator->isLocalPropertyOfNames($node->expr, $propertyNames);
            if (! $isMatch) {
                return null;
            }

            // what is return type?
            /** @var ClassMethod|null $classMethod */
            $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
            if (! $classMethod instanceof ClassMethod) {
                return null;
            }

            /** @var PhpDocInfo $phpDocInfo */
            $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);

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
