<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ParentFinder;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;

final class FunctionLikeDocParamTypeInferer implements ParamTypeInfererInterface
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private ParentFinder $parentFinder
    ) {
    }

    public function inferParam(Param $param): Type
    {
        $functionLike = $this->resolveScopeNode($param);
        if (! $functionLike instanceof FunctionLike) {
            return new MixedType();
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);

        $paramTypesByName = $phpDocInfo->getParamTypesByName();
        if ($paramTypesByName === []) {
            return new MixedType();
        }

        return $this->matchParamNodeFromDoc($paramTypesByName, $param);
    }

    /**
     * @return ClassMethod|Function_|null
     */
    private function resolveScopeNode(Param $param): ?Node
    {
        return $this->parentFinder->findByTypes($param, [ClassMethod::class, Function_::class]);
    }

    /**
     * @param Type[] $paramWithTypes
     */
    private function matchParamNodeFromDoc(array $paramWithTypes, Param $param): Type
    {
        $paramNodeName = '$' . $this->nodeNameResolver->getName($param->var);
        return $paramWithTypes[$paramNodeName] ?? new MixedType();
    }
}
