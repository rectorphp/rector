<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class PropertyNodeParamTypeInferer implements ParamTypeInfererInterface
{
    public function __construct(
        private readonly PropertyFetchAnalyzer $propertyFetchAnalyzer,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private readonly NodeTypeResolver $nodeTypeResolver,
        private readonly TypeFactory $typeFactory,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly StaticTypeMapper $staticTypeMapper,
        private readonly NodeComparator $nodeComparator
    ) {
    }

    public function inferParam(Param $param): Type
    {
        $classLike = $this->betterNodeFinder->findParentType($param, Class_::class);
        if (! $classLike instanceof Class_) {
            return new MixedType();
        }

        $paramName = $this->nodeNameResolver->getName($param);

        /** @var ClassMethod $classMethod */
        $classMethod = $param->getAttribute(AttributeKey::PARENT_NODE);

        $propertyStaticTypes = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use (
            $paramName,
            &$propertyStaticTypes
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->propertyFetchAnalyzer->isVariableAssignToThisPropertyFetch($node, $paramName)) {
                return null;
            }

            $exprType = $this->nodeTypeResolver->getType($node->expr);
            $nodeExprType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($exprType, TypeKind::PARAM());
            $varType = $this->nodeTypeResolver->getType($node->var);
            $nodeVarType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, TypeKind::ANY());

            if ($nodeExprType instanceof Node && ! $this->nodeComparator->areNodesEqual($nodeExprType, $nodeVarType)) {
                return null;
            }

            $propertyStaticTypes[] = $varType;

            return null;
        });

        return $this->typeFactory->createMixedPassedOrUnionType($propertyStaticTypes);
    }
}
