<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\Annotation\AnnotationItemsResolver;
use Rector\BetterPhpDocParser\AnnotationReader\NodeAnnotationReader;
use Rector\BetterPhpDocParser\PhpDocParser\AnnotationContentResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;

abstract class AbstractPhpDocNodeFactory
{
    /**
     * @var NodeAnnotationReader
     */
    protected $nodeAnnotationReader;

    /**
     * @var AnnotationContentResolver
     */
    protected $annotationContentResolver;

    /**
     * @var AnnotationItemsResolver
     */
    protected $annotationItemsResolver;

    /**
     * @var ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;

    /**
     * @required
     */
    public function autowireAbstractPhpDocNodeFactory(
        NodeAnnotationReader $nodeAnnotationReader,
        AnnotationContentResolver $annotationContentResolver,
        AnnotationItemsResolver $annotationItemsResolver,
        ObjectTypeSpecifier $objectTypeSpecifier
    ): void {
        $this->nodeAnnotationReader = $nodeAnnotationReader;
        $this->annotationContentResolver = $annotationContentResolver;
        $this->annotationItemsResolver = $annotationItemsResolver;
        $this->objectTypeSpecifier = $objectTypeSpecifier;
    }

    protected function resolveContentFromTokenIterator(TokenIterator $tokenIterator): string
    {
        return $this->annotationContentResolver->resolveFromTokenIterator($tokenIterator);
    }

    protected function resolveFqnTargetEntity(string $targetEntity, Node $node): string
    {
        $targetEntity = $this->getCleanedUpTargetEntity($targetEntity);
        if (class_exists($targetEntity)) {
            return $targetEntity;
        }

        $namespacedTargetEntity = $node->getAttribute(AttributeKey::NAMESPACE_NAME) . '\\' . $targetEntity;
        if (class_exists($namespacedTargetEntity)) {
            return $namespacedTargetEntity;
        }

        $resolvedType = $this->objectTypeSpecifier->narrowToFullyQualifiedOrAlaisedObjectType(
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
     * @return string[]
     */
    protected function matchCurlyBracketOpeningAndClosingSpace(string $annotationContent): array
    {
        $match = Strings::match($annotationContent, '#^\{(?<opening_space>\s+)#');
        $openingSpace = $match['opening_space'] ?? '';

        $match = Strings::match($annotationContent, '#(?<closing_space>\s+)\}$#');
        $closingSpace = $match['closing_space'] ?? '';

        return [$openingSpace, $closingSpace];
    }

    private function getCleanedUpTargetEntity(string $targetEntity): string
    {
        return Strings::replace($targetEntity, '#::class#', '');
    }
}
