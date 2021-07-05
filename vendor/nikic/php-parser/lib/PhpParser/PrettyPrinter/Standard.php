<?php

declare (strict_types=1);
namespace PhpParser\PrettyPrinter;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Stmt;
use PhpParser\PrettyPrinterAbstract;
class Standard extends \PhpParser\PrettyPrinterAbstract
{
    // Special nodes
    /**
     * @param \PhpParser\Node\Param $node
     */
    protected function pParam($node)
    {
        return $this->pAttrGroups($node->attrGroups, \true) . $this->pModifiers($node->flags) . ($node->type ? $this->p($node->type) . ' ' : '') . ($node->byRef ? '&' : '') . ($node->variadic ? '...' : '') . $this->p($node->var) . ($node->default ? ' = ' . $this->p($node->default) : '');
    }
    /**
     * @param \PhpParser\Node\Arg $node
     */
    protected function pArg($node)
    {
        return ($node->name ? $node->name->toString() . ': ' : '') . ($node->byRef ? '&' : '') . ($node->unpack ? '...' : '') . $this->p($node->value);
    }
    /**
     * @param \PhpParser\Node\Const_ $node
     */
    protected function pConst($node)
    {
        return $node->name . ' = ' . $this->p($node->value);
    }
    /**
     * @param \PhpParser\Node\NullableType $node
     */
    protected function pNullableType($node)
    {
        return '?' . $this->p($node->type);
    }
    /**
     * @param \PhpParser\Node\UnionType $node
     */
    protected function pUnionType($node)
    {
        return $this->pImplode($node->types, '|');
    }
    /**
     * @param \PhpParser\Node\Identifier $node
     */
    protected function pIdentifier($node)
    {
        return $node->name;
    }
    /**
     * @param \PhpParser\Node\VarLikeIdentifier $node
     */
    protected function pVarLikeIdentifier($node)
    {
        return '$' . $node->name;
    }
    /**
     * @param \PhpParser\Node\Attribute $node
     */
    protected function pAttribute($node)
    {
        return $this->p($node->name) . ($node->args ? '(' . $this->pCommaSeparated($node->args) . ')' : '');
    }
    /**
     * @param \PhpParser\Node\AttributeGroup $node
     */
    protected function pAttributeGroup($node)
    {
        return '#[' . $this->pCommaSeparated($node->attrs) . ']';
    }
    // Names
    /**
     * @param \PhpParser\Node\Name $node
     */
    protected function pName($node)
    {
        return \implode('\\', $node->parts);
    }
    /**
     * @param \PhpParser\Node\Name\FullyQualified $node
     */
    protected function pName_FullyQualified($node)
    {
        return '\\' . \implode('\\', $node->parts);
    }
    /**
     * @param \PhpParser\Node\Name\Relative $node
     */
    protected function pName_Relative($node)
    {
        return 'namespace\\' . \implode('\\', $node->parts);
    }
    // Magic Constants
    /**
     * @param \PhpParser\Node\Scalar\MagicConst\Class_ $node
     */
    protected function pScalar_MagicConst_Class($node)
    {
        return '__CLASS__';
    }
    /**
     * @param \PhpParser\Node\Scalar\MagicConst\Dir $node
     */
    protected function pScalar_MagicConst_Dir($node)
    {
        return '__DIR__';
    }
    /**
     * @param \PhpParser\Node\Scalar\MagicConst\File $node
     */
    protected function pScalar_MagicConst_File($node)
    {
        return '__FILE__';
    }
    /**
     * @param \PhpParser\Node\Scalar\MagicConst\Function_ $node
     */
    protected function pScalar_MagicConst_Function($node)
    {
        return '__FUNCTION__';
    }
    /**
     * @param \PhpParser\Node\Scalar\MagicConst\Line $node
     */
    protected function pScalar_MagicConst_Line($node)
    {
        return '__LINE__';
    }
    /**
     * @param \PhpParser\Node\Scalar\MagicConst\Method $node
     */
    protected function pScalar_MagicConst_Method($node)
    {
        return '__METHOD__';
    }
    /**
     * @param \PhpParser\Node\Scalar\MagicConst\Namespace_ $node
     */
    protected function pScalar_MagicConst_Namespace($node)
    {
        return '__NAMESPACE__';
    }
    /**
     * @param \PhpParser\Node\Scalar\MagicConst\Trait_ $node
     */
    protected function pScalar_MagicConst_Trait($node)
    {
        return '__TRAIT__';
    }
    // Scalars
    /**
     * @param \PhpParser\Node\Scalar\String_ $node
     */
    protected function pScalar_String($node)
    {
        $kind = $node->getAttribute('kind', \PhpParser\Node\Scalar\String_::KIND_SINGLE_QUOTED);
        switch ($kind) {
            case \PhpParser\Node\Scalar\String_::KIND_NOWDOC:
                $label = $node->getAttribute('docLabel');
                if ($label && !$this->containsEndLabel($node->value, $label)) {
                    if ($node->value === '') {
                        return "<<<'{$label}'\n{$label}" . $this->docStringEndToken;
                    }
                    return "<<<'{$label}'\n{$node->value}\n{$label}" . $this->docStringEndToken;
                }
            /* break missing intentionally */
            case \PhpParser\Node\Scalar\String_::KIND_SINGLE_QUOTED:
                return $this->pSingleQuotedString($node->value);
            case \PhpParser\Node\Scalar\String_::KIND_HEREDOC:
                $label = $node->getAttribute('docLabel');
                if ($label && !$this->containsEndLabel($node->value, $label)) {
                    if ($node->value === '') {
                        return "<<<{$label}\n{$label}" . $this->docStringEndToken;
                    }
                    $escaped = $this->escapeString($node->value, null);
                    return "<<<{$label}\n" . $escaped . "\n{$label}" . $this->docStringEndToken;
                }
            /* break missing intentionally */
            case \PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED:
                return '"' . $this->escapeString($node->value, '"') . '"';
        }
        throw new \Exception('Invalid string kind');
    }
    /**
     * @param \PhpParser\Node\Scalar\Encapsed $node
     */
    protected function pScalar_Encapsed($node)
    {
        if ($node->getAttribute('kind') === \PhpParser\Node\Scalar\String_::KIND_HEREDOC) {
            $label = $node->getAttribute('docLabel');
            if ($label && !$this->encapsedContainsEndLabel($node->parts, $label)) {
                if (\count($node->parts) === 1 && $node->parts[0] instanceof \PhpParser\Node\Scalar\EncapsedStringPart && $node->parts[0]->value === '') {
                    return "<<<{$label}\n{$label}" . $this->docStringEndToken;
                }
                return "<<<{$label}\n" . $this->pEncapsList($node->parts, null) . "\n{$label}" . $this->docStringEndToken;
            }
        }
        return '"' . $this->pEncapsList($node->parts, '"') . '"';
    }
    /**
     * @param \PhpParser\Node\Scalar\LNumber $node
     */
    protected function pScalar_LNumber($node)
    {
        if ($node->value === -\PHP_INT_MAX - 1) {
            // PHP_INT_MIN cannot be represented as a literal,
            // because the sign is not part of the literal
            return '(-' . \PHP_INT_MAX . '-1)';
        }
        $kind = $node->getAttribute('kind', \PhpParser\Node\Scalar\LNumber::KIND_DEC);
        if (\PhpParser\Node\Scalar\LNumber::KIND_DEC === $kind) {
            return (string) $node->value;
        }
        if ($node->value < 0) {
            $sign = '-';
            $str = (string) -$node->value;
        } else {
            $sign = '';
            $str = (string) $node->value;
        }
        switch ($kind) {
            case \PhpParser\Node\Scalar\LNumber::KIND_BIN:
                return $sign . '0b' . \base_convert($str, 10, 2);
            case \PhpParser\Node\Scalar\LNumber::KIND_OCT:
                return $sign . '0' . \base_convert($str, 10, 8);
            case \PhpParser\Node\Scalar\LNumber::KIND_HEX:
                return $sign . '0x' . \base_convert($str, 10, 16);
        }
        throw new \Exception('Invalid number kind');
    }
    /**
     * @param \PhpParser\Node\Scalar\DNumber $node
     */
    protected function pScalar_DNumber($node)
    {
        if (!\is_finite($node->value)) {
            if ($node->value === \INF) {
                return '\\INF';
            } elseif ($node->value === -\INF) {
                return '-\\INF';
            } else {
                return '\\NAN';
            }
        }
        // Try to find a short full-precision representation
        $stringValue = \sprintf('%.16G', $node->value);
        if ($node->value !== (double) $stringValue) {
            $stringValue = \sprintf('%.17G', $node->value);
        }
        // %G is locale dependent and there exists no locale-independent alternative. We don't want
        // mess with switching locales here, so let's assume that a comma is the only non-standard
        // decimal separator we may encounter...
        $stringValue = \str_replace(',', '.', $stringValue);
        // ensure that number is really printed as float
        return \preg_match('/^-?[0-9]+$/', $stringValue) ? $stringValue . '.0' : $stringValue;
    }
    /**
     * @param \PhpParser\Node\Scalar\EncapsedStringPart $node
     */
    protected function pScalar_EncapsedStringPart($node)
    {
        throw new \LogicException('Cannot directly print EncapsedStringPart');
    }
    // Assignments
    /**
     * @param \PhpParser\Node\Expr\Assign $node
     */
    protected function pExpr_Assign($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\Assign::class, $node->var, ' = ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignRef $node
     */
    protected function pExpr_AssignRef($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignRef::class, $node->var, ' =& ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\Plus $node
     */
    protected function pExpr_AssignOp_Plus($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\Plus::class, $node->var, ' += ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\Minus $node
     */
    protected function pExpr_AssignOp_Minus($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\Minus::class, $node->var, ' -= ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\Mul $node
     */
    protected function pExpr_AssignOp_Mul($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\Mul::class, $node->var, ' *= ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\Div $node
     */
    protected function pExpr_AssignOp_Div($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\Div::class, $node->var, ' /= ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\Concat $node
     */
    protected function pExpr_AssignOp_Concat($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\Concat::class, $node->var, ' .= ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\Mod $node
     */
    protected function pExpr_AssignOp_Mod($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\Mod::class, $node->var, ' %= ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\BitwiseAnd $node
     */
    protected function pExpr_AssignOp_BitwiseAnd($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\BitwiseAnd::class, $node->var, ' &= ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\BitwiseOr $node
     */
    protected function pExpr_AssignOp_BitwiseOr($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\BitwiseOr::class, $node->var, ' |= ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\BitwiseXor $node
     */
    protected function pExpr_AssignOp_BitwiseXor($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\BitwiseXor::class, $node->var, ' ^= ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\ShiftLeft $node
     */
    protected function pExpr_AssignOp_ShiftLeft($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\ShiftLeft::class, $node->var, ' <<= ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\ShiftRight $node
     */
    protected function pExpr_AssignOp_ShiftRight($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\ShiftRight::class, $node->var, ' >>= ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\Pow $node
     */
    protected function pExpr_AssignOp_Pow($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\Pow::class, $node->var, ' **= ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\AssignOp\Coalesce $node
     */
    protected function pExpr_AssignOp_Coalesce($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\AssignOp\Coalesce::class, $node->var, ' ??= ', $node->expr);
    }
    // Binary expressions
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Plus $node
     */
    protected function pExpr_BinaryOp_Plus($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Plus::class, $node->left, ' + ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Minus $node
     */
    protected function pExpr_BinaryOp_Minus($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Minus::class, $node->left, ' - ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Mul $node
     */
    protected function pExpr_BinaryOp_Mul($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Mul::class, $node->left, ' * ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Div $node
     */
    protected function pExpr_BinaryOp_Div($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Div::class, $node->left, ' / ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Concat $node
     */
    protected function pExpr_BinaryOp_Concat($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Concat::class, $node->left, ' . ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Mod $node
     */
    protected function pExpr_BinaryOp_Mod($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Mod::class, $node->left, ' % ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\BooleanAnd $node
     */
    protected function pExpr_BinaryOp_BooleanAnd($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\BooleanAnd::class, $node->left, ' && ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\BooleanOr $node
     */
    protected function pExpr_BinaryOp_BooleanOr($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\BooleanOr::class, $node->left, ' || ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\BitwiseAnd $node
     */
    protected function pExpr_BinaryOp_BitwiseAnd($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\BitwiseAnd::class, $node->left, ' & ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\BitwiseOr $node
     */
    protected function pExpr_BinaryOp_BitwiseOr($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\BitwiseOr::class, $node->left, ' | ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\BitwiseXor $node
     */
    protected function pExpr_BinaryOp_BitwiseXor($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\BitwiseXor::class, $node->left, ' ^ ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\ShiftLeft $node
     */
    protected function pExpr_BinaryOp_ShiftLeft($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\ShiftLeft::class, $node->left, ' << ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\ShiftRight $node
     */
    protected function pExpr_BinaryOp_ShiftRight($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\ShiftRight::class, $node->left, ' >> ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Pow $node
     */
    protected function pExpr_BinaryOp_Pow($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Pow::class, $node->left, ' ** ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\LogicalAnd $node
     */
    protected function pExpr_BinaryOp_LogicalAnd($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\LogicalAnd::class, $node->left, ' and ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\LogicalOr $node
     */
    protected function pExpr_BinaryOp_LogicalOr($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\LogicalOr::class, $node->left, ' or ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\LogicalXor $node
     */
    protected function pExpr_BinaryOp_LogicalXor($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\LogicalXor::class, $node->left, ' xor ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Equal $node
     */
    protected function pExpr_BinaryOp_Equal($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Equal::class, $node->left, ' == ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\NotEqual $node
     */
    protected function pExpr_BinaryOp_NotEqual($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\NotEqual::class, $node->left, ' != ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical $node
     */
    protected function pExpr_BinaryOp_Identical($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Identical::class, $node->left, ' === ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\NotIdentical $node
     */
    protected function pExpr_BinaryOp_NotIdentical($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\NotIdentical::class, $node->left, ' !== ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Spaceship $node
     */
    protected function pExpr_BinaryOp_Spaceship($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Spaceship::class, $node->left, ' <=> ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Greater $node
     */
    protected function pExpr_BinaryOp_Greater($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Greater::class, $node->left, ' > ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual $node
     */
    protected function pExpr_BinaryOp_GreaterOrEqual($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\GreaterOrEqual::class, $node->left, ' >= ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Smaller $node
     */
    protected function pExpr_BinaryOp_Smaller($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Smaller::class, $node->left, ' < ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\SmallerOrEqual $node
     */
    protected function pExpr_BinaryOp_SmallerOrEqual($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\SmallerOrEqual::class, $node->left, ' <= ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Coalesce $node
     */
    protected function pExpr_BinaryOp_Coalesce($node)
    {
        return $this->pInfixOp(\PhpParser\Node\Expr\BinaryOp\Coalesce::class, $node->left, ' ?? ', $node->right);
    }
    /**
     * @param \PhpParser\Node\Expr\Instanceof_ $node
     */
    protected function pExpr_Instanceof($node)
    {
        list($precedence, $associativity) = $this->precedenceMap[\PhpParser\Node\Expr\Instanceof_::class];
        return $this->pPrec($node->expr, $precedence, $associativity, -1) . ' instanceof ' . $this->pNewVariable($node->class);
    }
    // Unary expressions
    /**
     * @param \PhpParser\Node\Expr\BooleanNot $node
     */
    protected function pExpr_BooleanNot($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\BooleanNot::class, '!', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\BitwiseNot $node
     */
    protected function pExpr_BitwiseNot($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\BitwiseNot::class, '~', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\UnaryMinus $node
     */
    protected function pExpr_UnaryMinus($node)
    {
        if ($node->expr instanceof \PhpParser\Node\Expr\UnaryMinus || $node->expr instanceof \PhpParser\Node\Expr\PreDec) {
            // Enforce -(-$expr) instead of --$expr
            return '-(' . $this->p($node->expr) . ')';
        }
        return $this->pPrefixOp(\PhpParser\Node\Expr\UnaryMinus::class, '-', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\UnaryPlus $node
     */
    protected function pExpr_UnaryPlus($node)
    {
        if ($node->expr instanceof \PhpParser\Node\Expr\UnaryPlus || $node->expr instanceof \PhpParser\Node\Expr\PreInc) {
            // Enforce +(+$expr) instead of ++$expr
            return '+(' . $this->p($node->expr) . ')';
        }
        return $this->pPrefixOp(\PhpParser\Node\Expr\UnaryPlus::class, '+', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\PreInc $node
     */
    protected function pExpr_PreInc($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\PreInc::class, '++', $node->var);
    }
    /**
     * @param \PhpParser\Node\Expr\PreDec $node
     */
    protected function pExpr_PreDec($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\PreDec::class, '--', $node->var);
    }
    /**
     * @param \PhpParser\Node\Expr\PostInc $node
     */
    protected function pExpr_PostInc($node)
    {
        return $this->pPostfixOp(\PhpParser\Node\Expr\PostInc::class, $node->var, '++');
    }
    /**
     * @param \PhpParser\Node\Expr\PostDec $node
     */
    protected function pExpr_PostDec($node)
    {
        return $this->pPostfixOp(\PhpParser\Node\Expr\PostDec::class, $node->var, '--');
    }
    /**
     * @param \PhpParser\Node\Expr\ErrorSuppress $node
     */
    protected function pExpr_ErrorSuppress($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\ErrorSuppress::class, '@', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\YieldFrom $node
     */
    protected function pExpr_YieldFrom($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\YieldFrom::class, 'yield from ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\Print_ $node
     */
    protected function pExpr_Print($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\Print_::class, 'print ', $node->expr);
    }
    // Casts
    /**
     * @param \PhpParser\Node\Expr\Cast\Int_ $node
     */
    protected function pExpr_Cast_Int($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\Cast\Int_::class, '(int) ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\Cast\Double $node
     */
    protected function pExpr_Cast_Double($node)
    {
        $kind = $node->getAttribute('kind', \PhpParser\Node\Expr\Cast\Double::KIND_DOUBLE);
        if ($kind === \PhpParser\Node\Expr\Cast\Double::KIND_DOUBLE) {
            $cast = '(double)';
        } elseif ($kind === \PhpParser\Node\Expr\Cast\Double::KIND_FLOAT) {
            $cast = '(float)';
        } elseif ($kind === \PhpParser\Node\Expr\Cast\Double::KIND_REAL) {
            $cast = '(real)';
        }
        return $this->pPrefixOp(\PhpParser\Node\Expr\Cast\Double::class, $cast . ' ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\Cast\String_ $node
     */
    protected function pExpr_Cast_String($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\Cast\String_::class, '(string) ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\Cast\Array_ $node
     */
    protected function pExpr_Cast_Array($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\Cast\Array_::class, '(array) ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\Cast\Object_ $node
     */
    protected function pExpr_Cast_Object($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\Cast\Object_::class, '(object) ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\Cast\Bool_ $node
     */
    protected function pExpr_Cast_Bool($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\Cast\Bool_::class, '(bool) ', $node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\Cast\Unset_ $node
     */
    protected function pExpr_Cast_Unset($node)
    {
        return $this->pPrefixOp(\PhpParser\Node\Expr\Cast\Unset_::class, '(unset) ', $node->expr);
    }
    // Function calls and similar constructs
    /**
     * @param \PhpParser\Node\Expr\FuncCall $node
     */
    protected function pExpr_FuncCall($node)
    {
        return $this->pCallLhs($node->name) . '(' . $this->pMaybeMultiline($node->args) . ')';
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall $node
     */
    protected function pExpr_MethodCall($node)
    {
        return $this->pDereferenceLhs($node->var) . '->' . $this->pObjectProperty($node->name) . '(' . $this->pMaybeMultiline($node->args) . ')';
    }
    /**
     * @param \PhpParser\Node\Expr\NullsafeMethodCall $node
     */
    protected function pExpr_NullsafeMethodCall($node)
    {
        return $this->pDereferenceLhs($node->var) . '?->' . $this->pObjectProperty($node->name) . '(' . $this->pMaybeMultiline($node->args) . ')';
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall $node
     */
    protected function pExpr_StaticCall($node)
    {
        return $this->pDereferenceLhs($node->class) . '::' . ($node->name instanceof \PhpParser\Node\Expr ? $node->name instanceof \PhpParser\Node\Expr\Variable ? $this->p($node->name) : '{' . $this->p($node->name) . '}' : $node->name) . '(' . $this->pMaybeMultiline($node->args) . ')';
    }
    /**
     * @param \PhpParser\Node\Expr\Empty_ $node
     */
    protected function pExpr_Empty($node)
    {
        return 'empty(' . $this->p($node->expr) . ')';
    }
    /**
     * @param \PhpParser\Node\Expr\Isset_ $node
     */
    protected function pExpr_Isset($node)
    {
        return 'isset(' . $this->pCommaSeparated($node->vars) . ')';
    }
    /**
     * @param \PhpParser\Node\Expr\Eval_ $node
     */
    protected function pExpr_Eval($node)
    {
        return 'eval(' . $this->p($node->expr) . ')';
    }
    /**
     * @param \PhpParser\Node\Expr\Include_ $node
     */
    protected function pExpr_Include($node)
    {
        static $map = [\PhpParser\Node\Expr\Include_::TYPE_INCLUDE => 'include', \PhpParser\Node\Expr\Include_::TYPE_INCLUDE_ONCE => 'include_once', \PhpParser\Node\Expr\Include_::TYPE_REQUIRE => 'require', \PhpParser\Node\Expr\Include_::TYPE_REQUIRE_ONCE => 'require_once'];
        return $map[$node->type] . ' ' . $this->p($node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\List_ $node
     */
    protected function pExpr_List($node)
    {
        return 'list(' . $this->pCommaSeparated($node->items) . ')';
    }
    // Other
    /**
     * @param \PhpParser\Node\Expr\Error $node
     */
    protected function pExpr_Error($node)
    {
        throw new \LogicException('Cannot pretty-print AST with Error nodes');
    }
    /**
     * @param \PhpParser\Node\Expr\Variable $node
     */
    protected function pExpr_Variable($node)
    {
        if ($node->name instanceof \PhpParser\Node\Expr) {
            return '${' . $this->p($node->name) . '}';
        } else {
            return '$' . $node->name;
        }
    }
    /**
     * @param \PhpParser\Node\Expr\Array_ $node
     */
    protected function pExpr_Array($node)
    {
        $syntax = $node->getAttribute('kind', $this->options['shortArraySyntax'] ? \PhpParser\Node\Expr\Array_::KIND_SHORT : \PhpParser\Node\Expr\Array_::KIND_LONG);
        if ($syntax === \PhpParser\Node\Expr\Array_::KIND_SHORT) {
            return '[' . $this->pMaybeMultiline($node->items, \true) . ']';
        } else {
            return 'array(' . $this->pMaybeMultiline($node->items, \true) . ')';
        }
    }
    /**
     * @param \PhpParser\Node\Expr\ArrayItem $node
     */
    protected function pExpr_ArrayItem($node)
    {
        return (null !== $node->key ? $this->p($node->key) . ' => ' : '') . ($node->byRef ? '&' : '') . ($node->unpack ? '...' : '') . $this->p($node->value);
    }
    /**
     * @param \PhpParser\Node\Expr\ArrayDimFetch $node
     */
    protected function pExpr_ArrayDimFetch($node)
    {
        return $this->pDereferenceLhs($node->var) . '[' . (null !== $node->dim ? $this->p($node->dim) : '') . ']';
    }
    /**
     * @param \PhpParser\Node\Expr\ConstFetch $node
     */
    protected function pExpr_ConstFetch($node)
    {
        return $this->p($node->name);
    }
    /**
     * @param \PhpParser\Node\Expr\ClassConstFetch $node
     */
    protected function pExpr_ClassConstFetch($node)
    {
        return $this->pDereferenceLhs($node->class) . '::' . $this->p($node->name);
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch $node
     */
    protected function pExpr_PropertyFetch($node)
    {
        return $this->pDereferenceLhs($node->var) . '->' . $this->pObjectProperty($node->name);
    }
    /**
     * @param \PhpParser\Node\Expr\NullsafePropertyFetch $node
     */
    protected function pExpr_NullsafePropertyFetch($node)
    {
        return $this->pDereferenceLhs($node->var) . '?->' . $this->pObjectProperty($node->name);
    }
    /**
     * @param \PhpParser\Node\Expr\StaticPropertyFetch $node
     */
    protected function pExpr_StaticPropertyFetch($node)
    {
        return $this->pDereferenceLhs($node->class) . '::$' . $this->pObjectProperty($node->name);
    }
    /**
     * @param \PhpParser\Node\Expr\ShellExec $node
     */
    protected function pExpr_ShellExec($node)
    {
        return '`' . $this->pEncapsList($node->parts, '`') . '`';
    }
    /**
     * @param \PhpParser\Node\Expr\Closure $node
     */
    protected function pExpr_Closure($node)
    {
        return $this->pAttrGroups($node->attrGroups, \true) . ($node->static ? 'static ' : '') . 'function ' . ($node->byRef ? '&' : '') . '(' . $this->pCommaSeparated($node->params) . ')' . (!empty($node->uses) ? ' use(' . $this->pCommaSeparated($node->uses) . ')' : '') . (null !== $node->returnType ? ' : ' . $this->p($node->returnType) : '') . ' {' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Expr\Match_ $node
     */
    protected function pExpr_Match($node)
    {
        return 'match (' . $this->p($node->cond) . ') {' . $this->pCommaSeparatedMultiline($node->arms, \true) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\MatchArm $node
     */
    protected function pMatchArm($node)
    {
        return ($node->conds ? $this->pCommaSeparated($node->conds) : 'default') . ' => ' . $this->p($node->body);
    }
    /**
     * @param \PhpParser\Node\Expr\ArrowFunction $node
     */
    protected function pExpr_ArrowFunction($node)
    {
        return $this->pAttrGroups($node->attrGroups, \true) . ($node->static ? 'static ' : '') . 'fn' . ($node->byRef ? '&' : '') . '(' . $this->pCommaSeparated($node->params) . ')' . (null !== $node->returnType ? ': ' . $this->p($node->returnType) : '') . ' => ' . $this->p($node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\ClosureUse $node
     */
    protected function pExpr_ClosureUse($node)
    {
        return ($node->byRef ? '&' : '') . $this->p($node->var);
    }
    /**
     * @param \PhpParser\Node\Expr\New_ $node
     */
    protected function pExpr_New($node)
    {
        if ($node->class instanceof \PhpParser\Node\Stmt\Class_) {
            $args = $node->args ? '(' . $this->pMaybeMultiline($node->args) . ')' : '';
            return 'new ' . $this->pClassCommon($node->class, $args);
        }
        return 'new ' . $this->pNewVariable($node->class) . '(' . $this->pMaybeMultiline($node->args) . ')';
    }
    /**
     * @param \PhpParser\Node\Expr\Clone_ $node
     */
    protected function pExpr_Clone($node)
    {
        return 'clone ' . $this->p($node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\Ternary $node
     */
    protected function pExpr_Ternary($node)
    {
        // a bit of cheating: we treat the ternary as a binary op where the ?...: part is the operator.
        // this is okay because the part between ? and : never needs parentheses.
        return $this->pInfixOp(\PhpParser\Node\Expr\Ternary::class, $node->cond, ' ?' . (null !== $node->if ? ' ' . $this->p($node->if) . ' ' : '') . ': ', $node->else);
    }
    /**
     * @param \PhpParser\Node\Expr\Exit_ $node
     */
    protected function pExpr_Exit($node)
    {
        $kind = $node->getAttribute('kind', \PhpParser\Node\Expr\Exit_::KIND_DIE);
        return ($kind === \PhpParser\Node\Expr\Exit_::KIND_EXIT ? 'exit' : 'die') . (null !== $node->expr ? '(' . $this->p($node->expr) . ')' : '');
    }
    /**
     * @param \PhpParser\Node\Expr\Throw_ $node
     */
    protected function pExpr_Throw($node)
    {
        return 'throw ' . $this->p($node->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\Yield_ $node
     */
    protected function pExpr_Yield($node)
    {
        if ($node->value === null) {
            return 'yield';
        } else {
            // this is a bit ugly, but currently there is no way to detect whether the parentheses are necessary
            return '(yield ' . ($node->key !== null ? $this->p($node->key) . ' => ' : '') . $this->p($node->value) . ')';
        }
    }
    // Declarations
    /**
     * @param \PhpParser\Node\Stmt\Namespace_ $node
     */
    protected function pStmt_Namespace($node)
    {
        if ($this->canUseSemicolonNamespaces) {
            return 'namespace ' . $this->p($node->name) . ';' . $this->nl . $this->pStmts($node->stmts, \false);
        } else {
            return 'namespace' . (null !== $node->name ? ' ' . $this->p($node->name) : '') . ' {' . $this->pStmts($node->stmts) . $this->nl . '}';
        }
    }
    /**
     * @param \PhpParser\Node\Stmt\Use_ $node
     */
    protected function pStmt_Use($node)
    {
        return 'use ' . $this->pUseType($node->type) . $this->pCommaSeparated($node->uses) . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\GroupUse $node
     */
    protected function pStmt_GroupUse($node)
    {
        return 'use ' . $this->pUseType($node->type) . $this->pName($node->prefix) . '\\{' . $this->pCommaSeparated($node->uses) . '};';
    }
    /**
     * @param \PhpParser\Node\Stmt\UseUse $node
     */
    protected function pStmt_UseUse($node)
    {
        return $this->pUseType($node->type) . $this->p($node->name) . (null !== $node->alias ? ' as ' . $node->alias : '');
    }
    protected function pUseType($type)
    {
        return $type === \PhpParser\Node\Stmt\Use_::TYPE_FUNCTION ? 'function ' : ($type === \PhpParser\Node\Stmt\Use_::TYPE_CONSTANT ? 'const ' : '');
    }
    /**
     * @param \PhpParser\Node\Stmt\Interface_ $node
     */
    protected function pStmt_Interface($node)
    {
        return $this->pAttrGroups($node->attrGroups) . 'interface ' . $node->name . (!empty($node->extends) ? ' extends ' . $this->pCommaSeparated($node->extends) : '') . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\Enum_ $node
     */
    protected function pStmt_Enum($node)
    {
        return $this->pAttrGroups($node->attrGroups) . 'enum ' . $node->name . (!empty($node->implements) ? ' implements ' . $this->pCommaSeparated($node->implements) : '') . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     */
    protected function pStmt_Class($node)
    {
        return $this->pClassCommon($node, ' ' . $node->name);
    }
    /**
     * @param \PhpParser\Node\Stmt\Trait_ $node
     */
    protected function pStmt_Trait($node)
    {
        return $this->pAttrGroups($node->attrGroups) . 'trait ' . $node->name . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\EnumCase $node
     */
    protected function pStmt_EnumCase($node)
    {
        return $this->pAttrGroups($node->attrGroups) . 'case ' . $node->name . ($node->expr ? ' = ' . $this->p($node->expr) : '') . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\TraitUse $node
     */
    protected function pStmt_TraitUse($node)
    {
        return 'use ' . $this->pCommaSeparated($node->traits) . (empty($node->adaptations) ? ';' : ' {' . $this->pStmts($node->adaptations) . $this->nl . '}');
    }
    /**
     * @param \PhpParser\Node\Stmt\TraitUseAdaptation\Precedence $node
     */
    protected function pStmt_TraitUseAdaptation_Precedence($node)
    {
        return $this->p($node->trait) . '::' . $node->method . ' insteadof ' . $this->pCommaSeparated($node->insteadof) . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\TraitUseAdaptation\Alias $node
     */
    protected function pStmt_TraitUseAdaptation_Alias($node)
    {
        return (null !== $node->trait ? $this->p($node->trait) . '::' : '') . $node->method . ' as' . (null !== $node->newModifier ? ' ' . \rtrim($this->pModifiers($node->newModifier), ' ') : '') . (null !== $node->newName ? ' ' . $node->newName : '') . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\Property $node
     */
    protected function pStmt_Property($node)
    {
        return $this->pAttrGroups($node->attrGroups) . (0 === $node->flags ? 'var ' : $this->pModifiers($node->flags)) . ($node->type ? $this->p($node->type) . ' ' : '') . $this->pCommaSeparated($node->props) . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\PropertyProperty $node
     */
    protected function pStmt_PropertyProperty($node)
    {
        return '$' . $node->name . (null !== $node->default ? ' = ' . $this->p($node->default) : '');
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     */
    protected function pStmt_ClassMethod($node)
    {
        return $this->pAttrGroups($node->attrGroups) . $this->pModifiers($node->flags) . 'function ' . ($node->byRef ? '&' : '') . $node->name . '(' . $this->pMaybeMultiline($node->params) . ')' . (null !== $node->returnType ? ' : ' . $this->p($node->returnType) : '') . (null !== $node->stmts ? $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}' : ';');
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassConst $node
     */
    protected function pStmt_ClassConst($node)
    {
        return $this->pAttrGroups($node->attrGroups) . $this->pModifiers($node->flags) . 'const ' . $this->pCommaSeparated($node->consts) . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\Function_ $node
     */
    protected function pStmt_Function($node)
    {
        return $this->pAttrGroups($node->attrGroups) . 'function ' . ($node->byRef ? '&' : '') . $node->name . '(' . $this->pCommaSeparated($node->params) . ')' . (null !== $node->returnType ? ' : ' . $this->p($node->returnType) : '') . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\Const_ $node
     */
    protected function pStmt_Const($node)
    {
        return 'const ' . $this->pCommaSeparated($node->consts) . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\Declare_ $node
     */
    protected function pStmt_Declare($node)
    {
        return 'declare (' . $this->pCommaSeparated($node->declares) . ')' . (null !== $node->stmts ? ' {' . $this->pStmts($node->stmts) . $this->nl . '}' : ';');
    }
    /**
     * @param \PhpParser\Node\Stmt\DeclareDeclare $node
     */
    protected function pStmt_DeclareDeclare($node)
    {
        return $node->key . '=' . $this->p($node->value);
    }
    // Control flow
    /**
     * @param \PhpParser\Node\Stmt\If_ $node
     */
    protected function pStmt_If($node)
    {
        return 'if (' . $this->p($node->cond) . ') {' . $this->pStmts($node->stmts) . $this->nl . '}' . ($node->elseifs ? ' ' . $this->pImplode($node->elseifs, ' ') : '') . (null !== $node->else ? ' ' . $this->p($node->else) : '');
    }
    /**
     * @param \PhpParser\Node\Stmt\ElseIf_ $node
     */
    protected function pStmt_ElseIf($node)
    {
        return 'elseif (' . $this->p($node->cond) . ') {' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\Else_ $node
     */
    protected function pStmt_Else($node)
    {
        return 'else {' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\For_ $node
     */
    protected function pStmt_For($node)
    {
        return 'for (' . $this->pCommaSeparated($node->init) . ';' . (!empty($node->cond) ? ' ' : '') . $this->pCommaSeparated($node->cond) . ';' . (!empty($node->loop) ? ' ' : '') . $this->pCommaSeparated($node->loop) . ') {' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\Foreach_ $node
     */
    protected function pStmt_Foreach($node)
    {
        return 'foreach (' . $this->p($node->expr) . ' as ' . (null !== $node->keyVar ? $this->p($node->keyVar) . ' => ' : '') . ($node->byRef ? '&' : '') . $this->p($node->valueVar) . ') {' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\While_ $node
     */
    protected function pStmt_While($node)
    {
        return 'while (' . $this->p($node->cond) . ') {' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\Do_ $node
     */
    protected function pStmt_Do($node)
    {
        return 'do {' . $this->pStmts($node->stmts) . $this->nl . '} while (' . $this->p($node->cond) . ');';
    }
    /**
     * @param \PhpParser\Node\Stmt\Switch_ $node
     */
    protected function pStmt_Switch($node)
    {
        return 'switch (' . $this->p($node->cond) . ') {' . $this->pStmts($node->cases) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\Case_ $node
     */
    protected function pStmt_Case($node)
    {
        return (null !== $node->cond ? 'case ' . $this->p($node->cond) : 'default') . ':' . $this->pStmts($node->stmts);
    }
    /**
     * @param \PhpParser\Node\Stmt\TryCatch $node
     */
    protected function pStmt_TryCatch($node)
    {
        return 'try {' . $this->pStmts($node->stmts) . $this->nl . '}' . ($node->catches ? ' ' . $this->pImplode($node->catches, ' ') : '') . ($node->finally !== null ? ' ' . $this->p($node->finally) : '');
    }
    /**
     * @param \PhpParser\Node\Stmt\Catch_ $node
     */
    protected function pStmt_Catch($node)
    {
        return 'catch (' . $this->pImplode($node->types, '|') . ($node->var !== null ? ' ' . $this->p($node->var) : '') . ') {' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\Finally_ $node
     */
    protected function pStmt_Finally($node)
    {
        return 'finally {' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    /**
     * @param \PhpParser\Node\Stmt\Break_ $node
     */
    protected function pStmt_Break($node)
    {
        return 'break' . ($node->num !== null ? ' ' . $this->p($node->num) : '') . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\Continue_ $node
     */
    protected function pStmt_Continue($node)
    {
        return 'continue' . ($node->num !== null ? ' ' . $this->p($node->num) : '') . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\Return_ $node
     */
    protected function pStmt_Return($node)
    {
        return 'return' . (null !== $node->expr ? ' ' . $this->p($node->expr) : '') . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\Throw_ $node
     */
    protected function pStmt_Throw($node)
    {
        return 'throw ' . $this->p($node->expr) . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\Label $node
     */
    protected function pStmt_Label($node)
    {
        return $node->name . ':';
    }
    /**
     * @param \PhpParser\Node\Stmt\Goto_ $node
     */
    protected function pStmt_Goto($node)
    {
        return 'goto ' . $node->name . ';';
    }
    // Other
    /**
     * @param \PhpParser\Node\Stmt\Expression $node
     */
    protected function pStmt_Expression($node)
    {
        return $this->p($node->expr) . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\Echo_ $node
     */
    protected function pStmt_Echo($node)
    {
        return 'echo ' . $this->pCommaSeparated($node->exprs) . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\Static_ $node
     */
    protected function pStmt_Static($node)
    {
        return 'static ' . $this->pCommaSeparated($node->vars) . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\Global_ $node
     */
    protected function pStmt_Global($node)
    {
        return 'global ' . $this->pCommaSeparated($node->vars) . ';';
    }
    /**
     * @param \PhpParser\Node\Stmt\StaticVar $node
     */
    protected function pStmt_StaticVar($node)
    {
        return $this->p($node->var) . (null !== $node->default ? ' = ' . $this->p($node->default) : '');
    }
    /**
     * @param \PhpParser\Node\Stmt\Unset_ $node
     */
    protected function pStmt_Unset($node)
    {
        return 'unset(' . $this->pCommaSeparated($node->vars) . ');';
    }
    /**
     * @param \PhpParser\Node\Stmt\InlineHTML $node
     */
    protected function pStmt_InlineHTML($node)
    {
        $newline = $node->getAttribute('hasLeadingNewline', \true) ? "\n" : '';
        return '?>' . $newline . $node->value . '<?php ';
    }
    /**
     * @param \PhpParser\Node\Stmt\HaltCompiler $node
     */
    protected function pStmt_HaltCompiler($node)
    {
        return '__halt_compiler();' . $node->remaining;
    }
    /**
     * @param \PhpParser\Node\Stmt\Nop $node
     */
    protected function pStmt_Nop($node)
    {
        return '';
    }
    // Helpers
    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     */
    protected function pClassCommon($node, $afterClassToken)
    {
        return $this->pAttrGroups($node->attrGroups, $node->name === null) . $this->pModifiers($node->flags) . 'class' . $afterClassToken . (null !== $node->extends ? ' extends ' . $this->p($node->extends) : '') . (!empty($node->implements) ? ' implements ' . $this->pCommaSeparated($node->implements) : '') . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}';
    }
    protected function pObjectProperty($node)
    {
        if ($node instanceof \PhpParser\Node\Expr) {
            return '{' . $this->p($node) . '}';
        } else {
            return $node;
        }
    }
    /**
     * @param mixed[] $encapsList
     */
    protected function pEncapsList($encapsList, $quote)
    {
        $return = '';
        foreach ($encapsList as $element) {
            if ($element instanceof \PhpParser\Node\Scalar\EncapsedStringPart) {
                $return .= $this->escapeString($element->value, $quote);
            } else {
                $return .= '{' . $this->p($element) . '}';
            }
        }
        return $return;
    }
    /**
     * @param string $string
     */
    protected function pSingleQuotedString($string)
    {
        return '\'' . \addcslashes($string, '\'\\') . '\'';
    }
    protected function escapeString($string, $quote)
    {
        if (null === $quote) {
            // For doc strings, don't escape newlines
            $escaped = \addcslashes($string, "\t\f\v\$\\");
        } else {
            $escaped = \addcslashes($string, "\n\r\t\f\v\$" . $quote . "\\");
        }
        // Escape control characters and non-UTF-8 characters.
        // Regex based on https://stackoverflow.com/a/11709412/385378.
        $regex = '/(
              [\\x00-\\x08\\x0E-\\x1F] # Control characters
            | [\\xC0-\\xC1] # Invalid UTF-8 Bytes
            | [\\xF5-\\xFF] # Invalid UTF-8 Bytes
            | \\xE0(?=[\\x80-\\x9F]) # Overlong encoding of prior code point
            | \\xF0(?=[\\x80-\\x8F]) # Overlong encoding of prior code point
            | [\\xC2-\\xDF](?![\\x80-\\xBF]) # Invalid UTF-8 Sequence Start
            | [\\xE0-\\xEF](?![\\x80-\\xBF]{2}) # Invalid UTF-8 Sequence Start
            | [\\xF0-\\xF4](?![\\x80-\\xBF]{3}) # Invalid UTF-8 Sequence Start
            | (?<=[\\x00-\\x7F\\xF5-\\xFF])[\\x80-\\xBF] # Invalid UTF-8 Sequence Middle
            | (?<![\\xC2-\\xDF]|[\\xE0-\\xEF]|[\\xE0-\\xEF][\\x80-\\xBF]|[\\xF0-\\xF4]|[\\xF0-\\xF4][\\x80-\\xBF]|[\\xF0-\\xF4][\\x80-\\xBF]{2})[\\x80-\\xBF] # Overlong Sequence
            | (?<=[\\xE0-\\xEF])[\\x80-\\xBF](?![\\x80-\\xBF]) # Short 3 byte sequence
            | (?<=[\\xF0-\\xF4])[\\x80-\\xBF](?![\\x80-\\xBF]{2}) # Short 4 byte sequence
            | (?<=[\\xF0-\\xF4][\\x80-\\xBF])[\\x80-\\xBF](?![\\x80-\\xBF]) # Short 4 byte sequence (2)
        )/x';
        return \preg_replace_callback($regex, function ($matches) {
            \assert(\strlen($matches[0]) === 1);
            $hex = \dechex(\ord($matches[0]));
            return '\\x' . \str_pad($hex, 2, '0', \STR_PAD_LEFT);
        }, $escaped);
    }
    protected function containsEndLabel($string, $label, $atStart = \true, $atEnd = \true)
    {
        $start = $atStart ? '(?:^|[\\r\\n])' : '[\\r\\n]';
        $end = $atEnd ? '(?:$|[;\\r\\n])' : '[;\\r\\n]';
        return \false !== \strpos($string, $label) && \preg_match('/' . $start . $label . $end . '/', $string);
    }
    /**
     * @param mixed[] $parts
     */
    protected function encapsedContainsEndLabel($parts, $label)
    {
        foreach ($parts as $i => $part) {
            $atStart = $i === 0;
            $atEnd = $i === \count($parts) - 1;
            if ($part instanceof \PhpParser\Node\Scalar\EncapsedStringPart && $this->containsEndLabel($part->value, $label, $atStart, $atEnd)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node $node
     */
    protected function pDereferenceLhs($node)
    {
        if (!$this->dereferenceLhsRequiresParens($node)) {
            return $this->p($node);
        } else {
            return '(' . $this->p($node) . ')';
        }
    }
    /**
     * @param \PhpParser\Node $node
     */
    protected function pCallLhs($node)
    {
        if (!$this->callLhsRequiresParens($node)) {
            return $this->p($node);
        } else {
            return '(' . $this->p($node) . ')';
        }
    }
    /**
     * @param \PhpParser\Node $node
     */
    protected function pNewVariable($node)
    {
        // TODO: This is not fully accurate.
        return $this->pDereferenceLhs($node);
    }
    /**
     * @param Node[] $nodes
     * @return bool
     */
    protected function hasNodeWithComments($nodes)
    {
        foreach ($nodes as $node) {
            if ($node && $node->getComments()) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param mixed[] $nodes
     * @param bool $trailingComma
     */
    protected function pMaybeMultiline($nodes, $trailingComma = \false)
    {
        if (!$this->hasNodeWithComments($nodes)) {
            return $this->pCommaSeparated($nodes);
        } else {
            return $this->pCommaSeparatedMultiline($nodes, $trailingComma) . $this->nl;
        }
    }
    /**
     * @param mixed[] $nodes
     * @param bool $inline
     */
    protected function pAttrGroups($nodes, $inline = \false) : string
    {
        $result = '';
        $sep = $inline ? ' ' : $this->nl;
        foreach ($nodes as $node) {
            $result .= $this->p($node) . $sep;
        }
        return $result;
    }
}
