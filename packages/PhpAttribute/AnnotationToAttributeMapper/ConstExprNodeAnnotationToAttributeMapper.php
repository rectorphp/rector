<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Expr;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
/**
 * @implements AnnotationToAttributeMapperInterface<ConstExprNode>
 */
final class ConstExprNodeAnnotationToAttributeMapper implements \Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface
{
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool
    {
        return $value instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
    }
    /**
     * @param ConstExprNode $value
     */
    public function map($value) : \PhpParser\Node\Expr
    {
        if ($value instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode) {
            return \PhpParser\BuilderHelpers::normalizeValue((int) $value->value);
        }
        if ($value instanceof \PHPStan\Type\Constant\ConstantFloatType || $value instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            return \PhpParser\BuilderHelpers::normalizeValue($value->getValue());
        }
        if ($value instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode) {
            return \PhpParser\BuilderHelpers::normalizeValue(\true);
        }
        if ($value instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode) {
            return \PhpParser\BuilderHelpers::normalizeValue(\false);
        }
        throw new \Rector\Core\Exception\NotImplementedYetException();
    }
}
