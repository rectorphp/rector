<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\UnionType;
use Rector\Php\PhpVersionProvider;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\ValueObject\PhpVersion;
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
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReturnTypeInferer $returnTypeInferer, StaticTypeMapper $staticTypeMapper, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, PhpVersionProvider $phpVersionProvider)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @template TFunctionLike of ClassMethod|Function_
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return TFunctionLike|null
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
        // (object) passes an object through unchanged, so only "object" is safe, not stdClass
        $hasObjectCastReturn = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($functionLike, static fn(Node $subNode): bool => $subNode instanceof Return_ && $subNode->expr instanceof Object_);
        if ($hasObjectCastReturn) {
            return $this->addObjectReturnType($functionLike);
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
    /**
     * @template TFunctionLike of ClassMethod|Function_
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return TFunctionLike|null
     */
    private function addObjectReturnType($functionLike)
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersion::PHP_72)) {
            return null;
        }
        $hasNonObjectCastReturn = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($functionLike, static fn(Node $subNode): bool => $subNode instanceof Return_ && !$subNode->expr instanceof Object_);
        if ($hasNonObjectCastReturn) {
            return null;
        }
        $functionLike->returnType = new Identifier('object');
        return $functionLike;
    }
}
