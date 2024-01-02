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
use Rector\Exception\NotImplementedYetException;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
/**
 * @implements AnnotationToAttributeMapperInterface<ConstExprNode>
 */
final class ConstExprNodeAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool
    {
        return $value instanceof ConstExprNode;
    }
    /**
     * @param ConstExprNode $value
     */
    public function map($value) : Expr
    {
        if ($value instanceof ConstExprIntegerNode) {
            return BuilderHelpers::normalizeValue((int) $value->value);
        }
        if ($value instanceof ConstantFloatType || $value instanceof ConstantBooleanType) {
            return BuilderHelpers::normalizeValue($value->getValue());
        }
        if ($value instanceof ConstExprTrueNode) {
            return BuilderHelpers::normalizeValue(\true);
        }
        if ($value instanceof ConstExprFalseNode) {
            return BuilderHelpers::normalizeValue(\false);
        }
        throw new NotImplementedYetException();
    }
}
