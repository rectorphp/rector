<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PhpAttribute\AnnotationToAttributeMapper;

use RectorPrefix20220606\PhpParser\BuilderHelpers;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantBooleanType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantFloatType;
use RectorPrefix20220606\Rector\Core\Exception\NotImplementedYetException;
use RectorPrefix20220606\Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
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
