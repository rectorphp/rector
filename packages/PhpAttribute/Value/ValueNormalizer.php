<?php

declare(strict_types=1);

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

        $annotationShortName = $doctrineAnnotationTagValueNode->identifierTypeNode->name;
        $annotationShortName = ltrim($annotationShortName, '@');

        $values = $doctrineAnnotationTagValueNode->getValues();
        if ($values !== []) {
            $argValues = $this->normalize(
                $doctrineAnnotationTagValueNode->getValuesWithExplicitSilentAndWithoutQuotes()
            );

            $args = [];
            if (! is_array($argValues)) {
                throw new ShouldNotHappenException();
            }

            foreach ($argValues as $key => $argValue) {
                $expr = BuilderHelpers::normalizeValue($argValue);
                $name = null;

                // for named arguments
                if (is_string($key)) {
                    $name = new Identifier($key);
                }

                $args[] = new Arg($expr, false, false, [], $name);
            }
        } else {
            $args = [];
        }

        return new New_(new Name($annotationShortName), $args);
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
