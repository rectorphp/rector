<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Value;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PhpAttribute\Exception\InvalidNestedAttributeException;

final class ValueNormalizer
{
    public function __construct(
        private PhpVersionProvider $phpVersionProvider
    ) {
    }

    /**
     * @param mixed $value
     * @return array<mixed>
     */
    public function normalize($value): bool | float | int | string | array | Expr
    {
        if ($value instanceof DoctrineAnnotationTagValueNode) {
            return $this->normalizeDoctrineAnnotationTagValueNode($value);
        }

        if ($value instanceof ConstExprNode) {
            return $this->normalizeConstrExprNode($value);
        }

        if ($value instanceof CurlyListNode) {
            return array_map(
                fn ($node): array | bool | float | int | Expr | string => $this->normalize($node),
                $value->getValuesWithExplicitSilentAndWithoutQuotes()
            );
        }

        if (is_string($value) && str_contains($value, '::')) {
            // class const fetch
            [$class, $constant] = explode('::', $value);
            return new ClassConstFetch(new Name($class), $constant);
        }

        if ($value instanceof Node) {
            return (string) $value;
        }

        if (\is_array($value)) {
            return array_map(fn ($item): array | bool | float | int | Expr | string => $this->normalize($item), $value);
        }

        return $value;
    }

    private function normalizeDoctrineAnnotationTagValueNode(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode
    ): New_ {
        // if PHP 8.0- throw exception
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::NEW_INITIALIZERS)) {
            throw new InvalidNestedAttributeException();
        }

        $resolveClass = $doctrineAnnotationTagValueNode->identifierTypeNode->getAttribute(
            PhpDocAttributeKey::RESOLVED_CLASS
        );
        return new New_(new FullyQualified($resolveClass));
    }

    private function normalizeConstrExprNode(ConstExprNode $constExprNode): int|bool|float
    {
        if ($constExprNode instanceof ConstExprIntegerNode) {
            return (int) $constExprNode->value;
        }

        if ($constExprNode instanceof ConstantFloatType || $constExprNode instanceof ConstantBooleanType) {
            return $constExprNode->getValue();
        }

        if ($constExprNode instanceof ConstExprTrueNode) {
            return true;
        }

        if ($constExprNode instanceof ConstExprFalseNode) {
            return false;
        }

        throw new ShouldNotHappenException();
    }
}
