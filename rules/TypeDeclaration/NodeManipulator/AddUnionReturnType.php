<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
final class AddUnionReturnType
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper
     */
    private $unionTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    public function __construct(ReturnTypeInferer $returnTypeInferer, UnionTypeMapper $unionTypeMapper, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->unionTypeMapper = $unionTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|null
     */
    public function add($node, Scope $scope)
    {
        if ($node->stmts === null) {
            return null;
        }
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
        $this->mapStandaloneSubType($node, $inferReturnType);
        $node->returnType = $returnType;
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function mapStandaloneSubType($node, UnionType $unionType) : void
    {
        $value = null;
        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof ConstantBooleanType) {
                $value = $type->getValue() ? 'true' : 'false';
                break;
            }
        }
        if ($value === null) {
            return;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnType = $phpDocInfo->getReturnTagValue();
        if (!$returnType instanceof ReturnTagValueNode) {
            return;
        }
        if (!$returnType->type instanceof BracketsAwareUnionTypeNode) {
            return;
        }
        foreach ($returnType->type->types as $key => $type) {
            if ($type instanceof IdentifierTypeNode && $type->__toString() === 'bool') {
                $returnType->type->types[$key] = new IdentifierTypeNode($value);
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
                break;
            }
        }
    }
}
