<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocParser\AnnotationContentResolver;
use Rector\BetterPhpDocParser\ValueObject\AroundSpaces;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;

abstract class AbstractPhpDocNodeFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/548EJJ/1
     */
    private const CLASS_CONST_REGEX = '#::class#';

    /**
     * @var string
     * @see https://regex101.com/r/CsmMaz/1
     */
    private const OPENING_SPACE_REGEX = '#^\{(?<opening_space>\s+)#';

    /**
     * @var string
     * @see https://regex101.com/r/Rrbi3V/1
     */
    private const CLOSING_SPACE_REGEX = '#(?<closing_space>\s+)\}$#';

    /**
     * @var AnnotationContentResolver
     */
    private $annotationContentResolver;

    /**
     * @var ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @required
     */
    public function autowireAbstractPhpDocNodeFactory(
        AnnotationContentResolver $annotationContentResolver,
        ObjectTypeSpecifier $objectTypeSpecifier,
        ReflectionProvider $reflectionProvider
    ): void {
        $this->annotationContentResolver = $annotationContentResolver;
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->reflectionProvider = $reflectionProvider;
    }

    protected function resolveContentFromTokenIterator(TokenIterator $tokenIterator): string
    {
        return $this->annotationContentResolver->resolveFromTokenIterator($tokenIterator);
    }

    protected function resolveFqnTargetEntity(string $targetEntity, Node $node): string
    {
        $targetEntity = $this->getCleanedUpTargetEntity($targetEntity);
        if ($this->reflectionProvider->hasClass($targetEntity)) {
            return $targetEntity;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return $targetEntity;
        }

        $namespacedTargetEntity = $scope->getNamespace() . '\\' . $targetEntity;
        if ($this->reflectionProvider->hasClass($namespacedTargetEntity)) {
            return $namespacedTargetEntity;
        }

        $resolvedType = $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType(
            $node,
            new ObjectType($targetEntity)
        );
        if ($resolvedType instanceof ShortenedObjectType) {
            return $resolvedType->getFullyQualifiedName();
        }

        // probably tested class
        return $targetEntity;
    }

    /**
     * Covers spaces like https://github.com/rectorphp/rector/issues/2110
     */
    protected function matchCurlyBracketAroundSpaces(string $annotationContent): AroundSpaces
    {
        $match = Strings::match($annotationContent, self::OPENING_SPACE_REGEX);
        $openingSpace = $match['opening_space'] ?? '';

        $match = Strings::match($annotationContent, self::CLOSING_SPACE_REGEX);
        $closingSpace = $match['closing_space'] ?? '';

        return new AroundSpaces($openingSpace, $closingSpace);
    }

    private function getCleanedUpTargetEntity(string $targetEntity): string
    {
        return Strings::replace($targetEntity, self::CLASS_CONST_REGEX, '');
    }
}
