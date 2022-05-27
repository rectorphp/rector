<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;
final class FunctionLikeDocParamTypeInferer implements \Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function inferParam(\PhpParser\Node\Param $param) : \PHPStan\Type\Type
    {
        $functionLike = $this->resolveScopeNode($param);
        if (!$functionLike instanceof \PhpParser\Node\FunctionLike) {
            return new \PHPStan\Type\MixedType();
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $paramTypesByName = $phpDocInfo->getParamTypesByName();
        if ($paramTypesByName === []) {
            return new \PHPStan\Type\MixedType();
        }
        return $this->matchParamNodeFromDoc($paramTypesByName, $param);
    }
    /**
     * @return ClassMethod|Function_|null
     */
    private function resolveScopeNode(\PhpParser\Node\Param $param) : ?\PhpParser\Node
    {
        return $this->betterNodeFinder->findParentByTypes($param, [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class]);
    }
    /**
     * @param Type[] $paramWithTypes
     */
    private function matchParamNodeFromDoc(array $paramWithTypes, \PhpParser\Node\Param $param) : \PHPStan\Type\Type
    {
        $paramNodeName = '$' . $this->nodeNameResolver->getName($param->var);
        return $paramWithTypes[$paramNodeName] ?? new \PHPStan\Type\MixedType();
    }
}
