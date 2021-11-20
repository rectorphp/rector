<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\Value;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PhpAttribute\Exception\InvalidNestedAttributeException;
final class ValueNormalizer
{
    /**
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @param mixed $value
     * @return mixed[]|bool|float|int|\PhpParser\Node\Expr|string
     */
    public function normalize($value)
    {
        if ($value instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return $this->normalizeDoctrineAnnotationTagValueNode($value);
        }
        if ($value instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode) {
            return $this->normalizeConstrExprNode($value);
        }
        if ($value instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
            return \array_map(function ($node) {
                return $this->normalize($node);
            }, $value->getValuesWithExplicitSilentAndWithoutQuotes());
        }
        if (\is_string($value) && \strpos($value, '::') !== \false) {
            // class const fetch
            [$class, $constant] = \explode('::', $value);
            return new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name($class), $constant);
        }
        if ($value instanceof \PHPStan\PhpDocParser\Ast\Node) {
            return (string) $value;
        }
        if (\is_array($value)) {
            return \array_map(function ($item) {
                return $this->normalize($item);
            }, $value);
        }
        return $value;
    }
    private function normalizeDoctrineAnnotationTagValueNode(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : \PhpParser\Node\Expr\New_
    {
        // if PHP 8.0- throw exception
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::NEW_INITIALIZERS)) {
            throw new \Rector\PhpAttribute\Exception\InvalidNestedAttributeException();
        }
        $annotationShortName = $doctrineAnnotationTagValueNode->identifierTypeNode->name;
        $annotationShortName = \ltrim($annotationShortName, '@');
        $values = $doctrineAnnotationTagValueNode->getValues();
        if ($values !== []) {
            $argValues = $this->normalize($doctrineAnnotationTagValueNode->getValuesWithExplicitSilentAndWithoutQuotes());
            $args = [];
            if (!\is_array($argValues)) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            foreach ($argValues as $key => $argValue) {
                $expr = \PhpParser\BuilderHelpers::normalizeValue($argValue);
                $name = null;
                // for named arguments
                if (\is_string($key)) {
                    $name = new \PhpParser\Node\Identifier($key);
                }
                $args[] = new \PhpParser\Node\Arg($expr, \false, \false, [], $name);
            }
        } else {
            $args = [];
        }
        return new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name($annotationShortName), $args);
    }
    /**
     * @return bool|float|int
     */
    private function normalizeConstrExprNode(\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode $constExprNode)
    {
        if ($constExprNode instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode) {
            return (int) $constExprNode->value;
        }
        if ($constExprNode instanceof \PHPStan\Type\Constant\ConstantFloatType || $constExprNode instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            return $constExprNode->getValue();
        }
        if ($constExprNode instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode) {
            return \true;
        }
        if ($constExprNode instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode) {
            return \false;
        }
        throw new \Rector\Core\Exception\ShouldNotHappenException();
    }
}
