<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\UnionType;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
final class AddReturnTypeFromCast
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ReturnTypeInferer $returnTypeInferer;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReturnTypeInferer $returnTypeInferer, StaticTypeMapper $staticTypeMapper, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|null
     */
    public function add($functionLike, Scope $scope)
    {
        if ($functionLike->returnType instanceof Node) {
            return null;
        }
        if ($functionLike instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($functionLike, $scope)) {
            return null;
        }
        $hasNonCastReturn = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($functionLike, static fn(Node $subNode): bool => $subNode instanceof Return_ && !$subNode->expr instanceof Cast);
        if ($hasNonCastReturn) {
            return null;
        }
        $returnType = $this->returnTypeInferer->inferFunctionLike($functionLike);
        if ($returnType instanceof UnionType) {
            return null;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        $functionLike->returnType = $returnTypeNode;
        return $functionLike;
    }
}
