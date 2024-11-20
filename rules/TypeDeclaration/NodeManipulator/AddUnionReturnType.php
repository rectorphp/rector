<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
final class AddUnionReturnType
{
    /**
     * @readonly
     */
    private ReturnTypeInferer $returnTypeInferer;
    /**
     * @readonly
     */
    private UnionTypeMapper $unionTypeMapper;
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    public function __construct(ReturnTypeInferer $returnTypeInferer, UnionTypeMapper $unionTypeMapper, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->unionTypeMapper = $unionTypeMapper;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
    }
    /**
     * @template TCallLike as ClassMethod|Function_
     *
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     * @return TCallLike|null
     */
    public function add($node, Scope $scope)
    {
        if ($node->stmts === null) {
            return null;
        }
        // type is already known
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        $inferReturnType = $this->returnTypeInferer->inferFunctionLike($node);
        if (!$inferReturnType instanceof UnionType) {
            return null;
        }
        $returnType = $this->unionTypeMapper->mapToPhpParserNode($inferReturnType, TypeKind::RETURN);
        if (!$returnType instanceof Node) {
            return null;
        }
        // handled by another PHP 7.1 rule with broader scope
        if ($returnType instanceof NullableType) {
            return null;
        }
        $node->returnType = $returnType;
        return $node;
    }
}
